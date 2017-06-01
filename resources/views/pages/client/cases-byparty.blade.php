@extends('layouts.client_new')

@section('content')
	<div class="container">
		<h1>Party Search</h1>

		<form action="?" method="get" class="well form-inline">
			<div class="form-group">
				<label>Search</label>
				<input type="text" name="search" value="{{ Request::get('search') }}"class="form-control">
			</div>

			<div class="form-group">
				<label>State</label>
				<select name="state" class="form-control">
					@foreach ($states as $state)
						<option value="{{ $state }}"{{ Request::get('state') == $state ? ' selected' : '' }}>{{ strtoupper($state) }}</option>
					@endforeach
				</select>
			</div>

			<div class="form-group">
				<label>Court Type</label>
				<select name="court_type" class="form-control">
					<option value="">Any</option>
					@foreach ($court_types as $type)
						<option value="{{ $type }}"{{ Request::get('court_type') == $type ? ' selected' : '' }}>{{ $type }}</option>
					@endforeach
				</select>
			</div>

			<button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-search"></i> Search</button>
		</form>

		<p><strong>Total results:</strong> {{ number_format($cases->total()) }}</p>

		@if ($cases->total())
			<table class="table table-responsive table-bordered table-hover cases-table" cellspacing="0" width="100%">
				<thead>
					<tr>
						<th>Notification Date</th>
						<th>Court</th>
						<th>Court Suburb</th>
						<th>Jurisdiction</th>
						<th>Case Name</th>
						<th>Case Type</th>
						<th>Next Event</th>
						<th>Case ID</th>
					</tr>
				</thead>
				<tbody>
					@foreach($cases as $case)
						<tr>
							<td>{{ $case->notification_time ? (new \DateTime($case->notification_time))->setTimezone(new \DateTimeZone($case->timezone))->format('F d, Y') : '' }}</td>
							<td>{{ $case->state }} {{ $case->court_type }}</td>
							<td>{{ $case->suburb }}</td>
							<td>{{ $case->jurisdiction }}</td>
							<td><a href="/client/cases/view/{{ $case->id }}" class="view-case-link" target="_blank">{{ $case->case_name }}</a></td>
							<td>{{ $case->case_type }}</td>
							<td>{{ $case->getNextHearingDate() ? (new \Carbon\Carbon($case->getNextHearingDate()))->setTimezone($case->timezone)->format('F d, Y') : '' }}</td>
							<td>{{ $case->case_no }}</td>
						</tr>
					@endforeach
				</tbody>
			</table>

			<div class="text-center">
				{!! $cases->appends(Request::all())->render() !!}
			</div>
		@endif
	</div>
@endsection
