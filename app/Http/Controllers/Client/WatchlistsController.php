<?php

namespace App\Http\Controllers\Client;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Watchlist;
use App\WatchlistEntity;
use App\WatchlistNotification;
use App\WatchlistSubscriber;
use Auth;
use SoapClient;
use stdClass;
use Validator;
use App\AbrLookup;

class WatchlistsController extends Controller
{

	public function __construct()
	{
		if (!Auth::user()->can_access_watchlists) {
			return redirect('/not-subscribed');
		}
	}

	/**
	 * Show watchlist page.
	 *
	 */
	public function getIndex()
	{
		$watchlists = Watchlist::where('created_by', Auth::user()->id)
		                       ->orderBy('id', 'desc')
		                       ->get();

		$notifications = WatchlistNotification::join('watchlist_entities AS e', 'watchlist_notifications.watchlist_entity_id', '=', 'e.id')
		                                      ->join('watchlists AS w', 'e.watchlist_id', '=', 'w.id')
		                                      ->where('w.created_by', '=', Auth::user()->id)
		                                      ->orderBy('watchlist_notifications.id', 'desc')
		                                      ->select('watchlist_notifications.*')
		                                      ->take(10)
		                                      ->get();

		return view('pages.client.watchlists-list')
		     ->with('title', 'Watchlists')
		     ->with('watchlists', $watchlists)
		     ->with('notifications', $notifications);
	}

	/**
	 * Ajax call to return entity notifications.
	 *
	 * @param  int $entity_id
	 * @return Illuminate\View\View
	 */
	public function getEntityNotifications($entity_id)
	{
		$notifications = WatchlistNotification::where('watchlist_entity_id', $entity_id)->orderBy('id', 'desc')->get();
		$view = view('partials.entity-notification')
					->with('notifications', $notifications)
					->with('entity', WatchlistEntity::find($entity_id));
		return $view;
	}

	/**
	 * Ajax call to return more notifications for all user's watchlists.
	 *
	 * @param  int $counter
	 * @return Illuminate\Http\JsonResponse
	 */
	public function getMoreNotificationsAll($counter)
	{
		$notifications = WatchlistNotification::join('watchlist_entities AS e', 'watchlist_notifications.watchlist_entity_id', '=', 'e.id')
		                                      ->join('watchlists AS w', 'e.watchlist_id', '=', 'w.id')
		                                      ->where('w.created_by', '=', Auth::user()->id)
		                                      ->orderBy('watchlist_notifications.id', 'desc')
		                                      ->select('watchlist_notifications.*')
		                                      ->take(10)
		                                      ->skip($counter)
		                                      ->get();

		$html = view('partials.watchlist-list-notifications')
				->with('notifications', $notifications)
				->render();

		$next_notifications = WatchlistNotification::join('watchlist_entities AS e', 'watchlist_notifications.watchlist_entity_id', '=', 'e.id')
		                                      ->join('watchlists AS w', 'e.watchlist_id', '=', 'w.id')
		                                      ->where('w.created_by', '=', Auth::user()->id)
		                                      ->select('watchlist_notifications.*')
		                                      ->take(10)
		                                      ->skip($counter + 10)
		                                      ->get();

		$data = [
			'html' => $html,
			'show_more' => (count($next_notifications)) ? 1 : 0
		];

		return response()->json($data);
	}

	/**
	 * Ajax call to get list of watchlist by logged in user.
	 *
	 * @return Illuminate\Http\JsonResponse
	 */
	public function getList()
	{
		$watchlists = Watchlist::whereCreatedBy(Auth::user()->id)
		                       ->orderBy('name')
		                       ->get(['id','name']);

		return response()->json($watchlists);
	}

