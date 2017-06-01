<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;
use App\ScrapeResult;
use App\CourtCase;
use Mail;

class SendNotificationEmails extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'emails:send';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description';

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
		$users = User::getUsersWithStates();
		foreach ($users as $user) {
			$data['name'] = $user->name;

			Mail::send('emails.scrape_notif',array('data' => $data),function($message) use ($user) {
				$message->subject('Scrape Results Notification - '.date('F d, Y'));
				$message->sender('admin@' . env('MAIL_FROM_DOMAIN'), 'ADMIN');
				$message->from('admin@' . env('MAIL_FROM_DOMAIN'), 'ADMIN');
				$message->to($user->email);
			});
		}
	}
}
