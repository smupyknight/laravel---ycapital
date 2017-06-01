<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\WatchlistEntity;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\AbrLookupWatchlistEntity;

class HistoricAbrLookup extends Command
{

	use DispatchesJobs;

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'historic-abr-lookup';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Add all Watchlist Entities with missing abn/acn to queue for processing';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$query = WatchlistEntity::query();

		$query->where(function ($query) {
			$query->where('abn', null)
				->whereNotNull('acn');
		});

		$query->orWhere(function ($query) {
			$query->where('acn', null)
				->whereNotNull('abn');
		});

		$count = clone $query;

		$bar = $this->output->createProgressBar($count->count());

		$bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');

		$entities = $query->get();

		foreach ($entities as $entity) {
			$this->dispatch(new AbrLookupWatchlistEntity($entity));
			$bar->advance();
		}

		$bar->finish();

		echo PHP_EOL;
	}

}
