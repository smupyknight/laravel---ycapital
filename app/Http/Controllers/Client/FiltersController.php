<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Filter;
use Auth;
use Illuminate\Http\Request;

class FiltersController extends Controller
{

	public function postCreate(Request $request)
	{
		$this->validate($request, [
			'name' => 'required',
		]);

		parse_str($request->fields, $fields);
		$fields = collect($fields);

		Filter::create([
			'user_id'               => Auth::user()->id,
			'name'                  => $request->name,
			'state'                 => $fields->get('state', ''),
			'court_type'            => $fields->get('court_type', ''),
			'notification_date'     => $fields->get('notification_date', ''),
			'case_types'            => json_encode($fields->get('case_types', [])),
			'hearing_types'         => json_encode($fields->get('hearing_type', [])),
			'hearing_date'          => $fields->get('hearing_date', ''),
			'document_date'         => $fields->get('document_date', ''),
			'court_suburbs'         => json_encode($fields->get('court_suburb', [])),
			'party_representatives' => json_encode($fields->get('party_representative', [])),
			'per_page'              => $fields->get('per_page', 20),
		]);
	}

	public function postEdit(Request $request)
	{
		$filter = Filter::where('user_id', Auth::user()->id)->findOrFail($request->filter_id);

		parse_str($request->fields, $fields);
		$fields = collect($fields);

		$filter->update([
			'state'                 => $fields->get('state', ''),
			'court_type'            => $fields->get('court_type', ''),
			'notification_date'     => $fields->get('notification_date', ''),
			'case_types'            => json_encode($fields->get('case_types', [])),
			'hearing_types'         => json_encode($fields->get('hearing_type', [])),
			'hearing_date'          => $fields->get('hearing_date', ''),
			'document_date'         => $fields->get('document_date', ''),
			'court_suburbs'         => json_encode($fields->get('court_suburb', [])),
			'party_representatives' => json_encode($fields->get('party_representative', [])),
			'per_page'              => $fields->get('per_page', 20),
		]);
	}

}
