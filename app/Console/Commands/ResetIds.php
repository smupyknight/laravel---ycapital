<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;

class ResetIds extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'reset-ids';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Change application, hearing, party and document IDs to be consecutive.';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$this->doTable('applications', ['hearings','parties','documents']);
		$this->doTable('hearings');
		$this->doTable('parties');
		$this->doTable('documents');
	}

	private function doTable($table, array $child_tables = [])
	{
		$this->line("Resetting IDs in $table...");

		if ($child_tables) {
			$old_ids = DB::table($table)->orderBy('id', 'asc')->lists('id');
		}

		$num_records = DB::table($table)->count();

		DB::statement('SET @last_id := 0');
		DB::statement("UPDATE `$table` SET id = (@last_id := @last_id + 1) ORDER BY id ASC");
		DB::statement("ALTER TABLE $table AUTO_INCREMENT = " . ($num_records + 1));

		foreach ($child_tables as $child_table) {
			$this->line("Updating relations in $child_table...");

			$bar = $this->output->createProgressBar(count($old_ids));

			foreach ($old_ids as $index => $old_id) {
				DB::table($child_table)->where('application_id', $old_id)->update([
					'application_id' => $index + 1,
				]);

				$bar->advance();
			}

			$bar->finish();

			echo PHP_EOL;
		}
	}

}
