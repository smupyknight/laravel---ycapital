<?php
namespace App\Scrapers;

use App\ScrapeResults\Result;
use App\ScrapeResults\Application;
use App\ScrapeResults\Hearing;
use App\ScrapeResults\Party;
use Carbon\Carbon;
use ErrorException;
use Storage;

/**
 * Scrapes a single PDF file.
 *
 * @see http://www.ntcat.nt.gov.au/documents/Hearing-List.pdf
 */
class NtCatScraper extends Scraper
{

	protected $timezone = 'Australia/Darwin';

	private $override_pdf = [];

	public function overridePdf(array $content)
	{
		$this->override_pdf = $content;
	}

	/**
	 * Runs the scrape.
	 */
	protected function _scrape()
	{
		if ($this->override_pdf) {
			foreach ($this->override_pdf as $pdf) {
				$this->_scrapePdf($pdf);
			}

			return;
		}

		$url = 'http://www.ntcat.nt.gov.au/documents/Hearing-List.pdf';

		$this->_log('info', 'Requesting ' . $url);
		$pdf = $this->transport->get($url)->getBody();

		$date = Carbon::now($this->timezone);
		Storage::put('scrapers/nt-' . $date->format('Y-m-d') . '.pdf', $pdf->__toString());

		try {
			$this->_scrapePdf($pdf);
		} catch (ErrorException $e) {
			$this->_logException($e);
		}
	}

	/**
	 * Scrapes the records out of the PDF file.
	 *
	 * The PDF file contains some odd \f characters. These are removed.
	 *
	 * @param  string $pdf
	 */
	private function _scrapePdf($pdf)
	{
		$text = $this->_getTextFromPdf($pdf);

		$text = str_replace("\f", '', $text);
		$lines = explode("\n", $text);
		$lines = array_filter($lines);
		$lines = array_slice($lines, 3); // remove header lines

		$grouped = $this->_groupLines($lines);

		foreach ($grouped as $group) {
			try {
				$columns = $this->_parseColumns($group);
				$this->_scrapeProceeding($columns);
			} catch (ErrorException $e) {
				$this->_logException($e);
			}
		}
	}

	/**
	 * Converts an array of lines that looks like this:
	 *
	 * [
	 *     "Martini v Fauntleroy            Small Claims Act   27 March     Casuarina   08:30am   Compulsory",
	 *     "                                                   2017                               Conference",
	 *     "Sassy Blue & Lime Splice v      Residential        27 March     Casuarina   9:00am    Hearing",
	 *     "Joyce & Fejo-Tasker             Tenancies Act      2017",
	 *     "A matter under the              Guardianship of    27 March     Casuarina   10:00am   Hearing (Closed",
	 *     "Guardianship of Adults Act –    Adults Act         2017                               Hearing)",
	 *     "21042642",
	 *     "A matter under the              Guardianship of    27 March     Casuarina   10:00am   Hearing (Closed",
	 *     "Guardianship of Adults Act –    Adults Act         2017                               Hearing)",
	 *     "21512532",
	 * ]
	 *
	 * ...into this:
	 *
	 * [
	 *     0 => [
	 *         "Martini v Fauntleroy            Small Claims Act   27 March     Casuarina   08:30am   Compulsory",
	 *         "                                                   2017                               Conference",
	 *     ],
	 *     1 => [
	 *         "Sassy Blue & Lime Splice v      Residential        27 March     Casuarina   9:00am    Hearing",
	 *         "Joyce & Fejo-Tasker             Tenancies Act      2017",
	 *     ],
	 *     2 => [
	 *         "A matter under the              Guardianship of    27 March     Casuarina   10:00am   Hearing (Closed",
	 *         "Guardianship of Adults Act –    Adults Act         2017                               Hearing)",
	 *         "21042642",
	 *     ],
	 *     3 => [
	 *         "A matter under the              Guardianship of    27 March     Casuarina   10:00am   Hearing (Closed",
	 *         "Guardianship of Adults Act –    Adults Act         2017                               Hearing)",
	 *         "21512532",
	 *     ],
	 * ]
	 *
	 * It's grouping them by proceeding, and it identifies a new proceeding by
	 * the presence of the time field.
	 */
	private function _groupLines(array $lines)
	{
		$grouped = [];
		$current_group = [];

		foreach ($lines as $line) {
			if (preg_match('/\d:\d\d(a|p)m/', $line) && $current_group) {
				$grouped[] = $current_group;

				$current_group = [];
			}

			$current_group[] = $line;
		}

		return $grouped;
	}

