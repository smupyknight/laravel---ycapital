@foreach ($results as $result)
	<?php $data = json_decode($result->data, true); ?>
	<tr class="clickable odd">
		<td><input class="checkbox-data-review" type="checkbox" id="checkbox_{{ $result->id }}" name="checkbox[]" value="{{ $result->id }}"></td>
		<td data-toggle="collapse" data-target="#accordion_{{ $result->id }}"  class="collapsed_icon"> </td>
		<td class="hoverable" onclick="create_input('{{ $result->id }}_created_at','{{ $result->created_at->format('m/d/Y') }}',this,'date')">{{ $result->created_at->format('m/d/Y') }}</td>
		<td class="hoverable" onclick="create_input('{{ $result->id }}_court_type','{{ addslashes($result->court_type) }}',this,'text')">{{ $result->court_type }}</td>
		<td class="hoverable" onclick="create_input('{{ $result->id }}_case_no','{{ addslashes($result->case_no) }}',this,'text')">{{ $result->case_no }}</td>
		<td class="hoverable" onclick="create_input('{{ $result->id }}_case_name','{{ addslashes($result->case_name) }}',this,'text')">{{ $result->case_name }}</td>
		<td class="hoverable" onclick="create_input('{{ $result->id }}_case_type','{{ addslashes($result->case_type) }}',this,'text')">{{ $result->jurisdiction }}</td>
		<td>
			@if ($result['notes'])
					@foreach (json_decode($result['notes']) as $key => $note)
						{{$note}}@if( count(json_decode($result['notes'])) != 1 && $key != count(json_decode($result['notes']))-1 ), @endif
					@endforeach
			@endif
		</td>
		<td>
			<div class="btn-group btn-group-xs">
				 <button type="button" class="btn btn-primary btn-xs" onclick="approve_data({{ $result->id }})">Approve</button>
				 <button type="button" class="btn btn-danger btn-xs" onclick="reject_data({{ $result->id }})">Reject</button>
			</div>
		</td>
	</tr>
	<tr id="accordion_{{ $result->id }}" class="collapse custom-accordion">
		<td colspan="13">
			<div class="boxed-row">
				<h4 class="text-left sub_table_title">Applications</h4>
				@foreach ($data['applications'] as $app_index => $application)
					<table id="" class="table dt-responsive table-bordered custom_data_table" cellspacing="0" width="100%">
						<tbody>
							<tr data-toggle="collapse" data-target="#subAccordionThree_{{ $result->id }}_{{ $app_index }}" class="clickable">
								<td colspan="13" class="text-left sub_panel_title">
									<i class="sub_panel_control">+</i> Parties
								</td>
							</tr>
							<tr id="subAccordionThree_{{ $result->id }}_{{ $app_index }}" class="collapse">
								<td class="inner_table_container">
									<table class="table dt-responsive table-bordered custom_data_table" cellspacing="0" width="100%">
										<thead>
											<tr>
												<th>Id</th>
												<th>Name</th>
												<th>Role</th>
												<th>Address</th>
												<th>Phone</th>
												<th>Fax</th>
												<th>Email</th>
												<th>ABN</th>
											</tr>
										</thead>
										<tbody>
											@foreach ($application['parties'] as $party_index => $party)
												<tr>
													<td class="hoverable" onclick="create_input('{{ $result->id }}_applications[{{ $app_index }}][parties][{{ $party_index }}][id]','{{ addslashes($party['id']) }}',this,'text')">{{ $party['id'] }}</td>
													<td class="hoverable" onclick="create_input('{{ $result->id }}_applications[{{ $app_index }}][parties][{{ $party_index }}][name]','{{ addslashes($party['name']) }}',this,'text')">{{ $party['name'] }}</td>
													<td class="hoverable" onclick="create_input('{{ $result->id }}_applications[{{ $app_index }}][parties][{{ $party_index }}][role]','{{ addslashes($party['role']) }}',this,'text')">{{ $party['role'] }}</td>
													<td class="hoverable" onclick="create_input('{{ $result->id }}_applications[{{ $app_index }}][parties][{{ $party_index }}][address]','{{ addslashes($party['address']) }}',this,'text')">{{ $party['address'] }}</td>
													<td class="hoverable" onclick="create_input('{{ $result->id }}_applications[{{ $app_index }}][parties][{{ $party_index }}][phone]','{{ addslashes($party['phone']) }}',this,'text')">{{ $party['phone'] }}</td>
													<td class="hoverable" onclick="create_input('{{ $result->id }}_applications[{{ $app_index }}][parties][{{ $party_index }}][fax]','{{ addslashes($party['fax']) }}',this,'text')">{{ $party['fax'] }}</td>
													<td class="hoverable" onclick="create_input('{{ $result->id }}_applications[{{ $app_index }}][parties][{{ $party_index }}][email]','{{ addslashes($party['email']) }}',this,'text')">{{ $party['email'] }}</td>
													<td class="hoverable" onclick="create_input('{{ $result->id }}_applications[{{ $app_index }}][parties][{{ $party_index }}][abn]','{{ addslashes($party['abn']) }}',this,'text')">{{ $party['abn'] }}</td>
												</tr>
											@endforeach

											@if (!$application['parties'])
												<tr>
													<td colspan="8"><b>No Results</b></td>
												</tr>
											@endif
										</tbody>
									</table>
								</td>
							</tr>
							<tr data-toggle="collapse" data-target="#subAccordionOne_{{ $result->id }}_{{ $app_index }}" class="clickable">
								<td colspan="13" class="text-left sub_panel_title">
									<i class="sub_panel_control">+</i> Hearings
								</td>
							</tr>
							<tr id="subAccordionOne_{{ $result->id }}_{{ $app_index }}" class="collapse">
								<td class="inner_table_container">
									<table class="table dt-responsive table-bordered custom_data_table" cellspacing="0" width="100%">
										<thead>
											<tr>
												<th>Date</th>
												<th>Reason</th>
												<th>Officer</th>
												<th>Court Room</th>
												<th>Court Name</th>
												<th>Court Phone</th>
												<th>Court Address</th>
												<th>Court Suburb</th>
												<th>Type</th>
												<th>List #</th>
												<th>Outcome</th>
												<th>Orders Filename</th>
											</tr>
										</thead>
										<tbody>
											@foreach ($application['hearings'] as $hearing_index => $hearing)
												 <tr>
													<td>{{ date('m/d/Y', strtotime($hearing['datetime'])) }}</td>
													<td class="hoverable" onclick="create_input('{{ $result->id }}_applications[{{ $app_index }}][hearings][{{ $hearing_index }}][reason]','{{ addslashes($hearing['reason']) }}',this,'text')">{{ $hearing['reason'] }}</td>
													<td class="hoverable" onclick="create_input('{{ $result->id }}_applications[{{ $app_index }}][hearings][{{ $hearing_index }}][officer]','{{ addslashes($hearing['officer']) }}',this,'text')">{{ $hearing['officer'] }}</td>
													<td class="hoverable" onclick="create_input('{{ $result->id }}_applications[{{ $app_index }}][hearings][{{ $hearing_index }}][court_room]','{{ addslashes($hearing['court_room']) }}',this,'text')">{{ $hearing['court_room'] }}</td>
													<td class="hoverable" onclick="create_input('{{ $result->id }}_applications[{{ $app_index }}][hearings][{{ $hearing_index }}][court_name]','{{ addslashes($hearing['court_name']) }}',this,'text')">{{ $hearing['court_name'] }}</td>
													<td class="hoverable" onclick="create_input('{{ $result->id }}_applications[{{ $app_index }}][hearings][{{ $hearing_index }}][court_phone]','{{ addslashes($hearing['court_phone']) }}',this,'text')">{{ $hearing['court_phone'] }}</td>
													<td class="hoverable" onclick="create_input('{{ $result->id }}_applications[{{ $app_index }}][hearings][{{ $hearing_index }}][court_address]','{{ addslashes($hearing['court_address']) }}',this,'text')">{{ $hearing['court_address'] }}</td>
													<td class="hoverable" onclick="create_input('{{ $result->id }}_applications[{{ $app_index }}][hearings][{{ $hearing_index }}][court_suburb]','{{ addslashes($hearing['court_suburb']) }}',this,'text')">{{ $hearing['court_suburb'] }}</td>
													<td class="hoverable" onclick="create_input('{{ $result->id }}_applications[{{ $app_index }}][hearings][{{ $hearing_index }}][type]','{{ addslashes($hearing['type']) }}',this,'text')">{{ $hearing['type'] }}</td>
													<td class="hoverable" onclick="create_input('{{ $result->id }}_applications[{{ $app_index }}][hearings][{{ $hearing_index }}][list_no]','{{ addslashes($hearing['list_no']) }}',this,'text')">{{ $hearing['list_no'] }}</td>
													<td class="hoverable" onclick="create_input('{{ $result->id }}_applications[{{ $app_index }}][hearings][{{ $hearing_index }}][outcome]','{{ addslashes($hearing['outcome']) }}',this,'text')">{{ $hearing['outcome'] }}</td>
													<td class="hoverable" onclick="create_input('{{ $result->id }}_applications[{{ $app_index }}][hearings][{{ $hearing_index }}][orders_filename]','{{ addslashes($hearing['orders_filename']) }}',this,'text')">{{ $hearing['orders_filename'] }}</td>
												</tr>
											@endforeach

											@if (!$application['hearings'])
												<tr>
													<td colspan="12"><b>No Results</b></td>
												</tr>
											@endif
										</tbody>
									</table>
								</td>
							</tr>
							<tr data-toggle="collapse" data-target="#subAccordionTwo_{{ $result->id }}_{{ $app_index }}" class="clickable">
								<td colspan="13" class="text-left sub_panel_title">
									<i class="sub_panel_control">+</i> Documents
								</td>
							</tr>
							<tr id="subAccordionTwo_{{ $result->id }}_{{ $app_index }}" class="collapse">
								<td class="inner_table_container">
									<table class="table dt-responsive table-bordered custom_data_table" cellspacing="0" width="100%">
										<thead>
											<tr>
												<th>Date</th>
												<th>Time</th>
												<th>Title</th>
												<th>Description</th>
												<th>Filed By</th>
											</tr>
										</thead>
										<tbody>
											@foreach ($application['documents'] as $doc_index => $document)
												 <tr>
													<td>{{ date('F d, Y', strtotime($document['datetime'])) }}</td>
													<td>{{ date('H:i:s', strtotime($document['datetime'])) }}</td>
													<td class="hoverable" onclick="create_input('{{ $result->id }}_applications[{{ $app_index }}][documents][{{ $doc_index }}][title]','{{ addslashes($document['title']) }}',this,'text')">{{ $document['title'] }}</td>
													<td class="hoverable" onclick="create_input('{{ $result->id }}_applications[{{ $app_index }}][documents][{{ $doc_index }}][description]','{{ addslashes($document['description']) }}',this,'text')">{{ $document['description'] }}</td>
													<td class="hoverable" onclick="create_input('{{ $result->id }}_applications[{{ $app_index }}][documents][{{ $doc_index }}][filed_by]','{{ addslashes($document['filed_by']) }}',this,'text')">{{ $document['filed_by'] }}</td>
												</tr>
											@endforeach

											@if (!$application['documents'])
												<tr>
													<td colspan="5"><b>No Results</b></td>
												</tr>
											@endif
										</tbody>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
				@endforeach
			</div>
		</td>
	</tr>
@endforeach

@if (!$results)
	<tr>
		<td colspan="9"><b>No results</b></td>
	</tr>
@endif

<tr>
	<td colspan="9" style="text-align:right">{!! $results->appends(['state' => $state])->render() !!}</td>
</tr>
<tr>
	<td colspan="8" style="text-align:left">
		<div class="btn-group btn-group-sm">
			<button type="button" class="btn btn-primary" id="select_all_states">
				<input type="radio" id="radio_approve" name="approve_or_reject" value="approve">
				<label for="radio_approve">&nbsp;APPROVE SELECTED</label>
			</button>
			<button type="button" class="btn btn-danger" id="clear_all_states">
				<input type="radio" id="radio_reject" name="approve_or_reject" value="reject">
				<label for="radio_reject">&nbsp;REJECT SELECTED</label>
			</button>
		</div>
	</td>
	<td style="text-align:right"><button type="button" onclick="submit_form()" class="btn btn-success submit-selected-btn btn-block">CONFIRM</button></td>
</tr>