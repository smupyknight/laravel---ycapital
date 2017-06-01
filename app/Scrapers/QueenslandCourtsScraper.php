<?php
namespace App\Scrapers;

use App\ScrapeResults\Result;
use App\ScrapeResults\Application;
use App\ScrapeResults\Hearing;
use App\ScrapeResults\Party;
use App\ScrapeResults\Document;
use DateTime;
use DateTimeZone;
use DateInterval;
use ErrorException;
use Exception;

/**
 * Queensland Courts scraper.
 *
 * On this site, the results are ordered by last document date descending.
 * Results are limited to 500.
 *
 * The site operators have a habit of creating cases with dummy names, then
 * updating the party names later without creating any new documents. When this
 * happens the case is not bumped to the top of the list due to no new document.
 * To minimise the amount of missed updates, the scraper searches in a specific
 * way:
 *
 * The scraper does a search for each location and scrapes all the results. This
 * captures most stuff.
 *
 * The scraper then does a search by setting the party "from date" to yesterday,
 * which returns cases which had the party details filled in recently.
 *
 * @see http://apps.courts.qld.gov.au/esearching/
 */
class QueenslandCourtsScraper extends Scraper
{

	protected $timezone = 'Australia/Brisbane';

	private $suburb = null;

	private $locations = [];

	private $done = [];

	/**
	 * Scrapes results from the Queensland Courts website.
	 *
	 * @return array
	 */
	protected function _scrape()
	{
		$this->locations = $this->_scrapeLocations();

		foreach ($this->locations as $location) {
			$this->_searchAndScrape(['Originatinglocation' => $location]);
		}

		$this->_searchAndScrape(['Datefromparty' => $this->start_date->format('j/m/Y') . ' 12:00:00 AM']);
	}

	/**
	 * Requests the search form page, scrapes the locations out of the select
	 * list and returns them as an array.
	 *
	 * @return array
	 */
	private function _scrapeLocations()
	{
		$url = 'http://apps.courts.qld.gov.au/esearching/';

		$this->_log('info', 'Requesting ' . $url);
		$html = $this->transport->get($url)->getBody();

		if (!preg_match('%<select.+?name=".+?OriginatingLocation"(.+?)</select>%is', $html, $match)) {
			throw new Exception('Unable to scrape locations.');
		}

		preg_match_all('%<option value="(.*?)">(.*?)</option>%is', $match[1], $matches);
		$locations = [];

		foreach ($matches[1] as $index => $abbrev) {
			$name = $matches[2][$index];

			$locations[$name] = $abbrev;
		}

		return $locations;
	}

	/**
	 * Submits a search on the QLD Courts website and scrapes all of the
	 * results.
	 *
	 * @param  array  $search_params
	 * @return array
	 */
	private function _searchAndScrape(array $search_params)
	{
		$query_string = http_build_query($search_params);
		$form_params = [];
		$asp_params = [];
		$page = 1;

		do {
			if ($page > 1) {
				$form_params = array_merge($asp_params, [
					'__EVENTTARGET'   => 'ctl00$ContentPlaceHolder1$FileGrid$ctl01$NextTopLink',
					'__EVENTARGUMENT' => '',
				]);
			}

			$url = 'http://apps.courts.qld.gov.au/esearching/Results.aspx?' . $query_string;

			$this->_log('info', 'Requesting ' . $url . ' (page ' . $page . ')');
			$html = $this->transport->post($url, ['form_params' => $form_params])->getBody();

			$asp_params = $this->_scrapeAspParams($html);

			$this->_scrapePage($html);

			++$page;
		} while ($this->_hasNextPage($html));
	}

	/**
	 * Scrapes the ASP.net input fields from the page.
	 *
	 * The ASP input fields begin with a double underscore and need to be
	 * resubmitted on requests to subsequent pages.
	 *
	 * @param  string $html
	 * @return array
	 */
	private function _scrapeAspParams($html)
	{
		preg_match_all('/<input[^>]+name="(__[a-z0-9]+)"[^>]+value="(.*?)"/i', $html, $matches);
		$params = [];

		foreach ($matches[1] as $key => $name) {
			$params[$name] = $matches[2][$key];
		}

		return $params;
	}

	/**
	 * Determines if the HTML contains a "next page" link.
	 *
	 * @param  string $html
	 * @return bool
	 */
	private function _hasNextPage($html)
	{
		return (stripos($html, '_nexttoplink') !== false);
	}