	/**
	 * Show manage page for specific watchlist.
	 *
	 * @param  int $watchlist_id
	 * @return Illuminate\View\View
	 */
	public function getManage($watchlist_id)
	{
		$watchlist = Watchlist::findOrFail($watchlist_id);
		if ($watchlist->created_by != Auth::user()->id) {
			return redirect('/unauthorised');
		}

		$notifications = $watchlist->notifications()
		                           ->orderBy('id', 'DESC')
		                           ->paginate(25);

		return view('pages.client.watchlists-manage')
		     ->with('title', 'Watchlist - '.$watchlist->name)
		     ->with('watchlist', $watchlist)
		     ->with('notifications', $notifications);
	}

	/**
	 * Show entities page for specific watchlist.
	 *
	 * @param  int $watchlist_id
	 * @return Illuminate\View\View
	 */
	public function getCompanies($watchlist_id)
	{
		$watchlist = Watchlist::findOrFail($watchlist_id);

		if ($watchlist->created_by != Auth::user()->id) {
			return redirect('/unauthorised');
		}

		$entities = $watchlist->entities()->whereType('Company')->get();

		return view('pages.client.watchlists-companies')
		     ->with('title', 'Watchlist - '.$watchlist->name)
		     ->with('watchlist', $watchlist)
		     ->with('entities', $entities);
	}

	/**
	 * Show entities page for specific watchlist.
	 *
	 * @param  int $watchlist_id
	 * @return Illuminate\View\View
	 */
	public function getIndividuals($watchlist_id)
	{
		$watchlist = Watchlist::findOrFail($watchlist_id);

		if ($watchlist->created_by != Auth::user()->id) {
			return redirect('/unauthorised');
		}

		$entities = $watchlist->entities()->whereType('Individual')->get();

		return view('pages.client.watchlists-individuals')
		     ->with('title', 'Watchlist - '.$watchlist->name)
		     ->with('watchlist', $watchlist)
		     ->with('entities', $entities);
	}

	/**
	 * Show subscribers page for specific watchlist.
	 *
	 * @param  int $watchlist_id
	 * @return Illuminate\View\View
	 */
	public function getSubscribers($watchlist_id)
	{
		$watchlist = Watchlist::findOrFail($watchlist_id);
		if ($watchlist->created_by != Auth::user()->id) {
			return redirect('/unauthorised');
		}

		return view('pages.client.watchlists-subscribers')
		     ->with('title', 'Watchlist - '.$watchlist->name)
		     ->with('watchlist', $watchlist);
	}

	/**
	 * Show watchlist notifications page
	 *
	 * @return Illuminate\View\View
	 */
	public function getNotifications()
	{
		return view('pages.client.watchlists-notifications');
	}

	/**
	 * Deletes watchlist.
	 *
	 * Also deletes the watchlist's subsribers and entities.
	 *
	 * @param  int  $watchlist_id
	 */
	public function postDelete($watchlist_id)
	{
		$watchlist = Watchlist::where('created_by', Auth::user()->id)->findOrFail($watchlist_id);

		WatchlistSubscriber::where('watchlist_id', $watchlist_id)->delete();
		WatchlistEntity::where('watchlist_id', $watchlist_id)->delete();

		$watchlist->delete();
	}

	/**
	 * Post function that adds a watchlist to the logged in user.
	 *
	 * @param  Request $request
	 */
	public function postAdd(Request $request)
	{
		$this->validate($request, [
			'name' => 'required'
		]);

		$watchlist = new Watchlist;
		$watchlist->name = $request->name;
		$watchlist->created_by = Auth::user()->id;
		$watchlist->save();

		return response()->json(['watchlist_id' => $watchlist->id]);
	}

	/**
	 * Post function to update the watchlist by the user.
	 *
	 * @param  Request $request
	 * @param  int  $watchlist_id
	 */
	public function postEdit(Request $request, $watchlist_id)
	{
		$this->validate($request, [
			'name' => 'required'
		]);

		$watchlist = Watchlist::findOrFail($watchlist_id);
		$watchlist->name = $request->name;
		$watchlist->save();
	}

