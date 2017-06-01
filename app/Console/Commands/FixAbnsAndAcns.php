<?php
namespace App\Console\Commands;

use App\Party;
use DB;
use Illuminate\Console\Command;

class FixAbnsAndAcns extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'fix-abns-and-acns';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Fixes ABNs and ACNs.';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$num_parties = DB::table('parties')->count();
		$bar = $this->output->createProgressBar($num_parties);
		$batch_start_id = 0;

		do {
			$parties = $this->getBatch($batch_start_id);

			foreach ($parties as $party) {
				$this->fixParty($party);

				$bar->advance();

				$batch_start_id = $party->id;
			}
		} while (count($parties) == 100);

		$bar->finish();

		echo PHP_EOL;
	}

	private function getBatch($batch_start_id)
	{
		return Party::where('id', '>', $batch_start_id)
			->orderBy('id', 'asc')
			->take(100)
			->get();
	}

	private function fixParty(Party $party)
	{
		$orig_abn = $party->abn;
		$orig_acn = $party->acn;

		if (preg_match('/a(b|c)n([\d\s]+)/i', $party->name, $match)) {
			$name_number = preg_replace('/[^\d]+/', '', $match[2]);
		} else {
			$name_number = '';
		}

		$party->abn = preg_replace('/[^\d]+/', '', $party->abn);
		$party->acn = preg_replace('/[^\d]+/', '', $party->acn);

		if (strlen($party->abn) == 11) {
			// Do nothing
		} elseif (strlen($name_number) == 11) {
			$party->abn = $name_number;
		} elseif (strlen($party->abn) == 9 && strlen($party->acn) != 9) {
			$party->acn = $party->abn;
			$party->abn = '';
		} else {
			$party->abn = '';
		}

		if (strlen($party->acn) == 9) {
			// Do nothing
		} elseif (strlen($name_number) == 9) {
			$party->acn = $name_number;
		} elseif (strlen($party->abn) == 11) {
			$party->acn = substr($party->abn, 2);
		} else {
			$party->acn = '';
		}

		if ($party->abn != $orig_abn || $party->acn != $orig_acn) {
			$party->save();
			//$this->line("$party->name | $orig_abn -> $party->abn | $orig_acn -> $party->acn");
		}
	}

}
