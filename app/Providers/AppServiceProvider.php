<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

use App\WatchlistSubscriber;

class AppServiceProvider extends ServiceProvider
{

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		Validator::extend('unique_subscriber_name_email', function($attribute, $value, $parameters, $validator) {
			$watchlist_id = isset($parameters[0]) ? $parameters[0] : null;
			$name = isset($parameters[1]) ? $parameters[1] : null;
			$email = isset($parameters[2]) ? $parameters[2] : null;

			$subscriber = WatchlistSubscriber::where('watchlist_id', $watchlist_id)
							->where('name', $name)
							->where('email', $email)
							->first();

			return $subscriber ? false : true;
		});
		Validator::extend('valid_abn', function($attribute, $value, $parameters, $validator) {
			// strip anything other than digits
			$value = preg_replace("/[^\d]/","", $value);

			if (strlen($value) == 9) {
				return true;
			}

			if (strlen($value) != 11) {
				return false;
			}

			$weights = array(10, 1, 3, 5, 7, 9, 11, 13, 15, 17, 19);

			// apply ato check method
			$sum = 0;
			foreach ($weights as $position=>$weight) {
				$digit = $value[$position] - ($position ? 0 : 1);
				$sum += $weight * $digit;
			}
			return ($sum % 89)==0;
		});
	}

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->alias('bugsnag.multi', \Illuminate\Contracts\Logging\Log::class);
		$this->app->alias('bugsnag.multi', \Psr\Log\LoggerInterface::class);

		$this->app->bind('\App\Console\Commands\Scrape', function($app) {
			$transport = new \GuzzleHttp\Client([
				'cookies' => true,
				'timeout' => 300,
			]);

			return new \App\Console\Commands\Scrape($transport);
		});
	}

}
