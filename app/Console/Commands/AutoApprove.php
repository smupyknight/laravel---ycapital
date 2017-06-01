<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\ScrapeResult;

class AutoApprove extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'autoapprove';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Automatically approves scrape results if they look OK.';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$batch_start_id = 0;
		$bar = $this->output->createProgressBar(ScrapeResult::count());

		do {
			$results = $this->getBatch($batch_start_id);

			foreach ($results as $result) {
				if ($result->validate()) {
					$result->approve();
				}
				$bar->advance();
				$batch_start_id = $result->id;
			}

		} while (count($results) == 100);
		$bar->finish();
	}

	private function getBatch($batch_start_id)
	{
		return ScrapeResult::where('id', '>', $batch_start_id)
		                    ->orderBy('id', 'asc')
		                    ->take(100)
		                    ->get();
	}
}
