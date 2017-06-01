<?php

namespace App\Console;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		\App\Console\Commands\AutoApprove::class,
		\App\Console\Commands\Scrape::class,
		\App\Console\Commands\UpdateComCourts::class,
		\App\Console\Commands\SendNotificationEmails::class,
		\App\Console\Commands\FixData::class,
		\App\Console\Commands\FixQldJurisdiction::class,
		\App\Console\Commands\ProcessWatchlistQueue::class,
		\App\Console\Commands\SendWatchlistNotifications::class,
		\App\Console\Commands\SendStatistics::class,
		\App\Console\Commands\NotifyAdminsIfNoCasesToday::class,
		\App\Console\Commands\PopulateCaseTypeList::class,
		\App\Console\Commands\UpdatePartySearchableName::class,
		\App\Console\Commands\ResetIds::class,
		\App\Console\Commands\FixAbnsAndAcns::class,
		\App\Console\Commands\ValidateAbns::class,
		\App\Console\Commands\HistoricAbrLookup::class,
		\App\Console\Commands\CalculatePartyType::class,
		\App\Console\Commands\WatchlistTest::class,
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule	$schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{
		$schedule->command('update-comcourts')->dailyAt('06:15')->withoutOverlapping(); // 4:15pm AEST
		$schedule->command('process-watchlist-queue')->everyFiveMinutes()->withoutOverlapping();
		$schedule->command('send-watchlist-notifications')->dailyAt('03:00'); // 1:00pm AEST
		$schedule->command('send-watchlist-notifications')->dailyAt('06:30'); // 4:30pm AEST
		$schedule->command('send-statistics')->monthly();
		$schedule->command('populate:case_types')->dailyAt('01:00')->withoutOverlapping(); // 11:00am AEST
		$schedule->command('notify-admin-if-no-cases')->dailyAt('10:00')->withoutOverlapping(); // 8:00pm AEST

		$schedule->command('emails:send')->dailyAt('08:30'); // 6:30pm AEST
		$schedule->command('autoapprove')->twiceDaily(12, 0);; // 10:00am and 10:00pm AEST

		// Scrapers
		$schedule->command('scrape qld-magistrates')->twiceDaily(21, 23)->withoutOverlapping(); // 7:00am and 9:00am AEST

		$schedule->command('scrape qld')->dailyAt(0)->withoutOverlapping(); // 10:00am AEST
		$schedule->command('scrape qld')->dailyAt('7:30')->withoutOverlapping(); // 5:30pm AEST

		$schedule->command('scrape nsw')->twiceDaily(15, 5)->withoutOverlapping(); // 1:00am and 3:00pm AEST
		$schedule->command('scrape vic-county')->twiceDaily(15, 5)->withoutOverlapping(); // 1:00am and 3:00pm AEST
		$schedule->command('scrape vic-magistrates')->twiceDaily(15, 5)->withoutOverlapping(); // 1:00am and 3:00pm AEST
		$schedule->command('scrape vic-supreme')->twiceDaily(15, 5)->withoutOverlapping(); // 1:00am and 3:00pm AEST
		$schedule->command('scrape wa')->twiceDaily(15, 5)->withoutOverlapping(); // 1:00am and 3:00pm AEST
		$schedule->command('scrape act-supreme')->twiceDaily(15, 5)->withoutOverlapping(); // 1:00am and 3:00pm AEST
		$schedule->command('scrape act-magistrates')->twiceDaily(15, 5)->withoutOverlapping(); // 1:00am and 3:00pm AEST
		$schedule->command('scrape act-acat')->twiceDaily(15, 5)->withoutOverlapping(); // 1:00am and 3:00pm AEST
		$schedule->command('scrape nt')->dailyAt('20:00')->withoutOverlapping(); // 6:00am AEST

		$schedule->command('scrape federal-1')->dailyAt(0)->withoutOverlapping(); // 10:00am AEST
		$schedule->command('scrape federal-1')->dailyAt('7:30')->withoutOverlapping(); // 5:30pm AEST

		$schedule->command('scrape sa-cat')->dailyAt(
			Carbon::today('Australia/Adelaide')->setTime(17, 0, 0)->setTimezone('UTC')->format('H:i')
		)->withoutOverlapping();
	}

}
