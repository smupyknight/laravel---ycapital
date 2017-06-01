<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DateTimeZone;
use App\Setting;
use Log;

class CourtCase extends Model
{
	protected $table = 'cases';

	protected $dates = ['notification_time'];

	/**
	 * Get the Scrape Result details for export
	 * @param  query $query
	 * @param  array $data
	 * @param  array $states
	 * @return array
	 */
	protected static function boot()
	{
		parent::boot();
	}

	/**
	 * @param Request $request
	 * @param string  $type     'filter' or null
	 * @param string  $field    'party_representative','court_suburb','hearing_type', or 'case_type'
	 */
	public function scopeGetCases($query, $request, $type = null, $field = null)
	{
		$join_hearings = false;
		$join_parties = false;
		$join_documents = false;

		switch ($field) {
			case 'party_representative': $join_parties = true; break;
			case 'court_suburb': $join_hearings = true; break;
			case 'hearing_type': $join_hearings = true; break;
		}

		$query->join('applications AS a', 'a.case_id', '=', 'cases.id');

		// State
		if ($request->state == 'federal') {
			$query->where('court_type', 'federal');
		} elseif ($request->state) {
			$query->where('state', $request->state);
			$query->where('court_type', '!=', 'Federal');
		}

		// Notification time
		$today = Carbon::today(new DateTimeZone('Australia/Sydney'))->setTimezone('UTC');
		$cutoff = $today->copy();

		if ($request->notification_date) {
			switch ($request->notification_date) {
				case 'today': $query->where('cases.notification_time', '>=', $cutoff); break;
				case 'l7d':   $query->where('cases.notification_time', '>=', $cutoff->subDays(7)); break;
				case 'l30d':  $query->where('cases.notification_time', '>=', $cutoff->subDays(30)); break;
			}
		} else {
			$query->where('cases.notification_time', '>=', $cutoff);
		}

		// Court types
		if (is_array($request->court_types)) {
			$court_types = $request->get('court_types');

			$query->where(function($query) use ($court_types) {
				foreach ($court_types as $court_type) {
					if ($court_type == 'Magistrates/Local') {
						$query->orWhere('cases.court_type', 'Magistrates');
						$query->orWhere('cases.court_type', 'Local');
					} elseif ($court_type == 'District/County') {
						$query->orWhere('cases.court_type', 'District');
						$query->orWhere('cases.court_type', 'County');
					} else {
						$query->orWhere('cases.court_type', $court_type);
					}
				}
			});
		}

		// Jurisdiction
		if (Setting::where('field','criminal_jurisdiction')->first()->value == 0) {
			$query->where('cases.jurisdiction', '!=', 'Criminal');
		}

		if ($request->jurisdiction) {
			$query->where('cases.jurisdiction', $request->jurisdiction);
		}

		// Case type
		if (is_array($request->case_types) && $field != 'case_type') {
			$query->whereIn('cases.case_type', $request->case_types);
		}

		// Hearing type
		if ($request->hearing_types) {
			$query->whereIn('h.type', $request->hearing_types);
			$join_hearings = true;
		}

		// Hearing date
		if ($request->hearing_date) {
			$cutoff = $today->copy();

			switch ($request->hearing_date) {
				case 'n7d':  $cutoff->addDays(7); break;
				case 'n30d': $cutoff->addDays(30); break;
				case 'n90d': $cutoff->addDays(90); break;
			}

			$query->where('h.datetime', '>=', $today);
			$query->where('h.datetime', '<=', $cutoff);
			$join_hearings = true;
		}

		// Document date
		if ($request->document_date) {
			$cutoff = $today->copy();

			switch ($request->document_date) {
				case 'l1d':  $cutoff->subDays(1); break;
				case 'l7d':  $cutoff->subDays(7); break;
				case 'l30d': $cutoff->subDays(30); break;
			}

			$query->where('d.datetime', '>=', $cutoff);
			$join_documents = true;
		}

		// Court suburb
		if (is_array($request->court_suburbs) && $field != 'court_suburb') {
			$query->whereIn('cases.suburb', $request->court_suburbs);
		}

		// Party representatives
		if (is_array($request->party_representatives)) {
			$query->whereIn('p.name', $request->party_representatives);
			$join_parties = true;
		}

		if ($join_hearings)  $query->leftJoin('hearings AS h',  'h.application_id', '=', 'a.id');
		if ($join_parties)   $query->leftJoin('parties AS p',   'p.application_id', '=', 'a.id');
		if ($join_documents) $query->leftJoin('documents AS d', 'd.application_id', '=', 'a.id');

		return $query;
	}

	/**
	 * Adds the case to the watchlist_queue table.
	 *
	 * If the case already exists in the watchlist queue, it is ignored.
	 */
	public function addToWatchlistQueue()
	{
		if (!WatchlistQueueItem::find($this->id)) {
			WatchlistQueueItem::create(['id' => $this->id]);
		}
	}

	public function getNextHearingDate()
	{

		$next = null;

		foreach ($this->applications as $app) {
			foreach ($app->hearings as $hearing) {
				if ($hearing->datetime > Carbon::now()) {
					if($next == null) {
						$next = $hearing->datetime;
					}
					$next = min($next, $hearing->datetime);
				}
			}
		}

		return $next;
	}

	public function getHearingData()
	{
		$hearing_reason = '';
		$hearing_type = '';

		foreach ($this->applications as $application) {
			foreach ($application->hearings as $hearing) {
				$hearing_reason = $hearing->reason;
				$hearing_type = $hearing->type;
			}
		}

		return [
			'hearing_reason' => $hearing_reason,
			'hearing_type' => $hearing_type,
		];
	}

	public function getTimezone()
	{
		switch ($this->state) {
			case 'QLD':
				return 'Australia/Brisbane';
			case 'NSW':
			case 'ACT':
				return 'Australia/Sydney';
			case 'TAS':
				return 'Australia/Hobart';
			case 'VIC':
				return 'Australia/Melbourne';
			case 'SA':
				return 'Australia/Adelaide';
			case 'NT':
				return 'Australia/Darwin';
			case 'WA':
				return 'Australia/Perth';
			default:
				return 'Australia/Sydney';
		}
	}

	/**
	 * Get the applications for cases
	 */
	public function applications()
	{
		return $this->hasMany('App\Application','case_id','id');
	}

	/**
	 * Get the hearings for applications
	 */
	public function hearings()
	{
		return $this->hasManyThrough('App\Hearing','App\Application','case_id', 'application_id');
	}

	/**
	 * Get the documents for applications
	 */
	public function documents()
	{
		return $this->hasManyThrough('App\Document','App\Application','case_id', 'application_id');
	}

	/**
	 * Get the parties for applications
	 */
	public function parties()
	{
		return $this->hasManyThrough('App\Party','App\Application','case_id', 'application_id');
	}

	public function updates()
	{
		return $this->hasMany('App\Update', 'case_id')->orderBy('id', 'desc');
	}

}
