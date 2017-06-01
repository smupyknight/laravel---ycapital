<?php

namespace App\Console\Commands;

use App\ScrapeResults\Result;
use App\ScrapeResult;
use App\Scrapers\ComCourtsUpdater;
use DateTime;
use DateTimeZone;
use DB;
use GuzzleHttp;
use Illuminate\Console\Command;

class UpdateComCourts extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'update-comcourts';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Rescrapes federal cases which have a null date_finalised field.';

	protected $scraper_name = 'federal-1';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$guzzle = new GuzzleHttp\Client([
			'cookies' => true,
			'timeout' => 300,
		]);

		$scraper = new ComCourtsUpdater($guzzle);
		$scraper->setCallback([$this, 'handleResult']);
		$scraper->run();
	}

	public function handleResult(Result $result)
	{
		$result_id = ScrapeResult::where('scraper', $this->scraper_name)->where('unique_id', $result->getUniqueId())->value('id');

		$result_array = $result->asArray();

		$data = [
			'applications'  => $result_array['applications'],
			'related_cases' => $result_array['related_cases'],
		];

		$fields = [
			'scraper'      => $this->scraper_name,
			'unique_id'    => $result->getUniqueId(),
			'state'        => $result->getState(),
			'court_type'   => $result->getCourtType(),
			'case_no'      => $result->getCaseNumber(),
			'case_name'    => $result->getCaseName(),
			'case_type'    => $result->getCaseType(),
			'suburb'       => $result->getSuburb(),
			'jurisdiction' => $result->getJurisdiction(),
			'url'          => $result->getUrl(),
			'data'         => json_encode($data),
			'created_at'   => (new DateTime)->setTimezone(new DateTimeZone('UTC')),
			'updated_at'   => (new DateTime)->setTimezone(new DateTimeZone('UTC')),
		];

		if ($result_id) {
			// Updating
			$sets = array_map(function($fieldname) {
				return "`$fieldname` = ?";
			}, array_keys($fields));

			DB::update("UPDATE scrape_results SET " . implode(', ', $sets) . " WHERE id = ?",
				array_merge(array_values($fields), [$result_id])
			);
		} else {
			// Inserting
			$updateable = array_diff(array_keys($fields), ['scraper','unique_id','created_at','updated_at']);
			$updateable = array_map(function($fieldname) {
				return "`$fieldname` = VALUES(`$fieldname`)";
			}, $updateable);

			DB::insert(
				"INSERT INTO scrape_results (`" . implode("`,`", array_keys($fields)) . "`)
				VALUES (" . implode(', ', array_fill(0, count($fields), '?')) . ")
				ON DUPLICATE KEY UPDATE " . implode(', ', $updateable),
				array_values($fields)
			);

			$result_id = DB::select("SELECT LAST_INSERT_ID()")[0]->{'LAST_INSERT_ID()'};
		}

		$result = ScrapeResult::find($result_id);

		if ($result && $result->validate()) {
			$result->approve();
		}
	}

}
