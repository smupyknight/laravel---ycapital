<?php
namespace App\Console\Commands;

use App\Party;
use DB;
use Illuminate\Console\Command;

class ValidateAbns extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'validate-abns';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Validate all ABNS.';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$file = fopen("storage/exportcsv/invalid-abns.csv", "w");

		$headers = ['Party ID', 'Party Name', 'Invalid ABN', 'Valid ABN'];
		fputcsv($file, $headers);

		$this->info('Validating ABNS...');

		$bar = $this->output->createProgressBar(Party::where('abn', '!=', '')->count());

		Party::where('abn', '!=', '')->chunk(100, function($parties) use ($file, $bar) {
			foreach ($parties as $party) {
				$result = $this->validateABN($party->abn);

				if (!$result) {
					$data = [
						'party_id'   => $party->id,
						'party_name' => $party->name,
						'party_abn'  => $party->abn,
					];
					fputcsv($file, $data);
				}

				$bar->advance();
			}
		});

		fclose($file);

		$bar->finish();

		echo PHP_EOL;
	}

	private function validateABN($abn)
	{
	    $weights = array(10, 1, 3, 5, 7, 9, 11, 13, 15, 17, 19);

	    // strip anything other than digits
	    $abn = preg_replace("/[^\d]/","",$abn);

	    // check length is 11 digits
	    if (strlen($abn)==11) {
	        // apply ato check method
	        $sum = 0;
	        foreach ($weights as $position=>$weight) {
	            $digit = $abn[$position] - ($position ? 0 : 1);
	            $sum += $weight * $digit;
	        }
	        return ($sum % 89)==0;
	    }
	    return false;
	}

}
