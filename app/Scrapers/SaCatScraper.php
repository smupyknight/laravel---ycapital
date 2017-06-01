<?php
namespace App\Scrapers;

use App\ScrapeResults\Result;
use App\ScrapeResults\Application;
use App\ScrapeResults\Hearing;
use App\ScrapeResults\Party;
use Carbon\Carbon;
use ErrorException;
use Exception;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @see http://www.sacat.sa.gov.au/bringing-a-case/upcoming-hearings-and-conferences
 */
class SaCatScraper extends Scraper
{

	protected $timezone = 'Australia/Adelaide';

	private $date = '';

	/**
	 * Runs the scrape.
	 */
	protected function _scrape()
	{
		$url = 'http://www.sacat.sa.gov.au/bringing-a-case/upcoming-hearings-and-conferences';

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
	 * Scrapes the date from the h2 element, then finds and iterates the table
	 * rows.
	 *
	 * The heading can contain non-breaking spaces (0xc2a0). These are replaced
	 * with spaces.
	 */
	private function _scrapePage(Crawler $crawler)
	{
		$heading = $crawler->filter('h2')->text();
		$heading = str_replace(chr(0xc2) . chr(0xa0), ' ', $heading);
		preg_match('/\d+ \S+ \d+$/', $heading, $match);
		$this->date = $match[0];

		$crawler->filter('tr')->each(function($row) {
			try {
				$this->_scrapeRow($row);
			} catch (ErrorException $e) {
				$this->_logException($e);
			}
		});
	}

	/**
	 * Scrapes a table row element.
	 */
	private function _scrapeRow(Crawler $row)
	{
		$cells = $row->filter('td');
		$time = trim($cells->eq(0)->text());
		$case_name = trim($cells->eq(1)->text());
		$case_number = trim($cells->eq(2)->text());
		$location = trim($cells->eq(3)->text());

		if (trim($time) == 'Time') { // heading row
			return;
		}

		$datetime = $this->_determineDateTime($time);

		$result = new Result;
		$result->setUniqueId(md5("$case_number|{$datetime->format('U')}"));
		$result->setState('SA');
		$result->setCourtType('CAT');
		$result->setCaseNumber($case_number);
		$result->setCaseName($case_name);
		$result->setUrl('http://www.sacat.sa.gov.au/bringing-a-case/upcoming-hearings-and-conferences');

		$application = new Application;

		$hearing = new Hearing;
		$hearing->setDatetime($datetime);

		if (preg_match('/^(.*?) ((Hearing|Mediation) Room \d+)$/', $location, $match)) {
			$hearing->setCourtAddress($match[1]);
			$hearing->setCourtRoom($match[2]);
		}

		$application->addHearing($hearing);
		$result->addApplication($application);

		$this->_scrapeParties($application, $case_name);

		$this->ret($result);
	}

	/**
	 * As of date of implementation, the court website lists the wrong month
	 * name in the heading, but the day name and number is correct.
	 *
	 * This function works around their mistake by getting the difference
	 * between their listed day and today, and recalculating their month if it's
	 * too different.
	 */
	private function _determineDateTime($time)
	{
		$today = Carbon::today($this->timezone);
		$website_date = Carbon::createFromFormat('j F Y h:i A', "{$this->date} $time", $this->timezone);

		if ($today->diffInDays($website_date) > 5) {
			// Keep adding months until the date is within 5 days of today, or
			// throw an exception if it jumps past today + 5 days.
			while ($today->diffInDays($website_date) > 5 && $website_date->isPast()) {
				$website_date->addMonth();
			}

			if ($today->diffInDays($website_date) > 5) {
				throw new Exception("Can't interpret date {$this->date}");
			}
		}

		return $website_date;
	}

	/**
	 * Scrapes the parties from the case name.
	 *
	 * An example case name is "AAA & BBB & CCC"
	 */
	private function _scrapeParties(Application $application, $case_name)
	{
		$full_names = explode(' & ', $case_name);

		foreach ($full_names as $full_name) {
			$party = new Party;

			if (preg_match('/^(\S+)\s*, (.+)$/', $full_name, $match)) {
				$party->setIndividualNames($match[2], $match[1]);
			} else {
				$party->setName($full_name);
			}

			$application->addParty($party);
		}
	}

}
