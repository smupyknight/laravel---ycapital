<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\ScrapeResult;
use DB;

class FixData extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'fixdata';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Fixes missing data in scrape results.';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$batch_start_id = 0;
		$bar = $this->output->createProgressBar(DB::table('scrape_results')->count());

		do {
			$results = $this->getBatch($batch_start_id);

			foreach ($results as $result) {
				$data = json_decode($result->data);

				foreach ($data->applications as $application) {
					foreach ($application->hearings as $hearing) {
						if (!isset($hearing->orders_filename)) {
							$hearing->orders_filename = '';
						}
					}
				}

				$data = json_encode($data);
				DB::update("UPDATE scrape_results SET data = ? WHERE id = ?", [$data, $result->id]);
				$batch_start_id = $result->id;
				$bar->advance();
			}

		} while (count($results) == 100);

		$bar->finish();

		echo PHP_EOL;
	}

	private function getBatch($batch_start_id)
	{
		return ScrapeResult::where('id', '>', $batch_start_id)
		                    ->orderBy('id', 'asc')
		                    ->take(100)
		                    ->get();
	}
}