<?php
namespace App\Scrapers;

use App\Exceptions\DeadUrlException;
use DB;
use Exception;

/**
 * Selects previously scraped cases which have no date_finalised date, then
 * rescrapes them to get updates.
 */
class ComCourtsUpdater extends ComCourtsScraper
{

	public function run()
	{
		$cases = DB::table('applications AS a')
			->join('cases AS c', 'a.case_id', '=', 'c.id')
			->whereNull('a.date_finalised')
			->where('c.url_dead', 0)
			->where('c.unique_id', 'LIKE', 'federal-1-%')
			->groupBy('c.id')
			->select('c.id', 'c.url')
			->get();

		$count = count($cases);

		foreach ($cases as $index => $case) {
			$this->_log('info', 'Rescraping URL ' . ($index + 1) . ' of ' . $count);

			try {
				$this->_scrapeUrl($case->url);
			} catch (DeadUrlException $e) {
				DB::table('cases')
					->where('id', $case->id)
					->update(['url_dead' => 1]);
			} catch (Exception $e) {
				$this->_log('error', $e->getMessage());
			}
		}
	}

	/**
	 * Required because ComCourtsScraper::_scrape() is abstract.
	 */
	protected function _scrape()
	{
		// never used
	}

}
