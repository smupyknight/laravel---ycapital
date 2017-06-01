<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

use Carbon\Carbon;
use App\User;
use DB;
use Mail;

class SendStatistics extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'send-statistics';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Emails watchlist statistics to watchlist owners.';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		// Do stuff which applies to all users
		$start = (new Carbon('Last month'))->startOfMonth();
		$end = $start->copy()->endOfMonth();

		$num_cases_in_month = $this->getNumCasesCreated($start, $end);

		// Send emails to each user
		$rows = DB::select("SELECT DISTINCT(created_by) FROM watchlists");

		foreach ($rows as $row) {
			$user = User::find($row->created_by);
			$this->sendStatisticsForUser($user, $start, $end, $num_cases_in_month);
		}
	}

	private function sendStatisticsForUser(User $user, Carbon $start, Carbon $end, $num_cases_in_month)
	{
		$data = [
			'user'            => $user,
			'start'           => $start,
			'end'             => $end,
			'num_cases'       => $this->getNumCasesCreated($start, $end),
			'num_comparisons' => $this->getNumComparisons($start, $end, $user, $num_cases_in_month),
			'num_contains'    => $this->getNumNotificationsByType('contains', $start, $end, $user),
			'num_exact'       => $this->getNumNotificationsByType('exact', $start, $end, $user),
		];

		Mail::send('emails.statistics', $data, function ($mail) use ($user) {
			$mail->to($user->email);
			$mail->subject('Alares watchlist statistics');
		});
	}

	/**
	 * Number of records imported in a month (excluding updates).
	 */
	private function getNumCasesCreated(Carbon $start, Carbon $end)
	{
		return DB::table('cases')
		         ->where('created_at', '>=', $start)
		         ->where('created_at', '<=', $end)
		         ->count();
	}

	/**
	 * Number of comparisons for unique cases for the given user.
	 */
	private function getNumComparisons(Carbon $start, Carbon $end, User $user, $num_cases_in_month)
	{
		$num_comparisons = 0;

		$rows = DB::table('watchlists AS w')
		          ->join('watchlist_entities AS e', 'e.watchlist_id', '=', 'w.id')
		          ->where('w.created_by', '=', $user->id)
		          ->where('e.created_at', '<=', $end)
		          ->get(['e.created_at']);

		foreach ($rows as $entity) {
			$created = new Carbon($entity->created_at);

			$num_comparisons += ($created < $start ? $num_cases_in_month : $this->getNumCasesCreated($created, $end));
		}

		return $num_comparisons;
	}

	/**
	 * Number of notifications generated of the given type in the given time
	 * period for the given user.
	 */
	private function getNumNotificationsByType($type, Carbon $start, Carbon $end, User $user)
	{
		return DB::table('watchlist_notifications AS n')
		         ->join('watchlist_entities AS e', 'n.watchlist_entity_id', '=', 'e.id')
		         ->join('watchlists AS w', 'e.watchlist_id', '=', 'w.id')
		         ->where('n.match_type', '=', $type)
		         ->where('n.created_at', '>=', $start)
		         ->where('n.created_at', '<=', $end)
		         ->where('w.created_by', '=', $user->id)
		         ->count();
	}
}
