<?php
namespace App\Scrapers;

use App\ScrapeResults\Result;
use App\ScrapeResults\Application;
use App\ScrapeResults\Hearing;
use App\ScrapeResults\Party;
use DateTime;
use DateTimeZone;
use ErrorException;

class WaDotagScraper extends Scraper
{

	protected $timezone = 'Australia/Perth';

	/**
	 * Scrapes results from the WA Dotag website.
	 *
	 * @return array
	 */
	protected function _scrape()
	{
		$url = 'http://www.courts.dotag.wa.gov.au/_apps/courtlists/listings.aspx';

		$this->_log('info', 'Requesting ' . $url);
		$initial_html = $this->transport->get($url)->getBody();

		preg_match('/<input[^>]+name="__VIEWSTATE"[^>]+value="([^"]+)"/i', $initial_html, $matches);
		$viewstate = $matches[1];

		preg_match('/<input[^>]+name="__EVENTVALIDATION"[^>]+value="([^"]+)"/i', $initial_html, $matches);
		$validation = $matches[1];

		foreach (['SC','DC'] as $court_type) {
			$params = [
				'__EVENTTARGET'     => 'ctl00$ctl00$MasterContent$pageContent$lnk' . $court_type . 'List',
				'__EVENTARGUMENT'   => '',
				'__VIEWSTATE'       => $viewstate,
				'__EVENTVALIDATION' => $validation,
				'ctl00$ctl00$MasterContent$pageContent$hidSortField'     => '',
				'ctl00$ctl00$MasterContent$pageContent$hidSortDirection' => '',
			];

			$this->_log('info', "Requesting $url (type=$court_type)");
			$html = $this->transport->post($url, ['form_params' => $params])->getBody();

			$this->date = $this->_scrapeDate($html);

			$this->_scrapePage($html, $court_type);
		}
	}

	/**
	 * Scrapes the date from the HTML.
	 *
	 * @param  string $html
	 * @return DateTime
	 */
	private function _scrapeDate($html)
	{
		preg_match('%The daily court list is accurate as at: <b>([0-9]+ \S+ [0-9]{4})</b>%i', $html, $match);

		return DateTime::createFromFormat('j F Y', $match[1], new DateTimeZone($this->timezone));
	}

	/**
	 * Scrapes the results on a single page.
	 *
	 * @param  string $html
	 * @param  string $court_type
	 * @return array
	 */
	private function _scrapePage($html, $court_type)
	{
		$html = str_replace(chr(0xC2) . chr(0xA0), ' ', $html);

		preg_match_all('%<h2>(.*?)</h2>%is', $html, $matches);
		$titles = array_slice($matches[1], 1);

		$sections = preg_split('%<h2>.*?</h2>%is', $html);
		$sections = array_slice($sections, 2);

		foreach ($sections as $index => $section_html) {
			$title = $titles[$index];

			$this->_scrapeSection($title, $court_type, $section_html);
		}
	}

	private function _scrapeSection($section_title, $court_type, $html)
	{
		if (strpos($html, '<CourtLocation>') === false) {
			$this->_scrapeSectionInFormatA($section_title, $court_type, $html);
			return;
		}

		$this->_scrapeSectionInFormatB($section_title, $court_type, $html);
	}

	private function _scrapeSectionInFormatA($section_title, $court_type, $html)
	{
		$parts = explode('<hr />', $html);
		array_pop($parts);

		foreach ($parts as $part) {
			$this->_scrapePart($section_title, $court_type, $part);
		}
	}

	private function _scrapePart($section_title, $court_type, $html)
	{
		$html = str_replace('&nbsp;', ' ', $html);

		preg_match_all('%class="case">(.*?)</div>%is', $html, $matches);
		$cases = $matches[1];

		preg_match_all('%class="hearing">(.*?)</div>%is', $html, $matches);
		$hearings = $matches[1];

		foreach ($cases as $index => $case) {
			if ($case) {
				try {
					$this->_scrapeCaseInFormatA($case, $hearings[$index], $html, $section_title, $court_type);
				} catch (ErrorException $e) {
					$this->_logException($e);
				}
			}
		}
	}

