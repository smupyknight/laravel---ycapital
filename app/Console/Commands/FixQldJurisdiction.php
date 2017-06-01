<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\CourtCase;
use DB;

class FixQldJurisdiction extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'fixqldjurisdiction';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Adds missing jurisdiction to QLD records';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$batch_start_id = 0;
		$bar = $this->output->createProgressBar($this->getCountRecords());
			$results = $this->getCasesBatch($batch_start_id);

			foreach ($results as $result) {

				$case = CourtCase::find($result->id);
				$case->timestamps = false;
				$case->jurisdiction = 'Civil';
				$case->save();
				$bar->advance();
				$batch_start_id = $result->id;
			}
		$bar->finish();
	}

	private function getCasesBatch($batch_start_id)
	{
		return CourtCase::where('state', '=', 'QLD')
							->where('court_type', '=', 'Supreme')
				            ->orWhere(function($query)
				            {
				                $query->where('state', '=', 'QLD')
								->where('court_type', '=', 'District');
				            })
		                    ->orderBy('id', 'asc')
		                    ->get();
	}

	private function getCountRecords()
	{
		return CourtCase::where('state', '=', 'QLD')
								->where('court_type', '=', 'Supreme')
				            ->orWhere(function($query)
				            {
				                $query->where('state', '=', 'QLD')
								->where('court_type', '=', 'District');
				            })
		                    ->count();
	}
}