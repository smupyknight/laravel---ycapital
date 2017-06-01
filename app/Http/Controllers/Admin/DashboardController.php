<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Auth;
use App\User;
use App\ScrapeResult;
use App\CourtCase;
use App\Application;
use App\Document;
use App\Hearing;
use App\Party;
use Log;
use Mail;
use SparkPost\SparkPost;
use GuzzleHttp\Client;
use Ivory\HttpAdapter\Guzzle6HttpAdapter;
use DateTime;
use DateTimeZone;
use App\Company;
use DB;

class DashboardController extends Controller
{

	/**
	 * Shows dashboard page for admin
	 * @return view
	 */
	public function getIndex(Request $request)
	{
		return view('pages.admin.dashboard')
				->with('title', 'Dashboard')
				->with('metrics', $this->_getDatabaseMetrics())
//				->with('email_metrics', $this->_fetchEmailMetrics($request->input('graph_type')))
				->with('graph_type', $request->input('graph_type', 'today'));
	}
	/**
	 * Retrieves the metric information that comes from the database.
	 *
	 * Currently most of this is disabled due to the source tables not existing
	 * yet.
	 *
	 * @return array
	 */
	private function _getDatabaseMetrics()
	{
		$last_scrape_result = ScrapeResult::orderBy('id', 'desc')->first();
		$last_case = CourtCase::orderBy('id', 'desc')->first();

		if ($last_scrape_result && $last_case) {
			$last_scrape_time = max($last_scrape_result->created_at, $last_case->created_at);
		} else {
			$last_scrape_time = array_filter([
				$last_scrape_result->created_at,
				$last_case->created_at,
			])[0];
		}

		$date = new DateTime;
		$date->modify('-5 minutes');
		$formatted_date = $date->format('Y-m-d H:i:s');

		$num_subscribers = User::where('type','client')->where('status','active')->count();
		$num_active_subscribers = User::where('type','admin')->where('status','active')->where('last_login','>=',$formatted_date)->count();
		$num_companies = Company::active()->count();
		$num_cases_scraped = CourtCase::count() + ScrapeResult::count();
		$num_cases_scraped_today = CourtCase::where('created_at','>=',date('Y-m-d 00:00:00'))->count() + ScrapeResult::where('created_at','>=',date('Y-m-d 00:00:00'))->count();

		return [
			'num_subscribers'         => $num_subscribers,
			'num_active_subscribers'  => $num_active_subscribers,
			'num_companies'           => $num_companies,
			'num_cases_scraped'       => number_format($num_cases_scraped),
			'num_cases_scraped_today' => number_format($num_cases_scraped_today),
			'last_scrape_time'        => $last_scrape_time,
		];
	}

	/**
	 * Retrieves email metrics from SparkPost's API.
	 *
	 * @return array
	 */
	public  function getFetchEmailMetrics($graph_type)
	{
		$user_timezone = new DateTimeZone('Australia/Brisbane');
		$utc_timezone = new DateTimeZone('UTC');

		$graph_config = [
			'today'   => ['precision' => 'hour',  'start' => new DateTime('12:00am', $user_timezone)],
			'monthly' => ['precision' => 'day',   'start' => new DateTime('-30 days', $user_timezone)],
			'annual'  => ['precision' => 'month', 'start' => new DateTime('-1 year', $user_timezone)],
		];

		if (!isset($graph_config[$graph_type])) {
			$graph_type = 'today';
		}

		$adapter = new Guzzle6HttpAdapter(new Client());
		$spark = new SparkPost($adapter, ['key' => env('SPARKPOST_API_KEY')]);
		$metrics = [];

		$spark->setupUnwrapped('metrics');

		// Graph data
		$result = $spark->metrics->get('deliverability/time-series', [
			'from'      => $graph_config[$graph_type]['start']->setTimezone($utc_timezone)->format('Y-m-d\TH:i'),
			'precision' => $graph_config[$graph_type]['precision'],
			'metrics'   => 'count_sent,count_unique_confirmed_opened',
		]);
		$metrics['time_based'] = $result['results'];

		// Graph totals
		$result = $spark->metrics->get('deliverability', [
			'from'      => $graph_config[$graph_type]['start']->setTimezone($utc_timezone)->format('Y-m-d\TH:i'),
			'metrics'   => 'count_clicked,count_rendered,count_unique_confirmed_opened,count_accepted',
		]);
		$metrics['period'] = $result['results'][0];

		// All time count of sent emails
		$result = $spark->metrics->get('deliverability', [
			'from'    => (new DateTime('2016-01-01 00:00:00'))->format('Y-m-d\TH:i'),
			'metrics' => 'count_sent',
		]);
		$metrics['count_sent_alltime'] = $result['results'][0]['count_sent'];

		// Today's count of sent emails
		$result = $spark->metrics->get('deliverability', [
			'from'    => (new DateTime('7 days ago'))->format('Y-m-d\TH:i'),
			'metrics' => 'count_sent',
		]);
		$metrics['count_sent_today'] = $result['results'][0]['count_sent'];

		foreach ($metrics['time_based'] as $key => $value) {
			$metrics['time_based'][$key]['ts'] = (new DateTime($value['ts'], $utc_timezone));
		}
		$metrics['duration'] = $graph_type;

		return $metrics;
	}

}
