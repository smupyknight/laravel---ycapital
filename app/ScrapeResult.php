<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Exception;
use Log;
use App\Setting;
use Carbon\Carbon;
use App\Update;
use App\UpdateField;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\AbrLookup;

class ScrapeResult extends Model
{
	/**
	 * Table associated with scrape results
	 * @var string
	 */
	protected $table = 'scrape_results';

	/**
	 * Validates a scrape result and saves notes if any issues were found.
	 *
	 * @return bool is_valid
	 */

	protected static function boot()
	{
		parent::boot();

		static::addGlobalScope( new CaseTypeScope());
	}

	public function validate()
	{
		$notes = [];
		$data = json_decode($this->data, true);

		if (stripos($this->case_name, 'xxx') !== false) {
			$notes[] = 'Case name contains dummy content.';
		}

		if ($notes) {
			$this->notes = json_encode(array_unique($notes));
		} else {
			$this->notes = '';
		}

		$this->save();

		return count($notes) == 0;
	}

	/**
	 * Approves a scrape result.
	 *
	 * If the case already exists, it is updated.
	 *
	 * The scrape result is deleted once approved.
	 */
	public function approve()
	{
		$case = CourtCase::whereUniqueId($this->scraper . '-' . $this->unique_id)->first();

		if ($case) {
			$is_new = false;
		} else {
			$case = new CourtCase;
			$case->unique_id = $this->scraper . '-' . $this->unique_id;
			$case->save();
			$is_new = true;
		}

		if (!$is_new) {
			$changes = $this->compare([
				'state'        => [$case->state, $this->state],
				'court_type'   => [$case->court_type, $this->court_type],
				'case_no'      => [$case->case_no, $this->case_no],
				'case_name'    => [$case->case_name, $this->case_name],
				'suburb'       => [$case->suburb, $this->getMajorityHearingSuburb()],
				'jurisdiction' => [$case->jurisdiction, $this->jurisdiction],
			]);

			if ($changes) {
				$update = Update::create([
					'case_id'     => $case->id,
					'entity_id'   => $case->id,
					'entity_type' => 'case',
					'action_type' => 'edit',
				]);

				$update->fields()->saveMany($changes);
			}
		}

		$case->state = $this->state;
		$case->court_type = $this->court_type;
		$case->case_no = $this->case_no;
		$case->case_name = $this->case_name;
		$case->case_type = $this->case_type;
		$case->suburb = $this->getMajorityHearingSuburb();
		$case->jurisdiction = $this->jurisdiction;
		$case->url = $this->url;
		$case->notification_time = $this->calculateNotificationTime($case);
		$case->save();

		$this->updateLastScrapeTimestamp($this->scraper);

		$data = json_decode($this->data, true);

		foreach ($data['applications'] as $index => $application) {
			$this->approveApplication($application, $index, $case, !$is_new);
		}

		if ($is_new) {
			dispatch(new \App\Jobs\AbrLookup($case));
		}

		$this->delete();
	}

	private function compare($fields)
	{
		$changes = [];
		$timezone = $this->getTimezone();

		// If new value is Y-m-d and old value is Y-m-d H:i:s, convert old value
		// to Y-m-d before comparison.
		foreach ($fields as $name => $values) {
			if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $values[1])) {
				continue;
			}

