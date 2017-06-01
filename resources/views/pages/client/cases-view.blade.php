@extends('layouts.client_new')

@section('content')

	<div class="container" style="margin-top:50px;padding-bottom:50px">
		<h4>
			{{ $case->case_name }}
			<a href="#" onclick="window.history.back();"><button class="btn btn-xs btn-default pull-right" style="margin-right:10px">Back to previous page <i class="fa fa-undo" aria-hidden="true"></i></button></a>
		</h4>
		<hr>

		<div class="row">
			<div class="col-lg-3">
				<p><b>Court:</b> {{ $case->state }} {{ $case->court_type }}</p>
				<p><b>Court Suburb:</b> {{ $case->suburb }}</p>
			</div>
			<div class="col-lg-3">
				<p><b>Jurisdiction:</b> {{ $case->jurisdiction }}</p>
				<p><b>Case Type:</b> {{ $case->case_type }}</p>
			</div>
			<div class="col-lg-3">
				<p><b>Next Event:</b> {{ $case->getNextHearingDate() ? (new \DateTime($case->getNextHearingDate()))->setTimezone(new \DateTimeZone($case->getTimezone()))->format('F d, Y') : 'No future hearing date available'}}</p>
				<p><b>Case ID:</b> {{ $case->case_no }}</p>
			</div>
			<div class="col-lg-3">
				<p><b>Notification Date:</b> {{ $case->notification_time ? (new \DateTime($case->notification_time))->setTimezone(new \DateTimeZone($case->getTimezone()))->format('F d, Y') :'' }}</p>
			</div>
		</div>

		<hr>

		@if ($case->applications->count() > 1)
			<ul class="nav nav-tabs" role="tablist">
				@foreach ($case->applications as $key => $application)
					<li role="presentation" class="{{ $key == 0 ? "active" : '' }}"><a href="#{{ $application->id }}" aria-controls="{{ $application->id }}" role="tab" data-toggle="tab">{{ $application->title ? $application->title : 'No Title' }}</a></li>
				@endforeach
			</ul>
		@endif

		<div class="tab-content">
			@foreach ($case->applications as $key => $application)
				<div role="tabpanel" class="tab-pane {{ $key == 0 ? "active" : '' }}" id="{{ $application->id }}">

					<h4>Parties</h4>

					@if ($application->parties->count())
						<table class="table table-bordered">
							<thead>
								<tr>
									<th>Name</th>
									<th>Role</th>
									<th>Representative</th>
									<th>Address</th>
									<th>Phone</th>
									<th>ABN</th>
									<th>ACN</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>
								@foreach ($application->parties as $party)
									<tr>
										<td>{{ $party->name }}</td>
										<td>{{ $party->role }}</td>
										<td>{{ $party->rep_name }}</td>
										<td>{{ $party->address }}</td>
										<td>{{ $party->phone }}</td>
										<td>{{ $party->abn }}</td>
										<td>{{ $party->acn }}</td>
										<td>
											@if ($party->role != 'Docket')
												<button type="button" class="btn btn-xs btn-add-to-watchlist" data-name="{{ $party->name }}" data-abn="{{ $party->abn }}">Add to watchlist</button>
											@endif
										</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					@else
						<p>No results.</p>
					@endif

					<h4>Hearings</h4>

					@if ($application->hearings->count())
						<table class="table table-bordered">
							<thead>
								<tr>
									<th>Date / Time</th>
									<th>Reason</th>
									<th>Officer</th>
									<th>Court Room</th>
									<th>Court Location</th>
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
								@foreach ($application->hearings as $hearing)
									<tr>
										<td>{{ $hearing->datetime ? (new \Carbon\Carbon($hearing->datetime))->setTimezone($case->getTimezone())->format('h:i A - F d, Y') : '' }}</td>
										<td>{{ $hearing->reason }}</td>
										<td>{{ $hearing->officer }}</td>
										<td>{{ $hearing->court_room }}</td>
										<td>{{ $hearing->court_name }}</td>
										<td>{{ $hearing->court_phone }}</td>
										<td>{{ $hearing->court_address }}</td>
										<td>{{ $hearing->court_suburb }}</td>
										<td>{{ $hearing->type }}</td>
										<td>{{ $hearing->list_no }}</td>
										<td>{{ $hearing->outcome }}</td>
										<td>{{ $hearing->orders_filename }}</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					@else
						<p>No results.</p>
					@endif

					<h4>Documents</h4>

					@if ($application->documents->count())
						<table class="table table-bordered">
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
								@foreach ($application->documents as $document)
									<tr>
										<td>{{ (new \DateTime($document->datetime))->setTimezone(new \DateTimeZone($case->getTimezone()))->format('F d, Y') }}</td>
										<td>{{ (new \DateTime($document->datetime))->setTimezone(new \DateTimeZone($case->getTimezone()))->format('H:i:s') != '00:00:00' ? (new \DateTime($document->datetime))->setTimezone(new \DateTimeZone($case->getTimezone()))->format('H:i:s') : ''  }}</td>
										<td>{{ $document->title }}</td>
										<td>{{ $document->description }}</td>
										<td>{{ $document->filed_by }}</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					@else
						<p>No results.</p>
					@endif

					@if ($case->updates->count() && Auth::user()->isAdmin())
						<h4>Updates</h4>

						<table class="table table-bordered">
							<thead>
								<tr>
									<th>Time</th>
									<th>Action</th>
									<th>Changes</th>
								</tr>
							</thead>
							<tbody>
								@foreach ($case->updates as $update)
									<tr>
										<td class="text-nowrap">{{ $update->created_at->setTimezone(Auth::user()->timezone)->format('j F Y, g:ia') }}</td>
										<td class="text-nowrap">
											{{ ucfirst($update->entity_type) }}

											@if ($update->action_type == 'create')
												created
											@elseif ($update->action_type == 'edit')
												edited
											@else
												deleted
											@endif
										</td>
										<td>
											@if ($update->action_type == 'create')
												@foreach ($update->fields as $field)
													<strong>{{ ucfirst(str_replace('_', ' ', $field->name)) }}</strong>
													set to
													{!! $field->new_value ? e($field->new_value) : '<em class="text-muted">empty</em>' !!}
													<br>
												@endforeach
											@elseif ($update->action_type == 'edit')
												@foreach ($update->fields as $field)
													<strong>{{ ucfirst(str_replace('_', ' ', $field->name)) }}</strong>
													changed from
													{!! $field->old_value ? e($field->old_value) : '<em class="text-muted">empty</em>' !!}
													to
													{!! $field->new_value ? e($field->new_value) : '<em class="text-muted">empty</em>' !!}
													<br>
												@endforeach
											@endif
										</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					@endif
				</div>
			@endforeach

		</div>
	</div>
