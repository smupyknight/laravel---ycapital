<?php
namespace App\Scrapers;

use App\ScrapeResults\Result;
use App\ScrapeResults\Application;
use App\ScrapeResults\Hearing;
use App\ScrapeResults\Party;
use Carbon\Carbon;
use ErrorException;
use Storage;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @see http://ecourts.justice.wa.gov.au/ecourtsportal/courtlistings/todayscourtlistings
 */
class EcourtsWaScraper extends Scraper
{

	protected $timezone = 'Australia/Perth';

	/**
	 * Runs the scrape.
	 */
	protected function _scrape()
	{
		$url = 'http://ecourts.justice.wa.gov.au/ecourtsportal/courtlistings/todayscourtlistings';

		$this->_log('info', 'Requesting ' . $url);
		$html = $this->transport->get($url)->getBody();

		$crawler = new Crawler($html->__toString());

		try {
			$this->_scrapePage($crawler);
		} catch (ErrorException $e) {
			$this->_logException($e);
		}
	}

	/**
	 * Looks for the State Administrative Tribunal heading in the HTML, then
	 * finds the .panel-group elements after the heading and before the next
	 * heading.
	 */
	private function _scrapePage(Crawler $crawler)
	{
		$panel_groups = $crawler->filter('div[data-id="all-listings"] > div')
			->reduce(function($node) {
				static $in_sat_section = false;

				if ($node->attr('class') == 'panel-header') {
					$in_sat_section = ($node->html() == 'State Administrative Tribunal');
					return false;
				}

				return $in_sat_section;
			});

		$panel_groups->each(function($group) {
			$this->_scrapePanelGroup($group);
		});
	}

	/**
	 * Scrapes a .panel-group element from the HTML.
	 *
	 * The group consists of a court, jurisdiction and table rows.
	 *
	 * Rows within the "HR Hearing" subheading are ignored.
	 */
	private function _scrapePanelGroup(Crawler $group)
	{
		$court_info = $this->_scrapeCourtInfo($group);
		$jurisdiction = $group->filter('h5')->eq(0)->text() == 'Criminal Listing' ? 'Criminal' : 'Civil';

		$rows = $group->filter('tr');

		$rows->each(function($row) use($court_info, $jurisdiction) {
			static $heading;

			if ($row->filter('.row-header')->count()) {
				$heading = $row->filter('.row-header')->html();
				return;
			}

			if ($row->attr('class') == 'data-row' && $heading != 'HR Hearing') {
				try {
					$this->_scrapeRow($row, $heading, $court_info, $jurisdiction);
				} catch (ErrorException $e) {
					$this->_logException($e);
				}
			}
		});
	}

	private function _scrapeCourtInfo(Crawler $group)
	{
		$name = $group->filter('.list-header')->html();
		$full_address = $group->filter('.list-sub-header')->html();

		$parts = explode(',', $full_address);

		if (count($parts) == 1) {
			return [
				'name'    => $name,
				'address' => $full_address,
				'suburb'  => '',
			];
		}

		$suburb = array_pop($parts);
		$address = implode(',', $parts);

		return [
			'name'    => $name,
			'address' => trim($address),
			'suburb'  => trim($suburb),
		];
	}

	private function _scrapeRow(Crawler $row, $heading, array $court_info, $jurisdiction)
	{
		$datetime = $this->_scrapeDateTime($row);

		$matter_title = trim($row->filter('td')->eq(1)->text());
		$matter_no = trim($row->filter('td')->eq(2)->text());
		$floor_court = trim($row->filter('td')->eq(3)->text());

		$result = new Result;
		$result->setUniqueId(md5("$matter_no|{$datetime->format('U')}"));
		$result->setState('WA');
		$result->setCourtType('CAT');
		$result->setCaseNumber($matter_no);
		$result->setCaseName($matter_title);
		$result->setCaseType($heading);
		$result->setSuburb($court_info['suburb']);
		$result->setJurisdiction($jurisdiction);
		$result->setUrl('http://ecourts.justice.wa.gov.au/ecourtsportal/courtlistings/todayscourtlistings');

		$application = new Application;

		$hearing = new Hearing;
		$hearing->setDateTime($datetime);
		$hearing->setCourtName($court_info['name']);
		$hearing->setCourtAddress($court_info['address']);
		$hearing->setCourtSuburb($court_info['suburb']);
		$hearing->setCourtRoom($floor_court);

		$application->addHearing($hearing);
		$result->addApplication($application);

		$this->_scrapeParties($application, $matter_title);

		$this->ret($result);
	}

	private function _scrapeDateTime(Crawler $row)
	{
		$time = trim($row->filter('td')->eq(0)->text());
		list($hours, $mins) = explode(':', $time);

		$datetime = Carbon::today($this->timezone);
		$datetime->setTime($hours, $mins, 0);

		return $datetime;
	}

	/**
	 * Scrapes the parties from the matter title.
	 *
	 * An example case name is "AAA v BBB & CCC"
	 */
	private function _scrapeParties(Application $application, $matter_name)
	{
		$sides = explode(' v ', $matter_name);

		if (count($sides) != 2) {
			$this->_scrapePartySide($application, $sides, 'Defendant');
			return;
		}

		$this->_scrapePartySide($application, $sides[0], 'Defendant');
		$this->_scrapePartySide($application, $sides[1], 'Plaintiff');
	}

	private function _scrapePartySide(Application $application, $side, $role)
	{
		$names = explode(' & ', $side);

		foreach ($names as $name) {
			if ($name == 'Anor') {
				continue;
			}

			$party = new Party;

			if (preg_match('/^(\S+), (.+)$/', $name, $match)) {
				$party->setIndividualNames($match[2], $match[1]);
			} else {
				$party->setName($name);
			}

			$party->setRole($role);

			$application->addParty($party);
		}
	}

}
