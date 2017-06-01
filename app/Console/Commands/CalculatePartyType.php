<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Party;

class CalculatePartyType extends Command
{

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'calculate-party-type';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Calculates the party type for all existing parties';

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
		$bar = $this->output->createProgressBar(Party::count());

		$bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');

		Party::orderBy('id', 'DESC')->chunk(1000, function($parties) use ($bar) {
			foreach($parties as $party) {
				$type = $party->determineType();
				$party->type = $type;
				$party->save();
				$bar->advance();
			}
		});

		$bar->finish();
	}

}