	/**
	 * Scrapes the results on a single page.
	 *
	 * Finds the outermost table in the HTML, then iterates through its rows
	 * and scrapes individual rows.
	 *
	 * @param  string $html
	 * @return array
	 */
	private function _scrapePage($html)
	{
		$html = str_replace('&nbsp;', ' ', $html);

		preg_match('/<table.*?>(.*)$/is', $html, $match);
		$rows = $this->_extractChildren($match[1], 'tr');

		array_pop($rows);
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
		$result = new Result;
		$result->setState('QLD');

		preg_match('%_court">(.*?)</%', $tr_html, $match);
		$court_type = $match[1];
		$result->setCourtType($court_type);

		preg_match('%_filenumber">(.*?)</%', $tr_html, $match);
		$file_no = $match[1];
		$result->setCaseNumber($file_no);

		preg_match('%_originatinglocation">(.*?)</%', $tr_html, $match);
		$location = $match[1];

		$unique_id = $location . '-' . $court_type . '-' . $file_no;

		if (isset($this->done[$unique_id])) {
			$this->_log('info', 'Skipping ' . $unique_id . ' because it\'s already done');
			return;
		}

		$this->done[$unique_id] = 1;
		$result->setUniqueId($unique_id);

		preg_match('%_filename">(.*?)</%', $tr_html, $match);
		$result->setCaseName($match[1]);

		if (stripos('xxx', $result->getCaseName()) !== false) {
			$this->_log('info', 'Skipping ' . $unique_id . ' because it\'s a placeholder');
			return;
		}

		preg_match('%_currentlocation">(.*?)</%', $tr_html, $match);
		$result->setSuburb($match[1]);
		$this->suburb = $match[1];

		list($details_html, $effective_url) = $this->_fetchDetails($result, $tr_html);

		$result->setUrl($effective_url);

		$application = new Application;

		preg_match('%_proceedingtype">(.*?)</%', $details_html, $match);
		$application->setTitle($match[1]);
		$result->setCaseType($match[1]);
		$result->setJurisdiction(strpos($match[1], 'Criminal') === false ? 'Civil' : 'Criminal');

		preg_match('%_datefiled">(.*?)</%', $details_html, $match);
		$date = DateTime::createFromFormat('j/m/Y', $match[1]);
		$application->setDateFiled($date->format('Y-m-d 00:00:00'));

		$this->_scrapeParties($application, $details_html);
		$this->_scrapeEvents($application, $details_html);
		$this->_scrapeDocuments($application, $details_html);

		if (strpos($details_html, 'There are no Related files on this file') === false) {
			$result->setRelatedCases('To do');
		}

		$result->addApplication($application);

		$this->ret($result);
	}

	/**
	 * Fetches and returns the details page for the given proceeding.
	 *
	 * @param  Result  $result
	 * @param  string  $tr_html
	 * @return array
	 */
	private function _fetchDetails(Result $result, $tr_html)
	{
		preg_match('%_originatinglocation">(.*?)</%', $tr_html, $match);
		$location = $match[1];

		$params = [
			'Location'   => $this->locations[$location],
			'Court'      => ($result->getCourtType() == 'District' ? 'DISTR' : 'SUPRE'),
			'Filenumber' => $result->getCaseNumber(),
		];

		$url = 'http://apps.courts.qld.gov.au/esearching/FileDetails.aspx?' . http_build_query($params);

		$this->_log('info', 'Requesting ' . $url);
		$response = $this->transport->get($url);

		return [$response->getBody(), $url];
	}

	/**
	 * Scrapes the parties from a proceeding's HTML.
	 *
	 * It looks for a table with an attribute that ends in "_PartyGrid".
	 *
	 * @param  Application  $application
	 * @param  string       $html
	 */
	private function _scrapeParties(Application $application, $html)
	{
		preg_match('%_PartyGrid"[^>]*>(.*?)</table>%is', $html, $matches);

		if (empty($matches)) {
			return;
		}

		$rows = explode('<tr', $matches[1]);
		unset($rows[0]); // empty string
		unset($rows[1]); // header row
		$rows = array_values($rows);

		foreach ($rows as $index => $row) {
			$this->_scrapeParty($application, $row, $index);
		}
	}