	/**
	 * @param  string $case_html     Case title
	 * @param  string $hearing_html  Rightmost column of each row
	 * @param  string $part_html     The stuff separated by <hr>
	 * @param  string $section_title The <h2> heading
	 * @param  string $court_type    SC or DC
	 */
	private function _scrapeCaseInFormatA($case_html, $hearing_html, $part_html, $section_title, $court_type)
	{
		$result = new Result;
		$result->setState('WA');
		$result->setCourtType($court_type == 'SC' ? 'Supreme' : 'District');
		$result->setCaseType($section_title);
		$result->setJurisdiction(strpos($section_title, 'Criminal') !== false ? 'Criminal' : 'Civil');
		$result->setUrl('http://www.courts.dotag.wa.gov.au/_apps/courtlists/listings.aspx');

		preg_match('/\s*\((.*?)\)$/', $case_html, $match);
		$result->setCaseNumber($match[1]);
		$result->setUniqueId($match[1]);

		$case_name = preg_replace('/^\d+/', '', $case_html);
		$case_name = preg_replace('/\(.*?\)$/', '', $case_name);
		$result->setCaseName(trim($case_name));

		$application = new Application;
		$application->setTitle(trim($hearing_html));

		$hearing = new Hearing;
		$hearing->setCourtName($section_title);
		$hearing->setType(trim($hearing_html));

		preg_match('/(?:before|Coram:)\s(.*?)</i', $part_html, $match);
		$hearing->setOfficer(trim($match[1]));

		preg_match('/class="location">(.*?)</i', $part_html, $match);
		$location = trim($match[1]);
		$hearing->setCourtAddress($location);
		$hearing->setCourtRoom($this->_getCourtRoom($location));

		preg_match('/class="sittingTime">[^<]+([0-9]+):([0-9]{2})/', $part_html, $match);
		$date = clone $this->date;
		$date->setTime($match[1], $match[2], 0);
		$hearing->setDateTime($date);

		$application->addHearing($hearing);
		$result->addApplication($application);

		$this->_scrapeParties($result);

		$this->ret($result);
	}

	private function _scrapeSectionInFormatB($section_title, $court_type, $html)
	{
		preg_match('/before (.*?) (?:in|on) /', $html, $match);
		$officer = trim($match[1]);

		preg_match('%<CourtLocation>(.*?)</CourtLocation>%', $html, $match);
		$location = trim($match[1]);

		preg_match_all('%<td.*?>(.*?)</td>%is', $html, $matches);

		foreach (array_chunk($matches[1], 4) as $cells) {
			try {
				$this->_scrapeCaseInFormatB($cells, $section_title, $court_type, $officer, $location);
			} catch (ErrorException $e) {
				$this->_logException($e);
			}
		}
	}

	private function _scrapeCaseInFormatB($cells, $section_title, $court_type, $officer, $location)
	{
		$case_no = trim(str_replace('&nbsp;', ' ', $cells[2]));
		$case_name = trim(str_replace('&nbsp;', ' ', $cells[3]));

		$result = new Result;
		$result->setState('WA');
		$result->setCourtType($court_type == 'SC' ? 'Supreme' : 'District');
		$result->setJurisdiction($section_title == 'In Criminal' ? 'Criminal' : 'Civil');
		$result->setUrl('http://www.courts.dotag.wa.gov.au/_apps/courtlists/listings.aspx');
		$result->setCaseNumber($case_no);
		$result->setCaseName($case_name);
		$result->setCaseType($section_title);
		$result->setUniqueId($case_no);

		$application = new Application;

		$hearing = new Hearing;
		$hearing->setCourtName($section_title);
		$hearing->setOfficer($officer);
		$hearing->setCourtAddress($location);
		$hearing->setCourtRoom($this->_getCourtRoom($location));

		preg_match('/([0-9]+):([0-9]{2})/', $cells[0], $match);
		$date = clone $this->date;
		$date->setTime($match[1], $match[2], 0);
		$hearing->setDateTime($date);

		$application->addHearing($hearing);
		$result->addApplication($application);

		$this->_scrapeParties($result);

		$this->ret($result);
	}

	/**
	 * Scrapes the court room from the location field.
	 *
	 * @param  string $location
	 * @return string
	 */
	private function _getCourtRoom($location)
	{
		$parts = explode(',', $location);

		return trim($parts[0]);
	}

	/**
	 * Scrapes the parties from the matter cell.
	 *
	 * @param  Result $result
	 */
	private function _scrapeParties(Result $result)
	{
		$matter = $result->getCaseName();

		$parts = preg_split('/ v\.? /', $matter);

		if (count($parts) != 2) {
			return;
		}

		$party = new Party;
		$party->setName($parts[0]);
		$party->setRole('Plaintiff');
		$result->getApplications()[0]->addParty($party);

		$party = new Party;
		$party->setName($parts[1]);
		$party->setRole('Defendant');
		$result->getApplications()[0]->addParty($party);
	}

}
