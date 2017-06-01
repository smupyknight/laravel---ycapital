<?php

namespace App\Jobs;

use App\WatchlistEntity;
use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;


class AbrLookupWatchlistEntity extends Job implements SelfHandling, ShouldQueue
{
	use InteractsWithQueue, SerializesModels;

	private $entity;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct(WatchlistEntity $entity)
	{
		$this->entity = $entity;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{
		$entity = $this->entity;

		$abr_lookup = new \App\AbrLookup;

		// If both ABN and ACN are set, continue.
		if ($entity->abn != '' && $entity->acn != '') {
			return;
		}
		// If both ABN and ACN are blank, continue.
		if ($entity->abn == '' && $entity->acn == '') {
			return;
		}

		if ($entity->acn) {
			$result = $abr_lookup->getRecordFromAbrNumber($entity->acn);
			$entity->abn = $result['abn'] != '' ? $result['abn'] : null;
		}

		if ($entity->abn) {
			$result = $abr_lookup->getRecordFromAbrNumber($entity->abn);
			$entity->acn = $result['acn'] != '' ? $result['acn'] : null;
		}

		$entity->save();
	}
}
