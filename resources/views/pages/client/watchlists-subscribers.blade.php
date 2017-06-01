@extends('layouts.client_new')

@section('content')
	<div class="container">
		<h2>{{ $watchlist->name }}</h2>
		<p>Manage the subscribers and parties related to {{ $watchlist->name }}.</p>

		<ul class="nav nav-tabs">
			<li><a href="/client/watchlists/manage/{{ $watchlist->id }}">Notifications <span class="badge">{{ count($watchlist->notifications) }}</span></a></li>
			<li><a href="/client/watchlists/companies/{{ $watchlist->id }}">Companies <span class="badge">{{ $watchlist->entities()->whereType('Company')->count() }}</span></a></li>
			<li class="active"><a href="/client/watchlists/individuals/{{ $watchlist->id }}">Individuals <span class="badge">{{ $watchlist->entities()->whereType('Individual')->count() }}</span></a></li>
			<li class="active"><a href="/client/watchlists/subscribers/{{ $watchlist->id }}">Subscribers <span class="badge">{{ count($watchlist->subscribers) }}</span></a></li>
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
		<p class="pull-right">
			<button type="button" class="btn btn-primary btn-xs pull-right add-subscriber-btn">Add Subscriber</button>
		</p>
		<br>

		<table class="table table-bordered">
			<thead>
				<tr>
					<th>Name</th>
					<th>Email</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				@if (count($watchlist->subscribers))
					@foreach ($watchlist->subscribers as $subscriber)
						<tr>
							<td>{{ $subscriber->name }}</td>
							<td>{{ $subscriber->email }}</td>
							<td><button type="button" onclick="delete_subscriber('{{ $subscriber->id }}');return false;" class="btn btn-default btn-xs">Delete</button></td>
						</tr>
					@endforeach
				@else
					<tr>
						<td colspan="3" align="center">
							No subscribers for watchlist.
						</td>
					</tr>
				@endif
			</tbody>
		</table>
	</div>
@endsection

@section('js')
	<script>
		$(document).ready(function(){
			$('.add-subscriber-btn').click(function(){
				var add_subscriber_modal = ''+
				'<form action="/client/watchlists/add-subscriber" method="POST" class="form-horizontal">'+
					'<div class="form-group">'+
						'<label class="col-md-3 control-label">Name</label>'+
						'<div class="col-md-8">'+
							'<input type="text" id="add_subscriber_name" name="name" class="form-control">'+
						'</div>'+
					'</div>'+
					'<div class="form-group">'+
						'<label class="col-md-3 control-label">Email</label>'+
						'<div class="col-md-8">'+
							'<input type="text" id="add_subscriber_email" name="email" class="form-control">'+
						'</div>'+
					'</div>'+
					'<input type="hidden" name="watchlist_id" value="{{ $watchlist->id }}">'+
					'{{ csrf_field() }}'+
				'</form>';

				modalform.dialog({
					bootbox : {
						title : 'Add Subscriber to Watchlist',
						message : add_subscriber_modal,
						buttons : {
							cancel: {
								label: 'Cancel',
								className: 'btn-default'
							},
							submit: {
								label: 'Save',
								className: 'btn-primary'
							},
						}
					},
					autofocus : false,
					after_init : function() {
						var subscriber_names = new Bloodhound({
							datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
							queryTokenizer: Bloodhound.tokenizers.whitespace,
							remote: {
								url: '/client/watchlists/subscriber-name-list/%QUERY?watchlist_id={{ $watchlist->id }}',
								wildcard: '%QUERY'
							}
						});

						var subscriber_emails = new Bloodhound({
							datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
							queryTokenizer: Bloodhound.tokenizers.whitespace,
							remote: {
								url: '/client/watchlists/subscriber-email-list/%QUERY?watchlist_id={{ $watchlist->id }}',
								wildcard: '%QUERY'
							}
						});

						$('#add_subscriber_name').typeahead(null,{
							name : 'name',
							display : 'name',
							source : subscriber_names,
							templates: {
								empty: '<div class="empty-message">Unable to find any results that match the current query</div>',
								suggestion: Handlebars.compile('<div><strong>@{{name}}</strong> - @{{email}}</div>')
							}
						}).on('typeahead:select', function(event, suggestion) {
							$('#add_subscriber_email').val(suggestion.email);
						});

						$('#add_subscriber_email').typeahead(null,{
							name : 'email',
							display : 'email',
							source : subscriber_emails,
							templates: {
								empty: '<div class="empty-message">Unable to find any results that match the current query</div>',
								suggestion: Handlebars.compile('<div>@{{name}} - <strong>@{{email}}</strong></div>')
							}
						}).on('typeahead:select', function(event, suggestion) {
							$('#add_subscriber_name').val(suggestion.name);;
						});
					}
				});
			});
		});

		function delete_subscriber(subscriber_id)
		{
			var delete_subscriber_modal = ''+
				'<form action="/client/watchlists/delete-subscriber" method="POST" class="form-horizontal">'+
					'<div class="form-group">'+
						'<div class="col-md-12">Are you sure you want to delete this subscriber?</div>'+
						'<input type="hidden" value="'+subscriber_id+'" name="subscriber_id"'+
					'</div>'+
					'{{ csrf_field() }}'+
				'</form>';

			modalform.dialog({
				bootbox : {
					title : 'Delete Subscriber for {{ $watchlist->name }}',
					message : delete_subscriber_modal,
					buttons : {
						cancel: {
							label: 'Cancel',
							className: 'btn-default'
						},
						submit: {
							label: 'Delete',
							className: 'btn-primary'
						},
					}
				},
			});
		}
	</script>
@endsection
