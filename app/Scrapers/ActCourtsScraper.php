<?php
namespace App\Scrapers;

use App\ScrapeResults\Result;
use App\ScrapeResults\Application;
use App\ScrapeResults\Hearing;
use App\ScrapeResults\Party;
use Carbon\Carbon;
use ErrorException;

/**
 * The ACT courts website doesn't group its results by case number, meaning
 * there can be multiple results with the same case number but with a different
 * party or hearing date. The scraper works by requesting all pages, scraping
 * the three table cells out, then grouping them by case number and going from
 * there.
 *
 * @see http://www.courts.act.gov.au/supreme/lists
 * @see http://www.courts.act.gov.au/magistrates/lists
 * @see http://www.acat.act.gov.au/lists
 */
class ActCourtsScraper extends Scraper
{

	private $url_prefix = null;

	private $court_type = null;

	protected $timezone = 'Australia/Sydney';

	public function __construct($transport, $url_prefix, $court_type)
	{
		parent::__construct($transport);

		$this->url_prefix = $url_prefix;
		$this->court_type = $court_type;
	}

	/**
	 * Scrapes results from the ACT Courts website.
	 *
	 * @return array
	 */
	protected function _scrape()
	{
		$pages = $this->_fetchPages();
		$rows = $this->_scrapePages($pages);
		$rows = $this->_groupRows($rows);

		foreach ($rows as $case_id => $info) {
			$this->_scrapeRow($case_id, $info);
		}
	}

	/**
	 * Fetches each page of results and returns them as an array of HTML.
	 */
	private function _fetchPages()
	{
		$pages = [];
		$start = 1;

		do {
			$url = $this->url_prefix . '?num_ranks=1000&start_rank=' . $start;

			$this->_log('info', 'Requesting ' . $url);
			$html = $this->transport->get($url)->getBody()->__toString();
			$pages[] = $html;
			$start += 1000;
		} while ($this->_hasNextPage($html));

		return $pages;
	}

	/**
	 * Determines if the HTML contains a "next page" link.
	 *
	 * @param  string $html
	 * @return bool
	 */
	private function _hasNextPage($html)
	{
		return (strpos($html, 'rel="next"') !== false);
	}

	/**
	 * Scrapes the table cells out of the pages and returns them as a single
	 * array.
	 *
	 * Example return value:
	 * [
	 *     ['CR17/00003', 'John Smith', '13 Apr 2017 2:30 PM'],
	 *     ['CR17/00004', 'Jill Smith', '13 Apr 2017 3:30 PM'],
	 *     ['CR17/00003', 'John Smith', '23 May 2017 2:30 PM'],
	 * ]
	 */
	private function _scrapePages(array $pages)
	{
		$rows = [];

		foreach ($pages as $html) {
			preg_match('%<tbody.*?>(.*?)</tbody>%is', $html, $match);
			preg_match_all('%<tr[^>]*>(.*?)</tr>%is', $match[1], $matches);

			foreach ($matches[1] as $row) {
				preg_match_all('%<td[^>]*>(.*?)</td>%is', $row, $match);
				$rows[] = array_map('html_entity_decode', $match[1]);
			}
		}

		return $rows;
	}

	/**
	 * Groups the rows by case number.
	 *
	 * Example return value:
	 * [
	 *     'CR17/00003' => [
	 *         ['CR17/00003', 'John Smith', '13 Apr 2017 2:30 PM'],
	 *         ['CR17/00003', 'John Smith', '23 May 2017 2:30 PM'],
	 *     ],
	 *     'CR17/00004' => [
	 *         ['CR17/00004', 'Jill Smith', '13 Apr 2017 3:30 PM'],
	 *     ],
	 * ]
	 */
	private function _groupRows(array $rows)
	{
		$grouped = [];

		foreach ($rows as $row) {
			if (!isset($grouped[$row[0]])) {
				$grouped[$row[0]] = [];
			}

			$grouped[$row[0]][] = $row;
		}

		return $grouped;
	}

	/**
	 * Creates the scrape result from the case/group.
	 */
	private function _scrapeRow($case_no, $info)
	{
		$result = new Result;
		$result->setState('ACT');
		$result->setUniqueId($case_no);
		$result->setCaseNumber($case_no);
		$result->setCourtType($this->court_type);

		$application = new Application;

		$this->_determineParties($application, $info);
		$this->_determineHearings($application, $info);

		$result->addApplication($application);
		$result->setCaseName($this->_determineCaseName($info));

		$this->ret($result);
	}

	/**
	 * Explodes the name fields on " v " and " & ", filters out duplicates, and
	 * filters out the special "Anor" name (which means "and others").
	 *
	 * Names in the format "SURNAME, F" are interpreted as an individual.
	 */
	private function _determineParties($application, $info)
	{
		$party_names = [];

		foreach ($info as $row) {
			$things = preg_split('/( v )|( & )/', $row[1]);
			$party_names = array_merge($party_names, $things);
		}

		$party_names = array_unique($party_names);
		$party_names = array_filter($party_names, function($name) {
			return !in_array($name, ['Anor', 'Suppressed', 'ZZZ: Name Suppressed']);
		});

		foreach ($party_names as $party_name) {
			$party = new Party;

			if (preg_match('/^(\S+), (\S)$/', $party_name, $match)) {
				$party->setIndividualNames($match[2], $match[1]);
			} else {
				$party->setName($party_name);
			}

			$application->addParty($party);
		}
	}

	private function _determineHearings($application, $info)
	{
		$hearing_times = [];

		foreach ($info as $row) {
			$hearing_times[] = $row[2];
		}

		$hearing_times = array_unique($hearing_times);

		foreach ($hearing_times as $time) {
			$time = (new Carbon($time, $this->timezone))->setTimezone('UTC')->format('Y-m-d H:i:s');
			$hearing = new Hearing;
			$hearing->setDateTime($time);
			$application->addHearing($hearing);
		}
	}

	private function _determineCaseName($info)
	{
		$party_names = [];

		foreach ($info as $row) {
			$party_names[] = $row[1];
		}

		$party_names = array_unique($party_names);

		return implode(' VS ', $party_names);
	}

}
