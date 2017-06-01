<?php
namespace App\Scrapers;

use App\ScrapeResults\Result;
use App\ScrapeResults\Application;
use App\ScrapeResults\Hearing;
use App\ScrapeResults\Document;
use App\ScrapeResults\Party;
use App\ScrapeResults\Order;
use DateTime;
use DateTimeZone;
use ErrorException;
use Exception;
use Storage;
use App\Exceptions\DeadUrlException;

/**
 * @see https://www.comcourts.gov.au/public/esearch
 */
abstract class ComCourtsScraper extends Scraper
{

	protected $timezone = 'Australia/Sydney';

	private $court_name = null;

	protected function _scrapeUrl($url, $action_type = '')
	{
		$this->_log('info', 'Requesting ' . $url);
		$details_html = $this->transport->get($url)->getBody();

		if (strpos($details_html, 'id="frmLogin"') !== false) {
			throw new DeadUrlException;
		}

		$result = new Result;
		$result->setCourtType('Federal');
		$result->setUrl($url);

		$this->_scrapeDetails($result, $details_html);

		if (!$result->getCaseType()) {
			$result->setCaseType($action_type);
		}

		$result->setJurisdiction(strpos($result->getCaseType(), 'Criminal') !== false ? 'Criminal' : 'Civil');

		$this->ret($result);
	}

	private function _scrapeDetails(Result $result, $html)
	{
		$html = str_replace('&nbsp;', ' ', $html);

		preg_match_all('%<th.*?>(.*?):</th>\s*<td>(.*?)</td>%is', $html, $matches);
		$fields = array_combine($matches[1], $matches[2]);
		$fields = array_map('html_entity_decode', $fields);
		$fields = array_map('trim', $fields);

		$result->setCaseNumber($fields['Number']);
		$result->setCaseName($fields['Title']);
		$result->setUniqueId($fields['Number']);

		$this->court_name = $fields['Court'];

		$this->_scrapeApplications($result, $html);
	}

	private function _scrapeApplications(Result $result, $html)
	{
		preg_match_all('%<div class="col_coa_fed apps-row">(.*?)</div>%s', $html, $matches);
		$app_titles = $matches[1];

		preg_match_all('%<div class="col_type apps-row">(.*?)</div>%s', $html, $matches);
		$app_types = $matches[1];

		preg_match_all('%<div class="col_date apps-row">(.*?)</div>%s', $html, $matches);
		$app_dates = $matches[1];

		preg_match_all('%<div class="col_status apps-row">(.*?)</div>%s', $html, $matches);
		$app_statuses = $matches[1];

		preg_match_all('%id="eao_[0-9]+".*?(/file/Federal/[a-z0-9/]+)/%is', $html, $matches);
		$app_base_urls = $matches[1];

		foreach ($app_titles as $index => $title) {
			$title = trim(strip_tags($title));
			$title = strpos($title, 'filed by') === false ? $title : substr($title, 0, strpos($title, 'filed by'));

			$application = new Application;
			$application->setTitle($title);
			$application->setType($app_types[$index]);
			$application->setStatus($app_statuses[$index]);

			if (isset($app_dates[$index * 2]) && $app_dates[$index * 2]) {
				$date = DateTime::createFromFormat('d-M-Y', $app_dates[$index * 2]);
				$application->setDateFiled($date->format('Y-m-d'));
			}

			if (isset($app_dates[$index * 2 + 1]) && $app_dates[$index * 2 + 1]) {
				$date = DateTime::createFromFormat('d-M-Y', $app_dates[$index * 2 + 1]);
				$application->setDateFinalised($date->format('Y-m-d'));
			}

			$base_url = 'https://www.comcourts.gov.au' . $app_base_urls[$index];

			$this->_scrapeEvents($application, $base_url);
			$this->_scrapeDocuments($application, $base_url);
			$this->_scrapeParties($application, $base_url);

			$result->addApplication($application);

			if (!$result->getCaseType() && $application->getType()) {
				$result->setCaseType($application->getTitle() . ' - ' . $application->getType());
			}
		}
	}

	private function _scrapeEvents(Application $application, $base_url)
	{
		$url = $base_url . '/events_and_orders';
		$this->_log('info', 'Requesting ' . $url);
		$html = $this->transport->get($url)->getBody();

		if (strpos($html, '>No events<') !== false) {
			return;
		}

		$html = str_replace('&nbsp;', ' ', $html);

		preg_match_all('%<tr.*?>(.*?)</tr>%is', $html, $matches);
		$rows = $matches[1];
		array_shift($rows);

		foreach ($rows as $row) {
			preg_match_all('%<td.*?>(.*?)</td>%is', $row, $matches);

			if (count($matches[1]) != 9) {
				continue;
			}

			$matches[1] = array_map('trim', $matches[1]);
			list($details_link, $nothing, $date, $time, $reason, $officer, $location, $outcome, $orders) = $matches[1];

			$datetime = DateTime::createFromFormat('d-M-Y H:i', "$date $time", new DateTimeZone($this->timezone));

			$hearing = new Hearing;
			$hearing->setDateTime($datetime);
			$hearing->setType($reason);
			$hearing->setReason($reason);
			$hearing->setOfficer($officer);
			$hearing->setCourtName($this->court_name);
			$hearing->setCourtRoom($location);
			$hearing->setOutcome($outcome);

			if (preg_match('/, (.*?) Registry$/i', $this->court_name, $match)) {
				$hearing->setCourtSuburb($match[1]);
			}

			if (preg_match('%/file/Federal/[a-z0-9/]+/outcomes%i', $row, $match)) {
				$this->_scrapeOutcomes($hearing, $match[0]);
			}

			if (preg_match('%/file/Federal/[a-z0-9/]+/document/[0-9]+%i', $orders, $match)) {
				$this->_scrapeOrders($hearing, $match[0]);
			}

			$application->addHearing($hearing);
		}
	}

