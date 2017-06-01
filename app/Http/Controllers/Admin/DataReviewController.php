<?php
namespace App\Http\Controllers\Admin;

use App\Setting;
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

class DataReviewController extends Controller
{

	/**
	 * Show data review page for admin to approve or decline scrape results
	 * @return view
	 */
	public function getIndex()
	{
		$court_types = $this->_getCourtTypes();
		$notes = $this->_getNotes();
		$states = Auth::user()->getStates();
		$case_types = ScrapeResult::groupBy('jurisdiction')->orWhere('jurisdiction','!=','')->lists('jurisdiction');
		$setting_get = Setting::where('field','cases_type')->first();
		if(count($setting_get)>0){
			$cases_data = explode(',', $setting_get->value);
		}
		else{
			$cases_data = '';
		}

		return view('pages.admin.data-review')
			->with('states',$states)
			->with('court_types',$court_types)
			->with('case_types',$case_types)
			->with('notes',$notes)
			->with('cases_data',$cases_data)
			->with('title','Admin Data Review');
	}

	public function _getCourtTypes()
	{
		return ScrapeResult::select('court_type')->groupBy('court_type')->orderBy('court_type','asc')->get();
	}

	public function _getNotes()
	{
		$results = ScrapeResult::select('notes')->groupBy('notes')->get();
		$return_array = [];
		foreach ($results as $result) {
			$notes_array = json_decode($result->notes);
			if ($notes_array) {
				foreach ($notes_array as $note) {
					if (!in_array($note,$return_array)) {
						array_push($return_array,$note);
					}
				}
			}
		}
		return $return_array;
	}

	/**
	 * Handles submission of data to approve or reject
	 * @param  Request $request
	 * @return redirect
	 */
	public function postIndex(Request $request)
	{
		if ($request->approve_or_reject == 'reject') {
			foreach ($request->input('checkbox', []) as $result_id) {
				$result = ScrapeResult::find($result_id);
				$result->delete();
			}

			return redirect('/admin/data-review');
		}

		foreach ($request->input('checkbox', []) as $result_id) {
			$fields = [];
			foreach ($request->all() as $key => $value) {
				if (strpos($key, $result_id . '_') === 0) {
					$fields[$key] = $value;
				}
			}

			$rekeyed_fields = [];
			foreach ($fields as $key => $value) {
				$new_key = str_replace($result_id . '_', '', $key);
				$rekeyed_fields[$new_key] = $value;
			}

			$result = ScrapeResult::find($result_id);

			$data = json_decode($result->data, true);

			$data = array_replace_recursive($data, $rekeyed_fields);

			$result->data = json_encode($data);
			$result->approve();
		}

		return redirect('/admin/data-review');
	}

	/**
	 * AJAX call to show scrape results for admin
	 * @param  Request $request
	 * @return json
	 */
	public function getStateScrapeResults(Request $request)
	{
		$state = $request->get('state');
		$results = $this->_getScrapeResults(explode(',', $state), $request);

		return view('partials.data-review')
			 ->with('results', $results)
			 ->with('state', $state);
	}

	/**
	 * Get scrape results for Admin Data View
	 * @param  array $states
	 * @param  Request $request
	 * @return array
	 */
	private function _getScrapeResults(array $states, Request $request)
	{
		$query = ScrapeResult::query();

		if ($states && $states[0] != 'null') {

			$query->whereIn('state',$states);

			if (in_array('federal',$states)) {
				$query->orWhere('court_type', 'federal');
			}
		}

		if ($request->case_id) {
			$query->where('case_no', $request->case_id);
		}

		if ($request->case_name) {
			$query->where('case_name','like','%'.$request->case_name.'%');
		}

		if ($request->date_added != '') {
			$query->where('created_at','>',date('Y-m-d 00:00:00',strtotime($request->date_added)));
			$query->where('created_at','<',date('Y-m-d 23:59:59',strtotime($request->date_added)));
		}

		if ($request->date_modified != '') {
			$query->where('updated_at','>',date('Y-m-d 00:00:00',strtotime($request->date_modified)));
			$query->where('updated_at','<',date('Y-m-d 23:59:59',strtotime($request->date_modified)));
		}

		if ($request->reason != '') {
			$query->where('notes','like','%'.$request->reason.'%');
		}

		if ($request->jurisdiction != '') {
			$query->where('court_type','like','%'.$request->jurisdiction.'%');
		}

		if ($request->case_type != '') {
			$query->whereIn('jurisdiction',$request->case_type);
		}

		return $query->orderBy('id','desc')->paginate(20);
	}
}
