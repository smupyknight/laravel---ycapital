<?php

namespace App\Http\Controllers;

use App\StatesSubscribed;
use App\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Auth;
use App\User;
use App\ScrapeResult;
use App\CourtCase;
use Excel;
use App\Application;
use App\Hearing;
use DateTime;
use DateTimeZone;
use DB;
use Illuminate\Support\Facades\Response;
use App\CaseType;

class ClientController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');
		//$this->middleware('client');
	}

	public function getExportCsv(Request $request)
	{
		if($request->notification_date=="" && $request->court_type=="" && $request->case_type==""
			 && $request->hearing_type=="" && $request->hearing_date == "" && $request->document_date==""
			&& $request->court_suburb=="" && $request->state =="")
		{
			return ['status'=>'fail','message' => 'Please select at least one filter'];
		}

		set_time_limit(0);

		Excel::create('Alares-export-'.Carbon::now()->format('Y-m-d'),function($excel) use ($request){
			$excel->sheet('Data',function($sheet) use ($request){

				$row_one_headers = array_fill(0, 40, '');
				$row_one_headers[8] = 'Responding Party 1';
				$row_one_headers[11] = 'Responding Party 2';
				$row_one_headers[14] = 'Responding Party 3';
				$row_one_headers[17] = 'Responding Party 4';
				$row_one_headers[20] = 'Initiating Party 1';
				$row_one_headers[23] = 'Initiating Party 2';
				$row_one_headers[26] = 'Initiating Party 3';
				$row_one_headers[29] = 'Initiating Party 4';
				$row_one_headers[32] = 'Other Party';

				$sheet->row(1,$row_one_headers);

				$sheet->cells('A1:AO1',function($cells) {
					$cells->setFontWeight('bold');
				});

				$row_two_headers = [
					'State',
					'Court Type',
					'Application Date Filed',
					'Application Title',
					'Application Type',
					'Case Name',
					'Case ID',
					'Application Status',
					'Party Name',
					'Party Role',
					'Party ACN/ABN',
					'Party Name',
					'Party Role',
					'Party ACN/ABN',
					'Party Name',
					'Party Role',
					'Party ACN/ABN',
					'Party Name',
					'Party Role',
					'Party ACN/ABN',
					'Party Name',
					'Party Role',
					'Party ACN/ABN',
					'Party Name',
					'Party Role',
					'Party ACN/ABN',
					'Party Name',
					'Party Role',
					'Party ACN/ABN',
					'Party Name',
					'Party Role',
					'Party ACN/ABN',
					'Party Name',
					'Party Role',
					'Party ACN/ABN',
					'Hearing Date',
					'Hearing Reason',
					'Hearing Type',
					'Link to Case',
				];

				$sheet->row(2,$row_two_headers);
				$sheet->cells('A2:AV2',function($cells){
					$cells->setFontWeight('bold');
				});

				$cases = CourtCase::query()
						  ->select('cases.*')
						  ->getCases($request)
						  ->groupBy('cases.id')
						  ->orderBy('cases.updated_at','desc')
						  ->limit(1000)
						  ->get();

				$i = 3;
				foreach ($cases as $case) {
					$data = $this->_buildData($case);

					$sheet->row($i, $data);
					$i++;
				}
			});
		})->export('xls');

	}

	private function _buildData($case)
	{
		$responding_array = [
			'accused','co-respondent','cross defendant','cross respondent',
			'defendant','defendant counter claim','prospective respondent',
			'respondent','respondent - appeal'
		];

		$initiating_array = [
			'appellant','applicant','complainant','cross appellant',
			'cross claimant','cross-appellant','cross-claimant',
			'plaintiff','plaintiff counter claim','prospective applicant',
			'substituted plaintiff'
		];

		$other_array = array_merge($responding_array, $initiating_array);

		foreach ($case->applications as $application) {
			$responding_parties = $application->parties()->whereIn('role', $responding_array)->get()->toArray();
			$initiating_parties = $application->parties()->whereIn('role', $initiating_array)->get()->toArray();
			$other_parties = $application->parties()->where(function($q) use ($other_array) {
												  $q->whereNotIn('role', $other_array)
														->orWhere('role', '');
												  })->get();
			$application_title = $application->title;
			$application_type = $application->type;
			$application_date_filed = $application->date_filed;
			$application_status = $application->status;
		}

		$other_party_names = [];
		$other_party_roles = [];
		$other_party_abns = [];

		foreach ($other_parties as $party) {
			$other_party_names[] = $party->name;
			$other_party_roles[] = $party->role;
			$other_party_abns[] = $party->abn;
			$other_party_abns[] = $party->acn;
		}

		$other_party_names = array_filter($other_party_names);
		$other_party_roles = array_filter($other_party_roles);
		$other_party_abns = array_filter($other_party_abns);

		$initiating_parties = $this->_getInitiatingParties($initiating_parties);
		$responding_parties = $this->_getRespondingParties($responding_parties);

		$data = [
			'State'                  => $case->state,
			'Court Type'             => $case->court_type,
			'Application Date Filed' => $application_date_filed,
			'Application Title'      => $application_title,
			'Application Type'       => $application_type,
			'Case Name'              => $case->case_name,
			'Case ID'                => $case->id,
			'Application Status'     => $application_status,
			'Party Name 1'           => isset($responding_parties[0]) ? $responding_parties[0]['name'] : '',
			'Party Role 1'           => isset($responding_parties[0]) ? $responding_parties[0]['role'] : '',
			'Party ACN/ABN 1'        => isset($responding_parties[0]) ? implode(', ', [$responding_parties[0]['abn'], $responding_parties[0]['acn']]) : '',
			'Party Name 2'           => isset($responding_parties[1]) ? $responding_parties[1]['name'] : '',
			'Party Role 2'           => isset($responding_parties[1]) ? $responding_parties[1]['role'] : '',
			'Party ACN/ABN 2'        => isset($responding_parties[1]) ? implode(', ', [$responding_parties[1]['abn'], $responding_parties[1]['acn']]) : '',
			'Party Name 3'           => isset($responding_parties[2]) ? $responding_parties[2]['name'] : '',
			'Party Role 3'           => isset($responding_parties[2]) ? $responding_parties[2]['role'] : '',
			'Party ACN/ABN 3'        => isset($responding_parties[2]) ? implode(', ', [$responding_parties[2]['abn'], $responding_parties[2]['acn']]) : '',
			'Party Name 4'           => isset($responding_parties[3]) ? $responding_parties[3]['name'] : '',
			'Party Role 4'           => isset($responding_parties[3]) ? $responding_parties[3]['role'] : '',
			'Party ACN/ABN 4'        => isset($responding_parties[3]) ? $responding_parties[3]['abn'] : '',
			'Party Name 5'           => isset($initiating_parties[0]) ? $initiating_parties[0]['name'] : '',
			'Party Role 5'           => isset($initiating_parties[0]['role']) ? $initiating_parties[0]['role'] : '',
			'Party ACN/ABN 5'        => isset($initiating_parties[0]) ? implode(', ', [$initiating_parties[0]['abn'], $initiating_parties[0]['acn']]) : '',
			'Party Name 6'           => isset($initiating_parties[1]) ? $initiating_parties[1]['name'] : '',
			'Party Role 6'           => isset($initiating_parties[1]) ? $initiating_parties[1]['role'] : '',
			'Party ACN/ABN 6'        => isset($initiating_parties[1]) ? implode(', ', [$initiating_parties[1]['abn'], $initiating_parties[1]['acn']]) : '',
			'Party Name 7'           => isset($initiating_parties[2]) ? $initiating_parties[2]['name'] : '',
			'Party Role 7'           => isset($initiating_parties[2]) ? $initiating_parties[2]['role'] : '',
			'Party ACN/ABN 7'        => isset($initiating_parties[2]) ? implode(', ', [$initiating_parties[2]['abn'], $initiating_parties[2]['acn']]) : '',
			'Party Name 8'           => isset($initiating_parties[3]) ? $initiating_parties[3]['name'] : '',
			'Party Role 8'           => isset($initiating_parties[3]) ? $initiating_parties[3]['role'] : '',
			'Party ACN/ABN 8'        => isset($initiating_parties[3]) ? $initiating_parties[3]['abn'] : '',
			'Party Name 9'           => isset($other_party_names) ? implode(', ', $other_party_names) : '',
			'Party Role 9'           => isset($other_party_roles) ? implode(', ', $other_party_roles) : '',
			'Party ACN/ABN 9'        => isset($other_party_abns) ? implode(', ', $other_party_abns) : '',
			'Hearing Date'           => $case->getNextHearingDate() !== null ? $case->getNextHearingDate()->format('d/m/Y') : '',
			'Hearing Reason'         => $case->getHearingData()['hearing_reason'],
			'Hearing Type'           => $case->getHearingData()['hearing_type'],
			'Link to Case'           => url() . '/client/cases/view/' . $case->id,
		];

		return $data;
	}

	/**
	 * Returns the same initiating parties array, but with parties after the 4th
	 * appended to the 4th's with comma separated values.
	 */
	private function _getInitiatingParties($initiating_parties)
	{
		$extra_party_names = [];
		$extra_party_roles = [];
		$extra_party_abns = [];

		foreach ($initiating_parties as $index => $party) {
			if ($index >= 3) {
				$extra_party_names[] = $party['name'];
				$extra_party_roles[] = $party['role'];
				$extra_party_abns[] = $party['abn'];
				$extra_party_abns[] = $party['acn'];
			}
		}

		$extra_party_names = array_filter($extra_party_names);
		$extra_party_roles = array_filter($extra_party_roles);
		$extra_party_abns = array_filter($extra_party_abns);

		$initiating_parties[3]['name'] = implode(', ', $extra_party_names);
		$initiating_parties[3]['role'] = implode(', ', $extra_party_roles);
		$initiating_parties[3]['abn'] = implode(', ', $extra_party_abns);

		return $initiating_parties;
	}

	/**
	 * Returns the same responding parties array, but with parties after the 4th
	 * appended to the 4th's with comma separated values.
	 */
	private function _getRespondingParties($responding_parties)
	{
		$extra_party_names = [];
		$extra_party_roles = [];
		$extra_party_abns = [];

		foreach ($responding_parties as $index => $party) {
			if ($index >= 3) {
				$extra_party_names[] = $party['name'];
				$extra_party_roles[] = $party['role'];
				$extra_party_abns[] = $party['abn'];
				$extra_party_abns[] = $party['acn'];
			}
		}

		$extra_party_names = array_filter($extra_party_names);
		$extra_party_roles = array_filter($extra_party_roles);
		$extra_party_abns = array_filter($extra_party_abns);

		$responding_parties[3]['name'] = implode(', ', $extra_party_names);
		$responding_parties[3]['role'] = implode(', ', $extra_party_roles);
		$responding_parties[3]['abn'] = implode(', ', $extra_party_abns);

		return $responding_parties;
	}

}
