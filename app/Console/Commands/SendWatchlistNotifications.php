<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use App\Watchlist;
use App\WatchlistNotification;
use App\WatchlistSubscriber;
use Mail;

class SendWatchlistNotifications extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'send-watchlist-notifications';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Sends pending notifications to subscribers.';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		foreach (Watchlist::all() as $watchlist) {
			$this->sendNotificationsForWatchlist($watchlist);
		}
	}

	private function sendNotificationsForWatchlist(Watchlist $watchlist)
	{
		$entity_ids = $watchlist->entities()->lists('id');

		if (!count($entity_ids)) {
			return;
		}

		$notifications = WatchlistNotification::whereIn('watchlist_entity_id', $entity_ids)
		                                      ->whereIsSent(0)
		                                      ->get();

		if (!count($notifications)) {
			return;
		}

		WatchlistNotification::whereIn('watchlist_entity_id', $entity_ids)
		                     ->whereIsSent(0)
		                     ->update(['is_sent' => 1]);

		foreach ($watchlist->subscribers as $subscriber) {
			$this->sendEmail($subscriber, $notifications);
		}
	}

	/**
	 * Sends the notifications to a single subscriber.
	 */
	private function sendEmail(WatchlistSubscriber $subscriber, Collection $notifications)
	{
		$data = [
			'notifications' => $notifications,
			'subscriber'    => $subscriber,
			'timezone'      => $subscriber->creator->timezone,
		];

		Mail::send('emails.watchlist-notifications', $data, function($mail) use ($subscriber) {
			$mail->to($subscriber->email);
			$mail->subject('Alares watchlist notifications');
		});
	}

}
