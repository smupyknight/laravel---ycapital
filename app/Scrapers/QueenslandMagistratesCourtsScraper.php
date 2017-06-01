<?php
namespace App\Scrapers;

use App\ScrapeResults\Result;
use App\ScrapeResults\Application;
use App\ScrapeResults\Hearing;
use App\ScrapeResults\Party;
use DateTime;
use DateTimeZone;
use ErrorException;
use Storage;

/**
 * @see http://www.courts.qld.gov.au/__external/CourtsLawList/BrisbaneMagCourt.pdf
 */
class QueenslandMagistratesCourtsScraper extends Scraper
{

	protected $timezone = 'Australia/Brisbane';

	/**
	 * The URL of the current PDF being scraped.
	 *
	 * @var string
	 */
	protected $url = null;

	/**
	 * The location/city of the current PDF being scraped.
	 *
	 * @var string
	 */
	protected $location = null;

	/**
	 * The date of the current PDF being scraped.
	 *
	 * @var DateTime
	 */
	protected $date = null;

	/**
	 * Scrapes information out of PDFs.
	 *
	 * @see http://www.courts.qld.gov.au/daily-law-lists (Magistrates Courts heading)
	 * @return array
	 */
	protected function _scrape()
	{
		$pdfs = [
			'BeenleighCourt.pdf',
			'BrisbaneArrestCourt.pdf',
			'BrisbaneMagCourt.pdf',
			'CabooltureCourt.pdf',
			'CairnsCourt.pdf',
			'IpswichCourt.pdf',
			'MackayCourt.pdf',
			'MaroochydoreCourt.pdf',
			'MountIsaCourt.pdf',
			'PineRiversCourt.pdf',
			'RockhamptonCourt.pdf',
			'SouthportCourt.pdf',
			'ToowoombaCourt.pdf',
			'TownsvilleCourt.pdf',
		];

		foreach ($pdfs as $filename) {
			$this->url = 'http://www.courts.qld.gov.au/__external/CourtsLawList/' . urlencode($filename);

			$this->_log('info', 'Requesting ' . $this->url);
			$pdf = $this->transport->get($this->url)->getBody();

			try {
				$this->_scrapePdf($pdf);
			} catch (ErrorException $e) {
				$this->_logException($e);
			}

			Storage::put('scrapers/qld-magistrates-' . $this->date->format('Y-m-d') . '-' . $filename, $pdf->__toString());
		}
	}

	/**
	 * Scrapes the records out of the PDF file.
	 *
	 * @param  string $pdf
	 * @return array
	 */
	private function _scrapePdf($pdf)
	{
		$text = $this->_getTextFromPdf($pdf);

		preg_match('/^(.*?) - ([0-9]+ \S+ [0-9]{4})$/m', $text, $match);

		$this->location = $match[1];
		$this->date = DateTime::createFromFormat('d F Y', $match[2], new DateTimeZone($this->timezone));

		preg_match_all('/^([^\n,]+,[^\n]+)\s{10,}([0-9]+)\s{10,}([0-9]+):([0-9]{2})(AM|PM)$/m', $text, $matches, PREG_SET_ORDER);

		foreach ($matches as $match) {
			try {
				$this->_scrapeProceeding($match);
			} catch (ErrorException $e) {
				$this->_logException($e);
			}
		}
	}

	/**
	 * Scrapes a single proceeding from a match in the PDF.
	 *
	 * @param  string $match
	 * @return Result
	 */
	private function _scrapeProceeding($match)
	{
		list($subject, $name, $court_no, $hour, $minute, $meridian) = $match;

		$name = trim($name);

		$result = new Result;

		$result->setState('QLD');
		$result->setCourtType('Magistrates');
		$result->setCaseName($name);
		$result->setCaseType($this->_isCorporate($name) ? 'Corporate' : 'Individual');
		$result->setJurisdiction(strpos($name, 'Criminal') !== false ? 'Criminal' : 'Civil');
		$result->setSuburb($this->location);
		$result->setUrl($this->url);

		$application = new Application;

		$hearing = new Hearing;
		$hearing->setCourtSuburb($this->location);
		$hearing->setCourtRoom($court_no);

		$date_value = $this->date->format('Y-m-d') . ' ' . $hour . ':' . $minute . $meridian;
		$date = DateTime::createFromFormat('Y-m-d g:iA', $date_value, new DateTimeZone($this->timezone));
		$hearing->setDateTime($date);

		$result->setUniqueId(md5(implode('|', [$name, $this->location, $court_no, "$hour:$minute$meridian"])));

		$party = new Party;

		if (preg_match('/^(\S+), (.+)$/', $name, $match)) {
			$party->setIndividualNames($match[2], $match[1]);
		} else {
			$party->setName($name);
		}

		$party->setRole('Defendant');

		$application->addHearing($hearing);
		$application->addParty($party);
		$result->addApplication($application);

		$this->ret($result);
	}

	private function _isCorporate($name)
	{
		if (stripos($name, 'pty') !== false) return true;
		if (stripos($name, 'ltd') !== false) return true;
		if (stripos($name, 'limited') !== false) return true;
		if (stripos($name, 'trust') !== false) return true;

		return false;
	}

}