	/**
	 * Scrapes a single party from a party table row.
	 *
	 * @param  Application  $application
	 * @param  string       $tr_html
	 * @param  int          $index
	 */
	private function _scrapeParty($application, $tr_html, $index)
	{
		$tr_html = str_replace('&nbsp;', ' ', $tr_html);

		preg_match_all('%<td.*?>(.*?)</td>%is', $tr_html, $matches);
		$matches[1] = array_map('html_entity_decode', $matches[1]);
		$matches[1] = array_map('trim', $matches[1]);
		list($surname_or_company, $first_name, $acn, $role, $representative) = $matches[1];

		$party = new Party;

		if ($first_name) {
			$party->setIndividualNames($first_name, $surname_or_company);
		} else {
			$party->setCompanyName($surname_or_company);
		}

		$party->setRole($role);
		$party->setAcn($acn);

		if ($representative) {
			$party->setRepName($representative);
		}

		$application->addParty($party);
	}

	/**
	 * Scrapes the events from a proceeding's HTML.
	 *
	 * It looks for a table with an attribute that ends in "_EventGrid".
	 *
	 * @param  Application  $application
	 * @param  string       $html
	 */
	private function _scrapeEvents(Application $application, $html)
	{
		preg_match('%_EventGrid"[^>]*>(.*?)</table>%is', $html, $matches);

		if (empty($matches)) {
			return;
		}

		$rows = explode('<tr', $matches[1]);
		unset($rows[0]); // empty string
		unset($rows[1]); // header row

		foreach ($rows as $row) {
			try {
				$application->addHearing($this->_scrapeEvent($row));
			} catch (Exception $e) {
				$this->_logException($e);
			}
		}
	}

	/**
	 * Scrapes a single event from an event table row.
	 *
	 * @param  string  $tr_html
	 */
	private function _scrapeEvent($tr_html)
	{
		$tr_html = str_replace('&nbsp;', ' ', $tr_html);

		preg_match_all('%<td.*?>(.*?)</td>%is', $tr_html, $matches);
		$matches[1] = array_map('html_entity_decode', $matches[1]);
		$matches[1] = array_map('trim', $matches[1]);
		list($date, $type, $diary_type, $resource, $result) = $matches[1];

		$date = trim(strip_tags($date));
		$datetime = DateTime::createFromFormat('d/m/Y', $date, new DateTimeZone($this->timezone));

		if (!$datetime) {
			throw new Exception('Unable to parse date from "' . $date . '"');
		}

		$hearing = new Hearing;
		$hearing->setDateTime($datetime->format('Y-m-d'));
		$hearing->setType($type);
		$hearing->setCourtSuburb($this->suburb);
		$hearing->setOfficer($resource);
		$hearing->setOutcome($result);

		return $hearing;
	}

	/**
	 * Scrapes the documents from a proceeding's HTML.
	 *
	 * It looks for a table with an attribute that ends in "_DocumentGrid".
	 *
	 * @param  Application  $application
	 * @param  string       $html
	 */
	private function _scrapeDocuments(Application $application, $html)
	{
		preg_match('%_DocumentGrid"[^>]*>(.*?)</table>%is', $html, $matches);

		if (empty($matches)) {
			return;
		}

		$rows = explode('<tr', $matches[1]);
		unset($rows[0]); // empty string
		unset($rows[1]); // header row

		foreach ($rows as $row) {
			$application->addDocument($this->_scrapeDocument($row));
		}
	}

	/**
	 * Scrapes a single document from a document table row.
	 *
	 * @param  string  $tr_html
	 */
	private function _scrapeDocument($tr_html)
	{
		$tr_html = str_replace('&nbsp;', ' ', $tr_html);

		preg_match_all('%<td.*?>(.*?)</td>%is', $tr_html, $matches);
		$matches[1] = array_map('html_entity_decode', $matches[1]);
		$matches[1] = array_map('trim', $matches[1]);
		list($doc_no, $date_filed, $type, $description, $filed_by, $pages) = $matches[1];

		$date = DateTime::createFromFormat('d/m/Y', $date_filed, new DateTimeZone($this->timezone));

		$document = new Document;
		$document->setDateTime($date->format('Y-m-d 00:00:00'));
		$document->setTitle($type);
		$document->setDescription($description);
		$document->setFiledBy($filed_by);

		return $document;
	}

}
