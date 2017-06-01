<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Party;

class UpdatePartySearchableName extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'update-party-searchable-name';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Recomputes the parties.searchable_name field.';

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
				$party->searchable_name = $party->getCalculatedSearchableName($party);
				$party->save();
				$bar->advance();
			}
		});

		$bar->finish();
		echo PHP_EOL;
	}

}
