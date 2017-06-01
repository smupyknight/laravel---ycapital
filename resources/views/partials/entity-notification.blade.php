<p>Party Name : {{ $entity->party_name }}</p>
<p>ABN/ACN : {{ $entity->abn }}</p>
<hr>
<table class="table">
	<thead>
		<tr>
			<th>Case ID</th>
			<th>Case Name</th>
			<th>Match Type</th>
		</tr>
	</thead>
	<tbody>
		@foreach ($notifications as $notification)
		<tr>
			<td><a href="/client/cases/view/{{ $notification->case_id }}" target="_blank">{{ $notification->case_id }}</a></td>
			<td><a href="/client/cases/view/{{ $notification->case_id }}" target="_blank">{{ $notification->court_case->case_name }}</a></td>
			<td>{{ ucwords($notification->match_type) }}</td>
		</tr>
	</tbody>
	@endforeach
</table>