@endsection

@section('js')
	<script type="text/javascript">
	$('.btn-add-to-watchlist').on('click', function(event) {
		var form = ''+
			'<form action="/client/watchlists/add-entity-from-case-list" method="post" class="form-horizontal">'+
				'<div class="form-group">'+
					'<label class="col-md-4 control-label">Watchlist</label>'+
					'<div class="col-md-8">'+
						'<select name="watchlist_id" class="form-control">'+
							'<option value="">Please select</option>'+
							'<option value="new">Create new...</option>'+
						'</select>'+
					'</div>'+
				'</div>'+
				'<div class="form-group">'+
					'<label class="col-md-4 control-label">Watchlist Name</label>'+
					'<div class="col-md-8">'+
						'<input type="text" name="name" value="" class="form-control">'+
					'</div>'+
				'</div>'+
				'<input type="hidden" name="party_name">'+
				'<input type="hidden" name="abn_acn">'+
				'{{ csrf_field() }}'+
			'</form>';

		modalform.dialog({
			bootbox: {
				title: 'Add Party to Watchlist',
				message: form,
				buttons: {
					cancel: {
						label: 'Cancel',
						className: 'btn-default'
					},
					submit: {
						label: 'Watch',
						className: 'btn-primary'
					}
				}
			},
			success: function(data, status, jqxhr) {
				if ($('.modal form select[name="watchlist_id"]').val() == 'new') {
					var message = 'The watchlist has been created and the party has been added to it successfully.';
				} else {
					var message = 'The party has been added to the watchlist successfully.';
				}

				bootbox.hideAll();

				bootbox.dialog({
					title: 'Success',
					message: message,
					buttons: {
						ok: {
							label: 'OK',
							className: 'btn-primary',
							success: function() {
								bootbox.hideAll();
							}
						}
					}
				});
			}
		});

		$('.modal [name="watchlist_id"]').on('change', function() {
			if ($(this).val() == 'new') {
				$('.modal [name="name"]').closest('.form-group').show();
				$('.modal [name="name"]').focus();
			} else {
				$('.modal [name="name"]').closest('.form-group').hide();
			}
		}).trigger('change');

		$('.modal [name="party_name"]').val($(event.target).data('name'));
		$('.modal [name="abn_acn"]').val($(event.target).data('abn'));

		$.ajax({
			url: '/client/watchlists/list',
			method: 'get',
			success: function(response) {
				var select = $('.modal [name="watchlist_id"]');

				$.each(response, function(index, watchlist) {
					var option = $('<option/>').val(watchlist.id).text(watchlist.name);
					select.append(option);
				});
			}
		});
	});
	</script>
@endsection