	/**
	 * Converts an array of lines like this:
	 *
	 * [
	 *     "A matter under the              Guardianship of    27 March     Casuarina   10:00am   Hearing (Closed",
	 *     "Guardianship of Adults Act -    Adults Act         2017                               Hearing)",
	 *     "21512532",
	 * ]
	 *
	 * ...into this:
	 *
	 * [
	 *     "A matter under the Guardianship of Adults Act - 21512532",
	 *     "Guardianship of Adults Act",
	 *     "27 March 2017",
	 *     "Casuarina",
	 *     "10:00am",
	 *     "Hearing (Closed Hearing)",
	 * ]
	 *
	 * This is done by finding the offsets of each column in the first line,
	 * then matching the following lines based on that offset.
	 *
	 * When the lines span over two pages, the offsets don't line up. In this
	 * case an exception is throw and the record is skipped.
	 */
	private function _parseColumns(array $lines)
	{
		$columns = preg_split('/\s{2,}/', $lines[0]);
		$offsets = [];
		$next_offset = 0;

		foreach ($columns as $column) {
			$offset = mb_strpos($lines[0], $column, $next_offset);
			$offsets[] = $offset;
			$next_offset = $offset + strlen($column);
		}

		foreach (array_slice($lines, 1) as $line) {
			$line_columns = preg_split('/\s{2,}/', trim($line));
			$next_offset = 0;

			foreach ($line_columns as $line_column) {
				$offset = mb_strpos($line, $line_column, $next_offset);

				$index = array_search($offset, $offsets);

				if ($index === false) {
					throw new ErrorException("Case name \"{$columns[0]}\" spans two pages - skipping");
				}

				$columns[$index] .= ' ' . $line_column;

				$next_offset = $offset + strlen($line_column);
			}
		}

		return $columns;
	}

	/**
	 * Scrapes a single proceeding from a match in the PDF.
	 *
	 * Proceedings with the following jurisdictions are skipped:
	 * "Guardianship of Adults Act"
	 * "Residential Tenancies Act"
	 */
	private function _scrapeProceeding(array $columns)
	{
		list($matter_name, $jurisdiction, $date, $location, $time, $matter_type) = $columns;

		if ($jurisdiction == 'Guardianship of Adults Act') {
			return;
		}

		if ($jurisdiction == 'Residential Tenancies Act') {
			return;
		}

		$result = new Result;
		$result->setUniqueId(md5("$matter_name|$date|$time|$location"));
		$result->setState('NT');
		$result->setCourtType('CAT');
		$result->setCaseName($matter_name);
		$result->setCaseType($jurisdiction);
		$result->setSuburb($location);
		$result->setJurisdiction('Civil');
		$result->setUrl('http://www.ntcat.nt.gov.au/documents/Hearing-List.pdf');

		$application = new Application;

		$hearing = new Hearing;
		$hearing->setCourtSuburb($location);
		$hearing->setType($matter_type);
		$hearing->setDateTime(Carbon::createFromFormat('d F Y h:ia', "$date $time", $this->timezone));

		$application->addHearing($hearing);

		$this->_scrapeParties($application, $matter_name);
		$result->addApplication($application);

		$this->ret($result);
	}

	/**
	 * Scrapes the parties from the matter name.
	 *
	 * An example case name is "AAA v BBB & CCC"
	 *
	 * If the matter name is one word v one word, both party types will be
	 * individual. Anything else will make the party type Other.
	 */
	private function _scrapeParties(Application $application, $matter_name)
	{
		$party_type = preg_match('/^\S+ v \S+$/', $matter_name) ? 'Individual' : 'Other';

		$sides = explode(' v ', $matter_name);

		if (count($sides) != 2) {
			$party = new Party;
			$party->setName($matter_name);
			$party->setType($party_type);
			$party->setRole('Defendant');
			$application->addParty($party);
			return;
		}

		$this->_scrapePartySide($application, $sides[0], $party_type, 'Defendant');
		$this->_scrapePartySide($application, $sides[1], $party_type, 'Plaintiff');
	}

	private function _scrapePartySide(Application $application, $side, $type, $role)
	{
		$names = explode(' & ', $side);

		foreach ($names as $name) {
			$party = new Party;
			$party->setName($name);
			$party->setType($type);
			$party->setRole($role);

			$application->addParty($party);
		}
	}

}