	/**
	 * Post function to add an entity to a specific watchlist
	 *
	 * @param  Request $request
	 * @return Illuminate\Http\RedirectResponse
	 */
	public function postAddCompany(Request $request)
	{
		$request->merge(['abn_acn' => str_replace(' ', '', $request->abn_acn)]);

		$this->validate($request, [
			'watchlist_id' => 'required',
			'party_name'   => 'required_if:type,party_name|unique:watchlist_entities,party_name,NULL,id,watchlist_id,'.$request->watchlist_id,
			'abn_acn'      => 'required_if:type,abn_acn|digits_between:9,11|valid_abn',
		], [
			'abn_acn.valid_abn' => 'Invalid ABN'
		]);

		$abn = preg_replace('/[^0-9]/', '', $request->abn_acn);

		$entity = new WatchlistEntity;
		$entity->watchlist_id = $request->watchlist_id;
		$entity->type = 'Company';

		if ($request->type == 'party_name') {
			$entity->party_name = $request->party_name;
		} elseif ($request->type == 'abn_acn') {
			$abr_lookup = new AbrLookup;
			$result = $abr_lookup->getRecordFromAbrNumber($abn);

			$entity->party_name = $result['name'];

			if (strlen($abn) == 11) {
				$entity->abn = $abn;
				$entity->acn = $result['acn'];
			} else {
				$entity->acn = $abn;
				$entity->abn = $result['abn'];
			}
		}

		$entity->created_by = Auth::user()->id;
		$entity->save();

		return redirect('client/watchlists/companies/'.$request->watchlist_id);
	}

	/**
	 * Post function to add an entity to a specific watchlist
	 *
	 * @param  Request $request
	 * @return Illuminate\Http\RedirectResponse
	 */
	public function postAddIndividual(Request $request)
	{
		$request->merge(['abn' => str_replace(' ', '', $request->abn)]);

		$this->validate($request, [
			'watchlist_id'      => 'required',
			'party_given_names' => 'required_if:type,party_name',
			'party_last_name'   => 'required_if:type,party_name',
			'abn'               => 'required_if:type,abn|digits:11|valid_abn',
		], [
			'abn.valid_abn' => 'Invalid ABN'
		]);

		$abn = preg_replace('/[^0-9]/', '', $request->abn);

		$entity = new WatchlistEntity;
		$entity->watchlist_id = $request->watchlist_id;
		$entity->type = 'Individual';

		if ($request->type == 'party_name') {
			$entity->party_name = $request->party_given_names . ' ' . $request->party_last_name;
			$entity->party_given_names = $request->party_given_names;
			$entity->party_last_name = $request->party_last_name;
		} elseif ($request->type == 'abn') {
			$entity->abn = $request->abn;
		}

		$entity->created_by = Auth::user()->id;
		$entity->save();

		return redirect('client/watchlists/individuals/'.$request->watchlist_id);
	}

	/**
	 * Maybe creates a watchlist, then adds an entity to it.
	 */
	public function postAddEntityFromCaseList(Request $request)
	{
		$this->validate($request, [
			'name' => 'required_if:watchlist_id,new',
		]);

		if ($request->watchlist_id == 'new') {
			$watchlist = new Watchlist;
			$watchlist->name = $request->name;
			$watchlist->created_by = Auth::user()->id;
			$watchlist->save();
		} else {
			$watchlist = Watchlist::where('created_by', Auth::user()->id)->findOrFail($request->watchlist_id);
		}

		$request->merge(['abn_acn' => str_replace(' ', '', $request->abn_acn)]);

		$this->validate($request, [
			'party_name' => 'unique:watchlist_entities,party_name,NULL,id,watchlist_id,' . $watchlist->id,
			'abn_acn'    => 'digits_between:9,11',
		]);

		$entity = new WatchlistEntity;
		$entity->watchlist_id = $watchlist->id;
		$entity->party_name = $request->party_name;

		$abn = preg_replace('/[^0-9]/', '', $request->abn_acn);

		if (strlen($abn) == 11) {
			$entity->abn = $abn;
		} else {
			$entity->acn = $abn;
		}

		$entity->created_by = Auth::user()->id;
		$entity->save();
	}

