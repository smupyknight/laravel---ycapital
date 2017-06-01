@extends('layouts.client_new')

@section('content')
	<div class="container">
		<h2>{{ $watchlist->name }}</h2>
		<p>Manage the subscribers and parties related to {{ $watchlist->name }}.</p>

		<ul class="nav nav-tabs">
			<li class="active"><a href="/client/watchlists/manage/{{ $watchlist->id }}">Notifications <span class="badge">{{ count($watchlist->notifications) }}</span></a></li>
			<li><a href="/client/watchlists/companies/{{ $watchlist->id }}">Companies <span class="badge">{{ $watchlist->entities()->whereType('Company')->count() }}</span></a></li>
			<li><a href="/client/watchlists/individuals/{{ $watchlist->id }}">Individuals <span class="badge">{{ $watchlist->entities()->whereType('Individual')->count() }}</span></a></li>
			<li><a href="/client/watchlists/subscribers/{{ $watchlist->id }}">Subscribers <span class="badge">{{ count($watchlist->subscribers) }}</span></a></li>
		</ul>
		<br>

		@if (count($errors) > 0)
			<div class="alert alert-danger alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<strong>Warning!</strong><br>
				@foreach ($errors->all() as $error)
					{{ $error }}<br>
				@endforeach
			</div>
		@endif

		<table class="table table-bordered">
			<thead>
				<tr>
					<th>Date/Time</th>
					<th>Match On</th>
					<th>Match Type</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody id="notification_body">
				@foreach ($notifications as $notification)
					<tr>
						<td class="text-nowrap">{{ $notification->created_at->setTimezone(Auth::user()->timezone)->format('j/m/Y g:i A') }} ({{ $notification->created_at->diffForHumans() }})</td>
						<td>
							@if ($notification->entity->party_name)
								{{ $notification->entity->party_name.' ' }}
							@endif

							@if ($notification->entity->abn)
								{{ $notification->entity->abn.' ' }}
							@endif

							@if ($notification->entity->acn)
								{{ $notification->entity->acn.' ' }}
							@endif
						</td>
						<td>{{ ucfirst($notification->match_type) }}</td>
						<td><a target="_blank" href="/client/cases/view/{{ $notification->case_id }}"><button class="btn btn-xs btn-primary">View Case</button></a></td>
					</tr>
				@endforeach
			</tbody>
		</table>
		{!! $notifications->render() !!}
	</div>
@endsection
