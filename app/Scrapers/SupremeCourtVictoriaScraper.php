<?php
namespace App\Scrapers;

use App\ScrapeResults\Result;
use App\ScrapeResults\Application;
use App\ScrapeResults\Hearing;
use App\ScrapeResults\Party;
use DateTime;
use DateTimeZone;
use ErrorException;
use Exception;
use Storage;

/**
 * Scrapes proceedings out of the Victoria Supreme Court's PDF file.
 *
 * Their PDF file uses alignment rather than tables, which makes it difficult to
 * scrape. We convert the PDF to text while preserving layout, then tag each
 * line (eg. proceeding, division, justice). Adjacent lines are then grouped
 * together and processed as one.
 *
 * @see http://www.scvprobate.com.au/lists/scv_daily_list.pdf
 */
class SupremeCourtVictoriaScraper extends Scraper
{

	protected $timezone = 'Australia/Melbourne';

	/**
	 * The date of the PDF being scraped.
	 *
	 * @var DateTime
	 */
	protected $date = null;

	private $override_pdf = [];
	private $division;
	private $justice;
	private $courtroom;
	private $address;

	public function overridePdf(array $content)
	{
		$this->override_pdf = $content;
	}

	/**
	 * Scrapes information out of PDFs.
	 *
	 * @return array
	 */
	protected function _scrape()
	{
		if ($this->override_pdf) {
			foreach ($this->override_pdf as $pdf) {
				$this->_scrapePdf($pdf);
			}

			return;
		}

		$url = 'http://www.scvprobate.com.au/lists/scv_daily_list.pdf';

		$this->_log('info', 'Requesting ' . $url);
		$pdf = $this->transport->get($url)->getBody();

		$this->_scrapePdf($pdf);

		Storage::put('scrapers/vic-supreme-' . $this->date->format('Y-m-d') . '.pdf', $pdf->__toString());
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
		$text = $this->_indentPages($text);

		preg_match('/^\s+Supreme Court List for \S+ ([0-9]+ \S+ [0-9]{4})\s*$/m', $text, $match);
		$this->date = DateTime::createFromFormat('d F Y', $match[1], new DateTimeZone($this->timezone));

		$lines = explode("\n", $text);
		$lines = array_slice($lines, 11);

		$tagged = $this->_tagLines($lines);
		$groups = $this->_groupifyLines($tagged);

		foreach ($groups as $group) {
			try {
				$this->_processGroup($group['type'], $group['lines']);
			} catch (ErrorException $e) {
				$this->_logException($e);
			}
		}
	}

	/**
	 * Ensures every page has a two space indent.
	 *
	 * Most pages have a division heading. The PDF to text converter gives the
	 * division heading no ident and the rest of the page has a two space
	 * indent. When a page has no division heading, the converter omits the
	 * indent from everything on that page. So this adds it back.
	 *
	 * @param  string $text
	 * @return string
	 */
	private function _indentPages($text)
	{
		preg_match_all('/Page [0-9]+ of [0-9].*?Printed:/is', $text, $matches);

		foreach ($matches[0] as $match) {
			if (preg_match('/^[0-9]{1,2}:[0-9]{2}/m', $match)) {
				$new = str_replace("\n", "\n  ", $match);
				$text = str_replace($match, $new, $text);
			}
		}

		return $text;
	}

	/**
	 * Iterates through each line and identifies it based on the content of the
	 * line and the type of the previous line.
	 *
	 * @param  array  $lines
	 * @return array
	 */
	private function _tagLines(array $lines)
	{
		$tagged = [];
		$last_line_type = null;

		foreach ($lines as $line) {
			$type = $this->_identifyLine($line, $last_line_type);
			$tagged[] = ['type' => $type, 'line' => $line];
			$last_line_type = $type;
		}

		return $tagged;
	}

	/**
	 * Groups adjacent lines with the same type.
	 *
	 * There is a minor exception here: If the line is a new proceeding
	 * (identified by the time field) then it forces a new group even if the
	 * previous line was a proceeding.
	 *
	 * @param  array  $lines
	 * @return array
	 */
	private function _groupifyLines(array $lines)
	{
		$grouped = [];
		$group = [];

		foreach ($lines as $line_info) {
			$type = $line_info['type'];
			$line = $line_info['line'];

			if (!$group ||
				$type != $group['type'] ||
				($type == 'proceeding' && $group['lines'] && preg_match('/^ {2,3}[0-9]/', $line))) {

				$grouped[] = $group;
				$group = ['type' => $type, 'lines' => []];
			}

			$group['lines'][] = $line;
		}

		$grouped[] = $group;

		array_shift($grouped);

		return $grouped;
	}

	/**
	 * Inspects the line and returns a "type" for it based on the content and
	 * previous line's type.
	 *
	 * @param  string $line
	 * @param  string $last_line_type
	 * @return string
	 */
	private function _identifyLine($line, $last_line_type)
	{
		if (preg_match('/^\S/i', $line)) {
			return 'division';
		}

		if (preg_match('/^\s\s\S/', $line) && preg_match('/^\s\s.*?(Justice|Registrar|Deputy)/i', $line)) {
			return 'justice';
		}

		if (preg_match('/^\s+.*?,.*?floor/i', $line) || preg_match('/^\s.*?court [0-9]/i', $line)) {
			return 'courtroom';
		}

		if (preg_match('/^\s+.*?, Melb\./i', $line)) {
			return 'address';
		}

		if (preg_match('/^\s+[0-9]+:[0-9]{2}/i', $line)) {
			return 'proceeding';
		}

		if (preg_match('/\s+v\.\s+/', $line)) {
			return 'proceeding';
		}

		if ($last_line_type == 'proceeding' && preg_match('/^\s\s\s/', $line)) {
			return 'proceeding';
		}

		return 'ignorable';
	}

