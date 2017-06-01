<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\CourtCase;
use Carbon\Carbon;
use App\AlertEmail;
use Mail;
use App\Setting;

class NotifyAdminsIfNoCasesToday extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify-admin-if-no-cases';

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
        $enable_alerts = Setting::where('field','enable_alerts')->first();
        $emails = AlertEmail::get();
        if (!$enable_alerts || $enable_alerts->value != 1 || !$emails) {
            return false;
        }

        $scraper_names = Setting::where('field','like','last_scrape_%')
                            ->where('value','<=',Carbon::today('Australia/Brisbane')->setTimezone('UTC'))
                            ->lists('field')
                            ->toArray();
        if (!$scraper_names) {
            return false;
        }

        $names = array_map(function($name) {
            $remove_last_scrape = explode('last_scrape_',$name);
            $array = explode('-',$remove_last_scrape[1]);
            return ucwords(implode(' ', $array));;
        },$scraper_names);

        foreach ($emails as $email) {
            Mail::send('emails.email_alert_no_case',['states_no_case' => $names],function($message) use ($email) {
                $message->subject('No Cases Alert - '.date('F d, Y'));
                $message->sender('admin@' . env('MAIL_FROM_DOMAIN'), 'ADMIN');
                $message->from('admin@' . env('MAIL_FROM_DOMAIN'), 'ADMIN');
                $message->to($email->email);
            });
        }
    }
}
