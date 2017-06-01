<?php
namespace App\Http\Controllers\Client;

use App\Application;
use App\CaseType;
use App\CourtCase;
use App\Filter;
use App\Hearing;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Party;
use App\ScrapeResult;
use App\Setting;
use App\StatesSubscribed;
use App\User;
use Auth;
use Carbon\Carbon;
use DB;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Response;
use Validator;

class CasesController extends Controller
{

	/**
	 * Shows dashboard page for clients.
	 *
	 * @return view
	 */
	public function getIndex(Request $request)
	{
		$states = Auth::user()->getStates();

		if (!$request->state || $states->search($request->state) === false) {
			return $this->determineRedirect($request);
		}

		$court_types = ['Supreme','District/County','Magistrates/Local','CAT'];

		$cases = $this->_getCases($request);
		$cases = $this->_populateRelations($cases);

		$hearing_types = $this->getHearingTypes($request);
		$case_types = $this->getCaseTypes($request);
		$court_suburbs = $this->getCourtSuburbs($request);
		$party_representatives = $this->getPartyRepresentatives($request);

		foreach ($cases as $case) {
			$case->timezone = $case->getTimezone($case);
		}

		$filters = Filter::where('user_id', Auth::user()->id)->get();

		return view('pages.client.cases-list')
			->with('request', $request)
			->with('filters', $filters)
			->with('states', $states)
			->with('court_types', $court_types)
			->with('hearing_types', $hearing_types)
			->with('case_types', $case_types)
			->with('court_suburbs', $court_suburbs)
			->with('party_representatives', $party_representatives)
			->with('cases', $cases)
			->with('title', 'Client Portal Home');
	}

	public function getByParty(Request $request)
	{
		$states = Auth::user()->getStates();

		if (!$request->state || $states->search($request->state) === false) {
			if ($states->count()) {
				return redirect('/client/cases/by-party?state=' . $states[0]);
			}

			return redirect('/not-subscribed');
		}

		// First, get the total count
		$sub = $this->buildByPartyQuery($request)
			->selectRaw('1')
			->groupBy('cases.id');

		$total = DB::table(DB::raw("({$sub->toSql()}) AS sub"))
			->mergeBindings($sub->getQuery())
			->count();

		// Get the ones we want
		$cases = $this->buildByPartyQuery($request)
			->select('cases.*')
			->groupBy('cases.id')
			->orderBy('cases.notification_time', 'desc')
			->skip(($request->get('page', 1) - 1) * 20)
			->take(20)
			->get();

		$cases = new LengthAwarePaginator($cases, $total, 20, null, [
			'path' => '/' . $request->path(),
		]);

		foreach ($cases as $case) {
			$case->timezone = $case->getTimezone($case);
		}

		return view('pages.client.cases-byparty')
			->with('request', $request)
			->with('states', $states)
			->with('cases', $cases)
			->with('court_types', ['Supreme','District/County','Magistrates/Local'])
			->with('title', 'Client Portal Home');
	}

	private function buildByPartyQuery(Request $request)
	{
		$query = CourtCase::query();

		// State
		if ($request->state == 'federal') {
			$query->where('court_type', 'federal');
		} elseif ($request->state) {
			$query->where('state', $request->state);
			$query->where('court_type', '!=', 'Federal');
		}

		// Court type
		if ($request->court_type) {
			$query->where(function($query) use($request) {
				if ($request->court_type == 'Magistrates/Local') {
					$query->orWhere('cases.court_type', 'Magistrates');
					$query->orWhere('cases.court_type', 'Local');
				} elseif ($request->court_type == 'District/County') {
					$query->orWhere('cases.court_type', 'District');
					$query->orWhere('cases.court_type', 'County');
				} else {
					$query->orWhere('cases.court_type', $request->court_type);
				}
			});
		}

		// Search
		if ($request->search) {
			$query->join('applications AS a', 'a.case_id', '=', 'cases.id');
			$query->join('parties AS p', 'p.application_id', '=', 'a.id');
			$query->where(function($query) use($request) {
				$query->where('name', 'LIKE', '%' . $request->search . '%');
				$query->orWhere('rep_name', 'LIKE', '%' . $request->search . '%');
			});
		}

		return $query;
	}

	private function determineRedirect(Request $request)
	{
		// If user has a filter
		if ($filter = Auth::user()->filters()->first()) {
			return redirect('/client/cases?' . $filter->getQueryString());
		}

		// If user is subscribed to any state
		if ($states = Auth::user()->getStates()->toArray()) {
			return redirect('/client/cases?state=' . $states[0]);
		}

		return redirect('/not-subscribed');
	}

