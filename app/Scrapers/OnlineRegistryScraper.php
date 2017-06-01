<?php
namespace App\Scrapers;

use App\ScrapeResults\Result;
use App\ScrapeResults\Application;
use App\ScrapeResults\Hearing;
use App\ScrapeResults\Party;
use DateTime;
use DateTimeZone;
use DateInterval;
use ErrorException;

/**
 * @see https://onlineregistry.lawlink.nsw.gov.au/content/court-lists
 */
class OnlineRegistryScraper extends Scraper
{

	protected $timezone = 'Australia/Sydney';

	/**
	 * Scrapes results from the Online Registry website.
	 *
	 * @return array
	 */
	protected function _scrape()
	{
		$offset = 0;

		do {
			$params = [
				'startDate' => $this->start_date->format('Y-m-d'),
				'sortField' => 'date,time,location',
				'sortOrder' => 'ASC',
				'count'     => 1000,
				'offset'    => $offset,
			];

			$url = 'https://onlineregistry.info/courtlistsearchlistings?' . http_build_query($params);

			$this->_log('info', 'Requesting ' . $url);
			$body = $this->transport->get($url)->getBody();

			$json = json_decode($body);
			$this->_scrapePage($json);

			$offset += 1000;
		} while ($this->_hasNextPage($json));
	}

	/**
	 * Determines if the results has another page or not.
	 *
	 * @param  array  $json
	 * @return bool
	 */
	private function _hasNextPage($json)
	{
		return ($json->offset + $json->count < $json->total);
	}

	/**
	 * Scrapes the results from the JSON data.
	 *
	 * @param  array $json
	 * @return array
	 */
	private function _scrapePage($json)
	{
		foreach ($json->hits as $hit) {
			try {
				$this->_scrapeProceeding($hit);
			} catch (ErrorException $e) {
				$this->_logException($e);
			}
		}
	}

	/**
	 * Scrapes an individual proceeding.
	 *
	 * @param  array $fields
	 * @return ProceedingScrapeResult
	 */
	private function _scrapeProceeding($fields)
	{
		$result = new Result;
		$result->setState('NSW');
		$result->setCourtType(str_replace(' Court', '', $fields->scm_jurisdiction_court_group));
		$result->setCaseName(trim($fields->case_title));
		$result->setCaseType($fields->jl_listing_type_ds);
		$result->setCaseNumber($fields->scm_case_number);
		$result->setUniqueId($fields->scm_case_number);
		$result->setJurisdiction($fields->scm_jurisdiction_type);
		$result->setUrl('https://onlineregistry.lawlink.nsw.gov.au/content/court-lists#/detail/' . $fields->id . '/');

		$application = new Application;
		$application->setType($fields->jl_listing_type_ds);

		$hearing = new Hearing;
		$hearing->setType($fields->jl_listing_type_ds);
		$hearing->setCourtSuburb($fields->location);
		$result->setSuburb($fields->location);

		if ($fields->time_listed) {
			$session_time = DateTime::createFromFormat('d M, g:i a', $fields->scm_date . ', ' . $fields->time_listed, new DateTimeZone($this->timezone));
		} else {
			$session_time = DateTime::createFromFormat('d M', $fields->scm_date)->format('Y-m-d');
		}
		$hearing->setDateTime($session_time);

		if (isset($fields->court_room_name)) {
			$hearing->setCourtRoom($fields->court_room_name);
		}

		if (isset($fields->court_house_name)) {
			$hearing->setCourtName($fields->court_house_name);
		}

		if (isset($fields->address)) {
			$hearing->setCourtAddress($fields->address);
		}

		if (isset($fields->scm_phone)) {
			$hearing->setCourtPhone($fields->scm_phone);
		}

		if (isset($fields->list_number)) {
			$hearing->setListNumber($fields->list_number);
		}

		if (isset($fields->{'officers.display_name'})) {
			$officer = $fields->{'officers.display_name'};

			if (is_array($officer)) {
				$officer = implode(', ', $officer);
			}

			$hearing->setOfficer($officer);
		}

		$this->_addPartiesFromTitle($application, trim($fields->case_title));

		$application->addHearing($hearing);
		$result->addApplication($application);

		$this->ret($result);
	}

	/**
	 * Scrapes the parties from the case title.
	 *
	 * The title may be in the format "In the matter of DEFENDANT" or
	 * "PLAINTIFF v DEFENDANT".
	 *
	 * @param  string $title
	 * @return array
	 */
	private function _addPartiesFromTitle(Application $application, $title)
	{
		if (strpos($title, 'In the matter of ') === 0) {
			$party = new Party;
			$party->setName(substr($title, 17));
			$party->setRole('Defendant');
			$application->addParty($party);
			return;
		}

		$names = explode(' v ', $title);

		if (count($names) != 2) {
			return;
		}

		$party = new Party;
		$party->setName($names[0]);
		$party->setRole('Plaintiff');
		$application->addParty($party);

		$party = new Party;
		$party->setName($names[1]);
		$party->setRole('Defendant');
		$application->addParty($party);
	}

}
