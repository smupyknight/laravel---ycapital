<?php
namespace App\Scrapers;

use App\ScrapeResults\Result;
use App\ScrapeResults\Application;
use App\ScrapeResults\Hearing;
use App\ScrapeResults\Party;
use App\ScrapeResults\Document;
use DateTime;
use DateTimeZone;
use ErrorException;

/**
 * County Court Victoria scraper.
 *
 * Last name is a required field, so the scraper searches for each letter of
 * the alphabet in the last_name field.
 *
 * The date searched by is the filing date of the case. It is not known how the
 * site handles case updates.
 *
 * @see http://cjep.justice.vic.gov.au/pls/p100/ck_public_qry_cpty.cp_personcase_setup_idx
 */
class CountyCourtVictoriaScraper extends Scraper
{

	protected $timezone = 'Australia/Melbourne';

	private $party_associations = [];

	/**
	 * Scrapes results from the County Court Victoria website.
	 *
	 * @return array
	 */
	protected function _scrape()
	{
		$letters = str_split('abcdefghijklmnopqrstuvwxyz');

		foreach ($letters as $letter) {
			$page = 1;

			do {
				$params = [
					'backto'      => 'P',
					'partial_ind' => 'checked',
					'last_name'   => $letter,
					'begin_date'  => $this->start_date->format('d-M-Y'),
					'case_type'   => 'ALL',
					'locn_type'   => 'ALL',
					'PageNo'      => $page++,
				];

				$url = 'http://cjep.justice.vic.gov.au/pls/p100/ck_public_qry_cpty.cp_personcase_srch_details?' . http_build_query($params);

				$this->_log('info', 'Requesting ' . $url);
				$html = $this->transport->get($url)->getBody();

				$this->_scrapePage($html);
			} while ($this->_hasNextPage($html));
		}
	}

	/**
	 * Determines if the HTML contains a "next page" link.
	 *
	 * @param  string $html
	 * @return bool
	 */
	private function _hasNextPage($html)
	{
		return (stripos($html, 'Next->') !== false);
	}

	/**
	 * Scrapes the results on a single page.
	 *
	 * Finds the second table, then iterates through its rows and scrapes
	 * individual rows.
	 *
	 * @param  string $html
	 * @return array
	 */
	private function _scrapePage($html)
	{
		preg_match_all('%<table.*?>(.*?)</table>%is', $html, $matches);
		$table = $matches[1][1];

		preg_match_all('%<tr.*?>(.*?)</tr>%is', $table, $matches);
		$rows = $matches[1];

		array_shift($rows);

		foreach ($rows as $tr) {
			try {
				$this->_scrapeProceeding($tr);
			} catch (ErrorException $e) {
				$this->_logException($e);
			}
		}
	}

	/**
	 * Scrapes a single proceeding from a table row's HTML.
	 *
	 * @param  string $tr_html
	 * @return Result
	 */
	private function _scrapeProceeding($tr_html)
	{
		preg_match_all('%<td.*?>(.*?)</td>%is', $tr_html, $matches);
		list($id, $name, $details, $role, $file_date) = $matches[1];

		preg_match('/CI-[0-9]+-[0-9]+/', $details, $match);
		$case_no = $match[0];

		preg_match('%</a>&nbsp;&nbsp;(.*?)<%i', $details, $match);
		$case_title = $match[1];

		$result = new Result;
		$result->setState('VIC');
		$result->setCourtType('County');
		$result->setUniqueId($case_no);
		$result->setCaseNumber($case_no);
		$result->setCaseName($case_title);
		$result->setUrl('http://cjep.justice.vic.gov.au/pls/p100/ck_public_qry_doct.cp_dktrpt_docket_report?case_id=' . $case_no);

		$application = new Application;

		$this->_log('info', 'Requesting ' . $result->getUrl());
		$details_html = $this->transport->get($result->getUrl())->getBody();

		preg_match('/Filing Date:.*?<td>(.*?)</is', $details_html, $match);
		$date = trim(str_replace('&nbsp;', ' ', $match[1]));
		$date = DateTime::createFromFormat('l, F dS, Y', $date);

		if ($date) {
			$application->setDateFiled($date->format('Y-m-d'));
		}

		preg_match('/Filing Ending Date:.*?<td>(.*?)</is', $details_html, $match);
		$date = trim(str_replace('&nbsp;', ' ', $match[1]));
		$date = DateTime::createFromFormat('l, F dS, Y', $date);

		if ($date) {
			$application->setDateFinalised($date->format('Y-m-d'));
		}

		preg_match('/Type:.*?<td>(.*?)</is', $details_html, $match);
		$type = trim(str_replace('&nbsp;', ' ', $match[1]));
		$application->setType($type);
		$result->setCaseType($type);
		$result->setJurisdiction(strpos($type, 'Criminal') !== false ? 'Criminal' : 'Civil');

		preg_match('/Location:.*? - ([^<]+)</is', $details_html, $match);
		$result->setSuburb($match[1]);

		preg_match('/Status:.*?<td>(.*?)<\//is', $details_html, $match);
		$status = trim(strip_tags(str_replace('&nbsp;', ' ', $match[1])));

		if ($status != 'none') {
			$application->setStatus($status);
		}

		$this->_scrapeRelatedCases($result, $details_html);

		$this->_scrapeParties($application, $details_html);
		$this->_scrapeFilingEntries($application, $details_html);

		$result->addApplication($application);

		return $this->ret($result);
	}