	public function getView($case_id)
	{
		$case = CourtCase::findOrFail($case_id);

		return view('pages.client.cases-view')
		     ->with('title','Case View')
		     ->with('case', $case);
	}

	public function getPartyRepresentatives(Request $request)
	{
		return CourtCase::getCases($request, 'filter', 'party_representative')
		                ->where('p.rep_name', '!=', '')
		                ->groupBy('p.rep_name')
		                ->lists('p.rep_name');
	}

	public function getCourtSuburbs(Request $request)
	{
		return CourtCase::getCases($request, 'filter', 'court_suburb')
		                ->where('suburb', '!=', '')
		                ->groupBy('suburb')
		                ->lists('suburb');
	}

	public function getHearingTypes(Request $request)
	{
		return CourtCase::getCases($request, 'filter', 'hearing_type')
		                ->where('h.type', '!=', '')
		                ->groupBy('h.type')
		                ->lists('h.type');
	}

	public function getCaseTypes(Request $request)
	{
		return CourtCase::getCases($request, 'filter', 'case_type')
						  ->where('cases.case_type', '!=', '')
						  ->groupBy('cases.case_type')
						  ->lists('cases.case_type')
						  ->unique();
	}

	/**
	 * Builds and runs the query which selects the case records that'll be shown
	 * on this page.
	 *
	 * The return value is an array of stdClasses representing case records.
	 *
	 * @param  Request $request
	 * @return array
	 */
	private function _getCases(Request $request)
	{
		// First, get the total count
		$total = CourtCase::query()
		                  ->selectRaw('DISTINCT(cases.id)')
		                  ->getCases($request)
		                  ->groupBy('cases.id')
		                  ->get()
		                  ->count();

		// Get the ones we want
		$per_page = $request->get('per_page', 20);

		$cases = CourtCase::query()
		                  ->select('cases.*')
		                  ->getCases($request)
		                  ->groupBy('cases.id')
		                  ->orderBy('cases.notification_time', 'desc')
		                  ->skip(($request->get('page', 1) - 1) * $per_page)
		                  ->take($per_page)
		                  ->get();

		return new LengthAwarePaginator($cases, $total, $per_page, null, [
			'path' => '/' . $request->path(),
		]);
	}

	/**
	 * Queries and populates the applications property of each case.
	 *
	 * Each applications property is also populated with hearings and so on.
	 *
	 * @param  \Illuminate\Pagination\LengthAwarePaginator $cases [description]
	 * @return [type]                                             [description]
	 */
	private function _populateRelations($cases)
	{
		$case_ids = [];

		foreach ($cases as $case) {
			$case_ids[] = $case->id;
		}

		$applications = DB::table('applications')
						  ->whereIn('case_id', $case_ids)
						  ->orderBy('id', 'asc')
						  ->get();

		$application_ids = [];
		foreach ($applications as $application) {
			$application_ids[] = $application->id;
		}

		$hearings = DB::table('hearings')
					  ->whereIn('application_id', $application_ids)
					  ->orderBy('id', 'asc')
					  ->get();

		$parties = DB::table('parties')
					 ->whereIn('application_id', $application_ids)
					 ->orderBy('id', 'asc')
					 ->get();

		$documents = DB::table('documents')
					   ->whereIn('application_id', $application_ids)
					   ->orderBy('id', 'asc')
					   ->get();

		foreach ($applications as $application) {
			$application->hearings = array_filter($hearings, function ($hearing) use ($application) {
				return $application->id == $hearing->application_id;
			});

			$application->parties = array_filter($parties, function ($party) use ($application) {
				return $application->id == $party->application_id;
			});

			$application->documents = array_filter($documents, function ($document) use ($application) {
				return $application->id == $document->application_id;
			});
		}

		foreach ($cases as $case) {
			$case->applications = array_filter($applications, function ($application) use ($case) {
				return $case->id == $application->case_id;
			});
		}

		return $cases;
	}

	public function getTypes()
	{
		$case_type_array = CaseType::get();
		$array = [];
		foreach ($case_type_array as $case_type) {
			$array[$case_type->scraper][] = $case_type->case_type;
		}

		return view('pages.client.case-type-list')
				->with('case_type_array',$array)
				->with('title','Case Types List');
	}

}