			try {
				$fields[$name][0] = Carbon::createFromFormat('Y-m-d H:i:s', $values[0])
					->format('Y-m-d');
			} catch (Exception $e) {
				// Ignore
			}
		}

		// Convert anything that looks like a datetime into a friendlier format
		foreach ($fields as $name => $values) {
			try {
				$fields[$name][0] = Carbon::createFromFormat('Y-m-d H:i:s', $values[0])
					->setTimezone($timezone)
					->format('j M Y, g:i:sa');
			} catch (Exception $e) {
				// Ignore
			}

			try {
				$fields[$name][1] = Carbon::createFromFormat('Y-m-d H:i:s', $values[1])
					->setTimezone($timezone)
					->format('j M Y, g:i:sa');
			} catch (Exception $e) {
				// Ignore
			}
		}

		foreach ($fields as $name => $values) {
			if ($values[0] != $values[1]) {
				$changes[] = new UpdateField([
					'name'      => $name,
					'old_value' => (string) $values[0],
					'new_value' => (string) $values[1],
				]);
			}
		}

		return $changes;
	}

	/**
	 * Determines the notification time.
	 *
	 * The earliest application file date is used, or the current time if there
	 * is no application file date.
	 */
	private function calculateNotificationTime(CourtCase $case)
	{
		$min_file_date = null;

		foreach ($case->applications as $application) {
			if (!$application['date_filed']) {
				continue;
			}

			if (!$min_file_date || $application['date_filed'] < $min_file_date) {
				$min_file_date = $application['date_filed'];
			}
		}

		if ($min_file_date) {
			return $min_file_date;
		}

		return Carbon::now();
	}

	private function updateLastScrapeTimestamp($scraper)
	{
		$setting = Setting::firstOrNew(['field' => 'last_scrape_'.$scraper]);
		$setting->value = new Carbon;
		$setting->save();
	}

	private function approveApplication(array $app_data, $index, CourtCase $case, $find_updates)
	{
		if (isset($case->applications[$index])) {
			$app = $case->applications[$index];
			$action = 'edit';
		} else {
			$app = new Application;
			$app->case_id = $case->id;
			$app->save();
			$action = 'create';
		}

		if ($find_updates) {
			$changes = $this->compare([
				'title'          => [$app->title, $app_data['title']],
				'type'           => [$app->type, $app_data['type']],
				'status'         => [$app->status, $app_data['status']],
				'date_filed'     => [$app->date_filed, $app_data['date_filed']],
				'date_finalised' => [$app->date_finalised, $app_data['date_finalised']],
			]);

			if ($changes) {
				$update = Update::create([
					'case_id'     => $case->id,
					'entity_id'   => $app->id,
					'entity_type' => 'application',
					'action_type' => $action,
				]);

				$update->fields()->saveMany($changes);
			}
		}

		$app->title = $app_data['title'];
		$app->type = $app_data['type'];
		$app->status = $app_data['status'];
		$app->date_filed = ($app_data['date_filed'] ? $app_data['date_filed'] : null);
		$app->date_finalised = ($app_data['date_finalised'] ? $app_data['date_finalised'] : null);
		$app->save();

		// Hearings
		$updated_hearing_ids = [];

		foreach ($app_data['hearings'] as $index => $hearing) {
			$updated_hearing_ids[] = $this->approveHearing($hearing, $index, $app, $find_updates);
		}

		$this->deleteAbandonedRelations($app->hearings(), $updated_hearing_ids, $case, 'hearing');

		// Parties
		$updated_party_ids = [];

		foreach ($app_data['parties'] as $index => $party) {
			$updated_party_ids[] = $this->approveParty($party, $index, $app, $find_updates);
		}

		$this->deleteAbandonedRelations($app->parties(), $updated_party_ids, $case, 'party');

		// Documents
		$updated_document_ids = [];

		foreach ($app_data['documents'] as $index => $document) {
			$updated_document_ids[] = $this->approveDocument($document, $index, $app, $find_updates);
		}

		$this->deleteAbandonedRelations($app->documents(), $updated_document_ids, $case, 'document');
	}

	private function approveHearing(array $hearing_data, $index, Application $app, $find_updates)
	{
		$app->hearings; // Make Laravel load the relation

		if (isset($app->hearings[$index])) {
			$hearing = $app->hearings[$index];
			$action = 'edit';
		} else {
			$hearing = new Hearing;
			$hearing->application_id = $app->id;
			$hearing->save();
			$action = 'create';
		}

		if ($find_updates) {
			$changes = $this->compare([
				'datetime'        => [$hearing->datetime, $hearing_data['datetime']],
				'reason'          => [$hearing->reason, $hearing_data['reason']],
				'officer'         => [$hearing->officer, $hearing_data['officer']],
				'court_room'      => [$hearing->court_room, $hearing_data['court_room']],
				'court_name'      => [$hearing->court_name, $hearing_data['court_name']],
				'court_phone'     => [$hearing->court_phone, $hearing_data['court_phone']],
				'court_address'   => [$hearing->court_address, $hearing_data['court_address']],
				'court_suburb'    => [$hearing->court_suburb, $hearing_data['court_suburb']],
				'type'            => [$hearing->type, $hearing_data['type']],
				'list_no'         => [$hearing->list_no, $hearing_data['list_no']],
				'outcome'         => [$hearing->outcome, $hearing_data['outcome']],
				'orders_filename' => [$hearing->orders_filename, $hearing_data['orders_filename']],
			]);

			if ($changes) {
				$update = Update::create([
					'case_id'     => $app->case_id,
					'entity_id'   => $hearing->id,
					'entity_type' => 'hearing',
					'action_type' => $action,
				]);

				$update->fields()->saveMany($changes);
			}
		}

		$hearing->datetime = $hearing_data['datetime'] ? $hearing_data['datetime'] : '';
		$hearing->reason = $hearing_data['reason'] ? $hearing_data['reason'] : '';
		$hearing->officer = $hearing_data['officer'] ? $hearing_data['officer'] : '';
		$hearing->court_room = $hearing_data['court_room'] ? $hearing_data['court_room'] : '';
		$hearing->court_name = $hearing_data['court_name'] ? $hearing_data['court_name'] : '';
		$hearing->court_phone = $hearing_data['court_phone'] ? $hearing_data['court_phone'] : '';
		$hearing->court_address = $hearing_data['court_address'] ? $hearing_data['court_address'] : '';
		$hearing->court_suburb = $hearing_data['court_suburb'] ? $hearing_data['court_suburb'] : '';
		$hearing->type = $hearing_data['type'] ? $hearing_data['type'] : '';
		$hearing->list_no = $hearing_data['list_no'] ? $hearing_data['list_no'] : '';
		$hearing->outcome = $hearing_data['outcome'] ? $hearing_data['outcome'] : '';
		$hearing->orders_filename = $hearing_data['orders_filename'] ? $hearing_data['orders_filename'] : '';
		$hearing->save();

		return $hearing->id;
	}

	private function approveParty(array $party_data, $index, Application $app, $find_updates)
	{
		$app->parties; // Make Laravel load the relation

		if (isset($app->parties[$index])) {
			$party = $app->parties[$index];
			$action = 'edit';
		} else {
			$party = new Party;
			$party->application_id = $app->id;
			$party->save();
			$action = 'create';
		}

		$name = html_entity_decode($party_data['name'], ENT_QUOTES | ENT_XML1, 'UTF-8');

		if ($find_updates) {
			$changes = $this->compare([
				'name'        => [$party->name, $name],
				'given_names' => [$party->given_names, $party_data['given_names']],
				'last_name'   => [$party->last_name, $party_data['last_name']],
				'type'        => [$party->type, $party_data['type']],
				'role'        => [$party->role, $party_data['role']],
				'rep_name'    => [$party->rep_name, $party_data['rep_name']],
				'address'     => [$party->address, $party_data['address']],
				'phone'       => [$party->phone, $party_data['phone']],
				'fax'         => [$party->fax, $party_data['fax']],
				'abn'         => [$party->abn, $party_data['abn']],
				'acn'         => [$party->acn, $party_data['acn']],
			]);

			if ($changes) {
				$update = Update::create([
					'case_id'     => $app->case_id,
					'entity_id'   => $party->id,
					'entity_type' => 'party',
					'action_type' => $action,
				]);

				$update->fields()->saveMany($changes);
			}
		}

		$party->name = $name;
		$party->given_names = $party_data['given_names'];
		$party->last_name = $party_data['last_name'];
		$party->type = $party_data['type'];
		$party->role = $party_data['role'];
		$party->rep_name = $party_data['rep_name'];
		$party->address = $party_data['address'];
		$party->phone = $party_data['phone'];
		$party->fax = $party_data['fax'];
		$party->abn = $party_data['abn'];
		$party->acn = $party_data['acn'];
		$party->searchable_name = $party->getCalculatedSearchableName();
		$party->save();

		return $party->id;
	}

	private function approveDocument(array $document_data, $index, Application $app, $find_updates)
	{
		$app->documents; // Make Laravel load the relation

		if (isset($app->documents[$index])) {
			$document = $app->documents[$index];
			$action = 'edit';
		} else {
			$document = new Document;
			$document->application_id = $app->id;
			$document->save();
			$action = 'create';
		}

		if ($find_updates) {
			$changes = $this->compare([
				'datetime'    => [$document->datetime, $document_data['datetime']],
				'title'       => [$document->title, $document_data['title']],
				'description' => [$document->description, $document_data['description']],
				'filed_by'    => [$document->filed_by, $document_data['filed_by']],
			]);

			if ($changes) {
				$update = Update::create([
					'case_id'     => $app->case_id,
					'entity_id'   => $document->id,
					'entity_type' => 'document',
					'action_type' => $action,
				]);

				$update->fields()->saveMany($changes);
			}
		}

		$document->datetime = $document_data['datetime'];
		$document->title = $document_data['title'];
		$document->description = $document_data['description'];
		$document->filed_by = $document_data['filed_by'];
		$document->save();

		return $document->id;
	}

	private function deleteAbandonedRelations($query, $updated_ids, $case, $entity_type)
	{
		if ($query->count() == count($updated_ids)) {
			return;
		}

		$query_copy = clone $query;
		$items = $query_copy->whereNotIn('id', $updated_ids)->get();

		foreach ($items as $item) {
			Update::create([
				'case_id'     => $case->id,
				'entity_id'   => $item->id,
				'entity_type' => $entity_type,
				'action_type' => 'delete',
			]);
		}

		$query->whereNotIn('id', $updated_ids)->delete();
	}

	public function getMajorityHearingSuburb()
	{
		$counts = [];
		$data = json_decode($this->data, true);

		foreach ($data['applications'] as $application) {
			foreach ($application['hearings'] as $hearing) {
				$suburb = $hearing['court_suburb'];

				if ($suburb) {
					if (!isset($counts[$suburb])) {
						$counts[$suburb] = 0;
					}

					$counts[$suburb]++;
				}
			}
		}

		if (!$counts) {
			return '';
		}

		asort($counts);

		return key($counts);
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

}