	/**
	 * Scrapes the Related Cases from a proceeding's HTML.
	 *
	 * @param  Result $result
	 * @param  string $html
	 * @return array
	 */
	private function _scrapeRelatedCases(Result $result, $html)
	{
		if (preg_match('/Related Cases.*?href.*?>(.*?)<\/a>.*?<a name="events">/is', $html, $match)) {
			$result->setRelatedCases($match[1]);
		}
	}

	/**
	 * Scrapes the parties from a proceeding's HTML.
	 *
	 * The page is divided into sections with <a name="foo"> tags. We find the
	 * tag with name="parties" and check for a table within that section.
	 *
	 * @param  Application  $application
	 * @param  string       $html
	 * @return array
	 */
	private function _scrapeParties(Application $application, $html)
	{
		preg_match('/<a\s[^>]*name="parties"[^>]*>(.*?)<a\s[^>]*name="/is', $html, $matches);

		if (empty($matches)) {
			return;
		}

		$section = $matches[1];

		$rows = explode('<TR', $matches[1]);
		unset($rows[0]); // empty string
		unset($rows[1]); // header row

		foreach ($rows as $row) {
			preg_match_all('%<td.*?>(.*?)</td>%is', $row, $matches);
			$cells = $matches[1];

			if (count($cells) == 6) {
				$application->addParty($this->_scrapeParty($cells));
			}
		}

		foreach ($this->party_associations as $seq_no => $assoc) {
			$seq_party = $application->getParties()[$seq_no - 1];
			$assoc_party = $application->getParties()[$assoc - 1];

			$seq_party->setName($seq_party->getName() . ' (associated with ' . $assoc_party->getName() . ')');
		}

		$this->party_associations = [];
	}

	/**
	 * Scrapes a party from an array of table cells.
	 *
	 * @param  array  $cells
	 * @return Party
	 */
	private function _scrapeParty($cells)
	{
		list($seq_no, $assoc, $end_date, $type, $id, $name) = $cells;

		$seq_no = trim(str_replace('&nbsp;', '', $seq_no));
		$assoc = trim(str_replace('&nbsp;', '', $assoc));
		$name = strip_tags($name);
		$id = strip_tags($id);

		$party = new Party;

		if (preg_match('/^(\S+), (.+)$/', $name, $match)) {
			$party->setIndividualNames($match[2], $match[1]);
		} else {
			$party->setName($name);
		}

		$party->setRole($type);
		$party->setId($id);

		if ($assoc) {
			$this->party_associations[$seq_no] = $assoc;
		}

		return $party;
	}

	private function _scrapeFilingEntries(Application $application, $html)
	{
		preg_match('%<a\s[^>]*name="dockets"[^>]*>(.*?)</table>%is', $html, $matches);

		if (empty($matches)) {
			return;
		}

		$section = $matches[1];

		$rows = explode('<TR', $matches[1]);
		array_shift($rows); // empty string
		array_shift($rows); // header row

		$i = 0;

		while ($i < count($rows)) {
			$application->addDocument($this->_scrapeFilingEntry($rows[$i] . $rows[$i+1]));
			$i += 3;
		}
	}

	private function _scrapeFilingEntry($html)
	{
		$html = str_replace('&nbsp;', ' ', $html);

		preg_match_all('%<td.*?>(.*?)</td>%is', $html, $matches);
		$cells = array_map('trim', $matches[1]);

		$date = DateTime::createFromFormat('d-M-Y<\B\R>h:i A', $cells[0], new DateTimeZone($this->timezone));

		$document = new Document;
		$document->setDateTime($date);
		$document->setTitle($cells[1]);
		$document->setDescription(trim($cells[5] . ' ' . $cells[3]));
		$document->setFiledBy($cells[2]);

		return $document;
	}

}