	/**
	 * Deletes a single entity of a watchlist.
	 *
	 * @param  Request $request
	 */
	public function postDeleteEntity(Request $request)
	{
		$entity = WatchlistEntity::findOrFail($request->entity_id);
		$entity->delete();
	}

	/**
	 * Deletes multiple entites of a watchlist.
	 *
	 * @param  Request $request
	 * @param  int  $watchlist_id
	 * @return Illuminate\Http\RedirectResponse
	 */
	public function postDeleteEntities(Request $request, $watchlist_id)
	{
		if ($request->checkbox_entities) {
			WatchlistEntity::whereIn('id', $request->checkbox_entities)->delete();
		}

		return redirect('/client/watchlists/entities/'.$watchlist_id);
	}

	/**
	 * Adds a subscriber to a watchlist.
	 *
	 * @param  Request $request
	 */
	public function postAddSubscriber(Request $request)
	{
		$this->validate($request, [
			'email'        => 'required|email|unique_subscriber_name_email:'.$request->watchlist_id.','.$request->name.','.$request->email,
			'name'         => 'required',
			'watchlist_id' => 'required',
		], [
			'email.unique_subscriber_name_email' => 'Name and Email combination already exists for this watchlist.'
		]);

		$subscriber = new WatchlistSubscriber;
		$subscriber->watchlist_id = $request->watchlist_id;
		$subscriber->name = $request->name;
		$subscriber->email = $request->email;
		$subscriber->created_by = Auth::user()->id;
		$subscriber->save();
	}

	/**
	 * Imports a CSV file to add multiple entites for the given watchlist.
	 *
	 * @param  Request $request
	 * @param  int  $watchlist_id
	 * @return Illuminate\Http\RedirectResponse
	 */
	public function postImportEntities(Request $request, $watchlist_id)
	{
		$this->validate($request, [
			'import_entities_file' => 'required|mimes:csv,txt',
		], [
			'import_entities_file.required' => 'Please select a file to upload.',
			'import_entities_file.mimes' => 'Please upload csv file.',
		]);

		$file = $request->file('import_entities_file');

		$open = fopen($file, 'r');
		while (($data = fgetcsv($open)) !== false) {
			if (!isset($data[1])) {
				return redirect('/client/watchlists/manage/'.$watchlist_id)->withErrors(['error' => 'Import CSV must have two columns. You can download the sample import entities file.']);
			}

			$content[] = [
				'abn' => $data[0],
				'acn' => $data[1]
			];
		}

		unset($content[0]);
		$errors = [];
		foreach ($content as $array) {
			$validator = Validator::make($array, [
					'abn' => 'digits:11|numeric',
					'acn' => 'digits:9|numeric',
				]);

			if ($validator->fails()) {
				$errors[] = [
					'value' => $array['abn'] ? $array['abn'] : $array['acn'],
					'message' => $validator->messages()
				];
			}
		}

		if ($errors == []) {
			foreach ($content as $array) {
				if ($array['abn']) {
					$data = $array['abn'];
				} else {
					$data = $array['acn'];
				}

				$entity = new WatchlistEntity;
				$entity->watchlist_id = $watchlist_id;

				$abr_lookup = new AbrLookup;
				$result = $abr_lookup->getRecordFromAbrNumber(str_replace(' ', '', $data));

				$entity->party_name = $result['name'];

				if (strlen($data) == 11) {
					$entity->abn = $data;
					$entity->acn = $result['acn'];
				} else {
					$entity->acn = $data;
					$entity->abn = $result['abn'];
				}

				$entity->created_by = Auth::user()->id;
				$entity->save();
			}
		}

		return redirect('/client/watchlists/companies/'.$watchlist_id)->withErrors($errors);
	}

