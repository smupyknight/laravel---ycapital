<?php

namespace App\Jobs;

use App\CourtCase;
use App\Party;
use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

class AbrLookup extends Job implements SelfHandling, ShouldQueue
{
	use InteractsWithQueue, SerializesModels;

	private $courtCase;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct(CourtCase $courtCase)
	{
		$this->courtCase = $courtCase;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{
		$courtCase = $this->courtCase;

		foreach ($courtCase->parties as $party) {
			// If both ABN and ACN are set, continue.
			if ($party->abn != '' && $party->acn != '') {
				continue;
			}

			// If both ABN and ACN are blank, continue.
			if ($party->abn == '' && $party->acn == '') {
				continue;
			}

			// Process Lookup
			$this->lookup($party);
		}

		$courtCase->addToWatchlistQueue();
		dispatch(new \App\Jobs\ProcessIndividualWatchlist($courtCase));

	}

	private function lookup(Party $party)
	{
		$abr_lookup = new \App\AbrLookup;

		if ($party->acn) {
			$result = $abr_lookup->getRecordFromAbrNumber($party->acn);
			$party->abn = $result['abn'];
		}

		if ($party->abn) {
			$result = $abr_lookup->getRecordFromAbrNumber($party->abn);
			$party->acn = $result['acn'];
		}

		if ($party->type == 'Other') {
			$party->type = $party->determineType();
		}

		$party->save();
	}

}