	private function _scrapeOutcomes(Hearing $hearing, $outcomes_url)
	{
		$url = 'https://www.comcourts.gov.au' . $outcomes_url;
		$this->_log('info', 'Requesting ' . $url);
		$html = $this->transport->get($url)->getBody();

		preg_match_all('%<td.*?>(.*?)</td>%is', $html, $matches);
		$matches[1] = array_map('trim', $matches[1]);
		$rows = array_chunk($matches[1], 2);

		foreach ($rows as $cells) {
			list($order_type, $decision_type) = $cells;

			$order = new Order;
			$order->setType($order_type);
			$order->setDecisionType($decision_type);
			$hearing->addOrder($order);
		}
	}

	private function _scrapeOrders(Hearing $hearing, $orders_url)
	{
		$url = 'https://www.comcourts.gov.au' . $orders_url;
		$this->_log('info', 'Requesting ' . $url);
		$pdf = $this->transport->get($url)->getBody();

		preg_match('/[0-9]+$/', $orders_url, $match);
		$doc_id = $match[0];

		$filename = 'scrapers/federal-1-' . $doc_id . '.pdf';

		Storage::put($filename, $pdf->__toString());

		$hearing->setOrdersFilename($filename);
	}

	private function _scrapeDocuments(Application $application, $base_url)
	{
		$url = $base_url . '/documents_filed';
		$this->_log('info', 'Requesting ' . $url);
		$documents_html = $this->transport->get($url)->getBody();

		preg_match_all('%<td.*?>(.*?)</td>%is', $documents_html, $matches);
		$matches[1] = array_map('trim', $matches[1]);
		$rows = array_chunk($matches[1], 5);

		foreach ($rows as $cells) {
			list($date, $time, $title, $filed_by) = $cells;

			$datetime = DateTime::createFromFormat('j-M-Y H:i', "$date $time", new DateTimeZone($this->timezone));

			$document = new Document;
			$document->setDateTime($datetime);
			$document->setTitle($title);
			$document->setFiledBy($filed_by);

			$application->addDocument($document);
		}
	}

	private function _scrapeParties(Application $application, $base_url)
	{
		$url = $base_url . '/parties';
		$this->_log('info', 'Requesting ' . $url);
		$html = $this->transport->get($url)->getBody();

		$normal_parties = [];
		$rep_parties = [];

		preg_match_all('%<tr.*?>.*?</tr>%is', $html, $matches);
		$rows = $matches[0];

		foreach ($rows as $row) {
			preg_match_all('%<td.*?>(.*?)</td>%is', $row, $matches);

			if (count($matches[1]) != 5) {
				continue;
			}

			$matches[1] = array_map('trim', $matches[1]);
			list($nothing, $nothing, $role, $name) = $matches[1];

			$role = trim(strip_tags($role));

			$party = new Party;
			$party->setName($name);
			$party->setRole($role);

			if (preg_match('%/file/Federal/[a-z0-9/]+/party_contact/[^\']+%i', $row, $match)) {
				$this->_scrapePartyContact($party, $match[0]);
			}

			if (strpos($role, 'Representative') !== false) {
				$rep_parties[] = $party;
			} else {
				$normal_parties[] = $party;
			}
		}

		foreach ($normal_parties as $party) {
			$found_rep = false;

			foreach ($rep_parties as $rep) {
				if ($rep->getRole() == 'Legal Representative ' . $party->getRole()) {
					$final_party = clone $party;
					$final_party->setRepName($rep->getName());
					$final_party->setAddress($rep->getAddress());
					$final_party->setPhone($rep->getPhone());
					$final_party->setFax($rep->getFax());

					$application->addParty($final_party);
					$found_rep = true;
				}
			}

			if (!$found_rep) {
				$application->addParty($party);
			}
		}
	}

	private function _scrapePartyContact(Party $party, $contact_link)
	{
		$url = 'https://www.comcourts.gov.au' . $contact_link;
		$this->_log('info', 'Requesting ' . $url);
		$html = $this->transport->get($url)->getBody();

		preg_match_all('%<th.*?>\s*(.*?):\s*</th>\s*<td.*?>\s*(.*?)\s*</td>%is', $html, $matches);
		$fields = array_combine($matches[1], $matches[2]);
		$fields = array_map('html_entity_decode', $fields);
		$fields = array_map('trim', $fields);

		$fields = array_merge(array_fill_keys(['Address','Business Fax','Business Phone'], ''), $fields);

		$fields['Address'] = preg_replace('/\s+/', ' ', $fields['Address']);

		$party->setAddress($fields['Address']);
		$party->setFax($fields['Business Fax']);
		$party->setPhone($fields['Business Phone']);
	}

}
