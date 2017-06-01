<?php
namespace App\Scrapers;

use App\Exceptions\DeadUrlException;
use Carbon\Carbon;

/**
 * Imports new cases. Fired from the artisan scrape command.
 */
class ComCourtsSearcher extends ComCourtsScraper
{

	/**
	 * Scrapes results from the Commonwealth Courts website.
	 *
	 * @return array
	 */
	protected function _scrape()
	{
		$date = Carbon::today('Australia/Sydney')->subDays(14);
		$stop = Carbon::today('Australia/Sydney');

		$basic_url = 'https://www.comcourts.gov.au/public/esearch/federal/query?';
		$basic_html = $this->transport->get($basic_url)->getBody();
		$action_types = $this->_getActionTypes($basic_html);

		while ($date->lte($stop)) {
			foreach ($action_types as $action_type) {
				$page = 1;

				do {
					$params = [
						'filed_after' => $date->format('d/m/Y'),
						'registry'    => 'any',
						'court'       => 'any',
						'file_status' => 'any',
						'registry'    => 'any',
						'action_type' => $action_type,
						'search_by'   => 'party_name',
						'page'        => $page,
					];

					$url = $basic_url . http_build_query($params);

					$this->_log('info', 'Requesting ' . $url);
					$html = $this->transport->get($url)->getBody();

					$this->_scrapePage($html, $action_type);

					$page++;
				} while ($this->_hasNextPage($html));
			}

			$date->addDay();
		}
	}

	/**
	 * Determines if the HTML contains a "next page" link.
	 *
	 * @param  string $html
	 * @return bool
	 */
	private function _hasNextPage($html)
	{
		return (strpos($html, 'class="next_page"') !== false);
	}

	private function _getActionTypes($html)
	{
		if (!preg_match('%<select[^>]*name="action_type".*?>(.*?)</select>%is', $html, $match)) {
			return;
		}

		$options = $this->_extractChildren($match[1], 'option');

		$action_types = [];

		foreach ($options as $option) {
			preg_match('%<option[^>]*value="(.*)".*>.*?</option>%is', $option, $result);
			$action_types[] = $result[1];
		}

		return $action_types;
	}

	/**
	 * Scrapes the results on a single page.
	 *
	 * Finds the table in the HTML, then iterates through its rows and scrapes
	 * individual rows. There is only one table in the markup.
	 *
	 * @param  string $html
	 * @return array
	 */
	private function _scrapePage($html, $action_type)
	{
		if (!preg_match('%<table[^>]*class="party-files-table".*?>(.*?)</table>%is', $html, $match)) {
			return;
		}

		$rows = $this->_extractChildren($match[1], 'tr');

		array_shift($rows);

		foreach ($rows as $tr) {
			preg_match('/href="(.*?)"/', $tr, $match);
			$url = 'https://www.comcourts.gov.au' . $match[1];

			try {
				$this->_scrapeUrl($url, $action_type);
			} catch (DeadUrlException $e) {
				// ignore
			} catch (ErrorException $e) {
				$this->_logException($e);
			}
		}
	}

}