	/**
	 * Processes a group of lines.
	 *
	 * @param  string $type
	 * @param  array  $lines
	 * @return string
	 */
	private function _processGroup($type, array $lines)
	{
		switch ($type) {
			case 'ignorable':  break;
			case 'division':   $this->_processDivision($lines); break;
			case 'justice':    $this->_processJustice($lines); break;
			case 'courtroom':  $this->_processCourtroom($lines); break;
			case 'address':    $this->_processAddress($lines); break;
			case 'proceeding': $this->_processProceeding($lines); break;
			default:           throw new Exception('Don\'t know what to do with line type ' . $type . '.');
		}
	}

	/**
	 * Scrapes the division from a division line.
	 *
	 * @param  array $lines
	 * @return string
	 */
	private function _processDivision(array $lines)
	{
		$this->division = trim(str_ireplace('- continued', '', $lines[0]));
	}

	/**
	 * Scrapes the justice from a justice line.
	 *
	 * @param  array $lines
	 * @return string
	 */
	private function _processJustice(array $lines)
	{
		$this->justice = trim($lines[0]);
	}

	/**
	 * Scrapes the courtroom from a courtroom line.
	 *
	 * @param  array $lines
	 * @return string
	 */
	private function _processCourtroom(array $lines)
	{
		$this->courtroom = trim($lines[0]);
	}

	/**
	 * Scrapes the address from a address line.
	 *
	 * @param  array $lines
	 * @return string
	 */
	private function _processAddress(array $lines)
	{
		$this->address = trim($lines[0]);
	}

	/**
	 * Scrapes the proceeding from a proceeding line.
	 *
	 * @param  array $lines
	 * @return string
	 */
	private function _processProceeding(array $lines)
	{
		preg_match('/[0-9]+:[0-9]{2}\s{3,}(.*?)\s{3,}/i', $lines[0], $match);
		$case_no = $match[1];

		preg_match('/^\s+([0-9]{1,2}):([0-9]{2})\s/i', $lines[0], $match);
		list($full, $hour, $minute) = $match;
		if ($hour < 8) $hour += 12;
		$session_time = clone $this->date;
		$session_time->setTime($hour, $minute, 0);

		list($plaintiff, $defendant) = $this->_scrapeNames($lines);

		$list_type = $this->_scrapeListType($lines);

		$result = new Result;
		$result->setState('VIC');
		$result->setCourtType('Supreme');
		$result->setCaseNumber($case_no);
		$result->setCaseName("$plaintiff v. $defendant");
		$result->setCaseType($this->division);
		$result->setJurisdiction(strpos($this->division, 'Criminal') !== false ? 'Criminal' : 'Civil');
		$result->setUniqueId($case_no);
		$result->setUrl('http://www.scvprobate.com.au/lists/scv_daily_list.pdf');

		$application = new Application;
		$application->setTitle($list_type);

		$hearing = new Hearing;
		$hearing->setType($list_type);
		$hearing->setCourtAddress($this->address);
		$hearing->setCourtRoom($this->courtroom);
		$hearing->setDateTime($session_time);
		$hearing->setOfficer($this->justice);

		$party = new Party;
		$party->setName($plaintiff);
		$party->setRole('Plaintiff');
		$application->addParty($party);

		$party = new Party;
		$party->setName($defendant);
		$party->setRole('Defendant');
		$application->addParty($party);

		$application->addHearing($hearing);
		$result->addApplication($application);

		$this->ret($result);
	}

	/**
	 * Scrapes the plaintiff and defendant names out of the proceeding lines.
	 *
	 * These names can wrap onto multiple lines. The column positions and widths
	 * must be determined so that the names are scraped correctly.
	 *
	 * @param  array  $lines
	 * @return array
	 */
	private function _scrapeNames(array $lines)
	{
		$lines = array_map('rtrim', $lines);

		$line_length = strlen($lines[1]);

		$left_column_position = $line_length - strlen(ltrim($lines[1]));
		$v_column_position = strpos($lines[1], ' v.   ') + 1;
		$right_column_position = $v_column_position + 5;

		$left_column_length = $v_column_position - $left_column_position;
		$right_column_length = strrpos($lines[0], '  ') + 2 - $right_column_position;

		$plaintiff = '';
		$defendant = '';

		array_shift($lines);

		foreach ($lines as $line) {
			$plaintiff .= ' ' . substr($line, $left_column_position, $left_column_length);
			$defendant .= ' ' . substr($line, $right_column_position, $right_column_length);
		}

		$plaintiff = preg_replace('/\s+/', ' ', trim($plaintiff));
		$defendant = preg_replace('/\s+/', ' ', trim($defendant));

		return [$plaintiff, $defendant];
	}

	/**
	 * Scrapes the list type out of the proceeding lines.
	 *
	 * List types can wrap onto multiple lines. The column position and width
	 * must be determined so that the list type is scraped correctly.
	 *
	 * @param  array  $lines
	 * @return array
	 */
	private function _scrapeListType(array $lines)
	{
		$column_position = strrpos(rtrim($lines[0]), '  ') + 2;

		$parts = [];

		foreach ($lines as $line) {
			$parts[] = substr($line, $column_position);
		}

		$list_type = implode(' ', $parts);
		$list_type = preg_replace('/\s+/', ' ', $list_type);

		return trim($list_type);
	}

}