	/**
	 * Imports a CSV file to add multiple entites for the given watchlist.
	 *
	 * @param  Request $request
	 * @param  int  $watchlist_id
	 * @return Illuminate\Http\RedirectResponse
	 */
	public function postImportIndividual(Request $request, $watchlist_id)
	{
		$this->validate($request, [
			'import_individual_file' => 'required|mimes:csv,txt',
		], [
			'import_individual_file.required' => 'Please select a file to upload.',
			'import_individual_file.mimes' => 'Please upload csv file.',
		]);

		$file = $request->file('import_individual_file');

		$open = fopen($file, 'r');
		while (($data = fgetcsv($open)) !== false) {
			if (!isset($data[1])) {
				return redirect('/client/watchlists/manage/'.$watchlist_id)->withErrors(['error' => 'Import CSV must have two columns. You can download the sample import entities file.']);
			}

			$errors = [];

			$validator = Validator::make(['abn' => $data[1]], [
				'abn' => 'digits:11|numeric',
			]);

			if ($validator->fails()) {
				$errors[] = [
					'value' => $data[1],
					'message' => $validator->messages()
				];
			}

			if ($errors == []) {
				$entity = new WatchlistEntity;
				$entity->watchlist_id = $watchlist_id;
				$entity->type = 'Individual';
				$entity->party_name = $data[0];
				$entity->abn = $data[1];
				$entity->created_by = Auth::user()->id;

				$entity->save();
			}
		}

		return redirect('/client/watchlists/individuals/'.$watchlist_id)->withErrors($errors);
	}

	/**
	 * Deletes subscriber of a watchlist
	 *
	 * @param  Request $request
	 */
	public function postDeleteSubscriber(Request $request)
	{
		$subscriber = WatchlistSubscriber::findOrFail($request->subscriber_id);
		$subscriber->delete();
	}

	/**
	 * Returns an array of all subscriber email addresses across all the user's watchlists to which the name contain $query.
	 *
	 * @param  string  $query   Part of the name to search for, like "John Doe"
	 * @return Illuminate\Database\Eloquent\Collection
	 */
	public function getSubscriberNameList($query)
	{
		$watchlists = Watchlist::where('created_by', Auth::user()->id)
					->lists('id')
					->toArray();

		$subscribers = WatchlistSubscriber::where('name', 'like', '%'.$query.'%')
						->groupBy('name', 'email')
						->whereIn('watchlist_id', $watchlists)
						->get();

		return $subscribers;
	}

	/**
	 * Returns an array of all subscriber email addresses across all the user's watchlists to which the email contain $query.
	 *
	 * @param  string  $query   Part of the email to search for, like "example@email"
	 * @return Illuminate\Database\Eloquent\Collection
	 */
	public function getSubscriberEmailList($query)
	{
		$watchlists = Watchlist::where('created_by', Auth::user()->id)
					->lists('id')
					->toArray();

		$subscribers = WatchlistSubscriber::where('email', 'like', '%'.$query.'%')
						->groupBy('name', 'email')
						->whereIn('watchlist_id', $watchlists)
						->get();

		return $subscribers;
	}

	/**
	 * Shows the screen for adding entities from the ABR.
	 */
	public function getAddEntitiesFromAbr(Request $request, $watchlist_id)
	{
		$watchlist = Watchlist::findOrFail($watchlist_id);

		return view('pages.client.watchlists-add-entities-from-abr')
		     ->with('watchlist', $watchlist)
		     ->with('request', $request);
	}

	/**
	 * AJAX request for getting ABR results for the above endpoint.
	 */
	public function getLookupName(Request $request)
	{
		$abr_lookup = new AbrLookup();
		$result = $abr_lookup->searchByName($request->search);

		return response()->json($result);
	}

}
