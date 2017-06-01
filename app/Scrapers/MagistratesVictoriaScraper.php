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
 * @see https://dailylists.magistratesvic.com.au/EFAS/CaseSearch
 */
class MagistratesVictoriaScraper extends Scraper
{

	protected $timezone = 'Australia/Melbourne';

	/**
	 * Scrapes results from the Online Registry website.
	 *
	 * @return array
	 */
	protected function _scrape()
	{
		foreach (['CIV','CRI'] as $case_type) {
			$page = 0;

			do {
				$page++;

				$params = [
					'sort'                        => 'CourtLinkCaseNo-desc',
					'page'                        => $page,
					'pageSize'                    => 5000,
					'group'                       => '',
					'filter'                      => '',
					'CaseType'                    => $case_type,
					'CourtID'                     => '',
					'HearingDate'                 => '',
					'CourtLinkCaseNo'             => '',
					'PlaintiffInformantApplicant' => '',
					'DefendantAccusedRespondent'  => '',
				];

				$url = 'https://dailylists.magistratesvic.com.au/EFAS/CaseSearch_GridData';

				$this->_log('info', "Requesting $url (CaseType=$case_type, page=$page)");
				$body = $this->transport->post($url, ['form_params' => $params])->getBody();

				try {
					$json = json_decode($body);
					$this->_scrapePage($json);
				} catch (ErrorException $e) {
					$this->_logException($e);
				}
			} while ($this->_hasNextPage($json, $page));
		}
	}

	/**
	 * Determines if the results has another page or not.
	 *
	 * @param  StdClass $json
	 * @param  int      $page
	 * @return bool
	 */
	private function _hasNextPage($json, $page)
	{
		return (($page - 1) * 5000 + count($json->Data) < $json->Total);
	}

	/**
	 * Scrapes the results from the JSON data.
	 *
	 * @param  array $json
	 * @return array
	 */
	private function _scrapePage($json)
	{
		foreach ($json->Data as $fields) {
			try {
				$this->_scrapeProceeding($fields);
			} catch (ErrorException $e) {
				$this->_logException($e);
			}
		}
	}

	/**
	 * Scrapes an individual proceeding.
	 *
	 * @param  array $fields
	 * @return Result
	 */
	private function _scrapeProceeding($fields)
	{
		preg_match('%/Date\(([0-9]+)000\)/%', $fields->HearingDateTime, $match);
		$session_time = DateTime::createFromFormat('U', $match[1], new DateTimeZone($this->timezone));

		$result = new Result;
		$result->setState('VIC');
		$result->setCourtType('Magistrates');
		$result->setJurisdiction($fields->CaseType == 'CIV' ? 'Civil' : 'Criminal');
		$result->setUniqueId($fields->CaseType . '-' . $fields->CaseID);
		$result->setCaseNumber($fields->CourtLinkCaseNo);
		$result->setCaseName($fields->PlaintiffInformantApplicant . ' v. ' . $fields->DefendantAccusedRespondent);
		$result->setUrl('https://dailylists.magistratesvic.com.au/EFAS/Case' . $fields->CaseType . '?CaseID=' . $fields->CaseID);

		$application = new Application;

		$hearing = new Hearing;
		$hearing->setCourtName($fields->Court);
		$hearing->setDateTime($session_time);

		$pos = strpos($fields->Court, ' Magistrates');

		if ($pos !== false) {
			$location = substr($fields->Court, 0, $pos);
			$hearing->setCourtSuburb($location);
			$result->setSuburb($location);
		}

		$party = new Party;
		$party->setName($fields->PlaintiffInformantApplicant);
		$party->setRole('Plaintiff');
		$application->addParty($party);

		$party = new Party;
		$party->setName($fields->DefendantAccusedRespondent);
		$party->setRole('Defendant');
		$application->addParty($party);

		$this->_log('info', 'Requesting ' . $result->getUrl());
		$details_html = $this->transport->get($result->getUrl())->getBody();

		if ($value = $this->_getValue('Complaint Nature', $details_html)) {
			$application->setTitle($value);
			$result->setCaseType($value);
		}

		$matter_description = '';
		if ($value = $this->_getValue('Matter Description', $details_html)) {
			$matter_description = $value;
			$application->setType($value);
		}

		if (($value = $this->_getValue('Plaintiff Representative', $details_html)) && $value != 'Not Represented') {
			$party = new Party;
			$party->setName($value);
			$party->setRole('Plaintiff Representative');
			$application->addParty($party);
		}

		if (($value = $this->_getValue('Defendant Representative', $details_html)) && $value != 'Not Represented') {
			$party = new Party;
			$party->setName($value);
			$party->setRole('Defendant Representative');
			$application->addParty($party);
		}

		$hearing_type = '';
		if ($value = $this->_getValue('Hearing Type', $details_html)) {
			$hearing_type = $value;
			if($result->jurisdiction==="Criminal"){
				$result->setCaseType($value);
			}

		}

		if ($matter_description != '' || $hearing_type != '') {
			$hearing->setType($matter_description.' - '.$hearing_type);
		}

		$hearing->setOutcome($this->_getValue('Plea', $details_html));

		if ($value = $this->_getValue('Informant', $details_html)) {
			$party = new Party;

			if (preg_match('/^(\S+), (.+)$/', $value, $match)) {
				$party->setIndividualNames($match[2], $match[1]);
			} else {
				$party->setName($value);
			}

			$party->setRole('Informant - ' . $fields->InformantDivision);
			$application->addParty($party);
		}

		$application->addHearing($hearing);
		$result->addApplication($application);

		$this->ret($result);
	}

	private function _getValue($label, $html)
	{
		preg_match('%<td>.*?' . $label . '.*?</td>\s*<td>(.*?)</td>%is', $html, $match);

		if (!isset($match[1])) {
			return '';
		}

		return trim($match[1]);
	}

}
