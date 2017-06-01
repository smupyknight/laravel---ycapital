<?php
namespace App\Scrapers;

use DateTime;
use DateTimeZone;
use Exception;
use Log;
use App\ScrapeResults\Result;

abstract class Scraper
{

	protected $transport = null;

	protected $start_date = null;

	protected $timezone = null;

	public function __construct($transport)
	{
		$this->transport = $transport;
	}

	/**
	 * Sets the starting date that should be scraped.
	 *
	 * @param DateTime $start_date
	 */
	public function setStartDate(DateTime $start_date)
	{
		$this->start_date = $start_date;
	}

	public function setCallback(callable $callback)
	{
		$this->callback = $callback;
	}

	/**
	 * Performs the scraping process.
	 *
	 * @return array
	 */
	public function run()
	{
		try {
			$this->_scrape();
		} catch (Exception $e) {
			$this->_logException($e, 'alert');
		}
	}

	protected function ret(Result $result)
	{
		call_user_func($this->callback, $result);
	}

	abstract protected function _scrape();

	/**
	 * Extracts the text from the PDF file.
	 *
	 * @param  string $pdf
	 * @return string
	 */
	protected function _getTextFromPdf($pdf)
	{
		$check_command = (PHP_OS == 'WINNT' ? 'where pdftotext' : 'which pdftotext');

		if (!`$check_command`) {
			throw new Exception('The pdftotext binary is not installed.');
		}

		$filename = tempnam(sys_get_temp_dir(), 'pdf');

		file_put_contents($filename, $pdf);
		exec('pdftotext -layout "' . $filename . '" -', $output, $return_code);
		unlink($filename);

		if ($return_code != 0) {
			throw new Exception('pdftotext failed with return code ' . $return_code . '.');
		}

		return implode("\n", $output);
	}

	/**
	 * Extracts elements from HTML without tripping up on nested elements of the
	 * same tag name.
	 *
	 * For example, you can pass markup containing nested tables with
	 * $tag = 'tr', and it will return an array of TR markup for the outer table
	 * only.
	 *
	 * @param  string $html
	 * @param  string $tag
	 * @return array
	 */
	protected function _extractChildren($html, $tag)
	{
		$children = [];
		$scan_position = 0;

		while (($current_child_start = stripos($html, '<' . $tag, $scan_position)) !== false) {
			$scan_position = $current_child_start + 1;

			$depth = 1;

			while ($depth > 0) {
				$next_open = stripos($html, '<' . $tag, $scan_position);
				$next_end = stripos($html, '</' . $tag, $scan_position);

				if ($next_open !== false && $next_open < $next_end) {
					$scan_position = $next_open + 1;
					$depth++;
				} else {
					$scan_position = $next_end + 1;
					$depth--;
				}
			}

			$children[] = substr($html, $current_child_start, $scan_position - $current_child_start + strlen($tag) + 2);
		}

		return $children;
	}

	/**
	 * Logs a message to both the Laravel log and the screen.
	 *
	 * @param  string $type    info, warning, etc
	 * @param  string $message
	 */
	protected function _log($type, $message)
	{
		$class = explode('\\', get_class($this));
		$class = array_pop($class);
		$class = str_replace('Scraper', '', $class);

		$full_message = '[' . $class . '] ' . $message;

		Log::$type($full_message);

		$date = new DateTime;
		$date->setTimezone(new DateTimeZone('Australia/Brisbane'));
		echo '[' . $date->format('Y-m-d H:i:s') . '] local.' . strtoupper($type) . ': ' . $full_message . "\n";
	}

	/**
	 * Logs an exception.
	 *
	 * @param  Exception $e
	 * @param  string    $type
	 */
	protected function _logException(Exception $e, $type = 'warning')
	{
		$this->_log($type, $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
	}

}
