@extends('layouts.client_new')

@section('content')
	<div class="container">
		<h2>{{ $watchlist->name }}</h2>
		<p>Manage the subscribers and parties related to {{ $watchlist->name }}.</p>

		<ul class="nav nav-tabs">
			<li><a href="/client/watchlists/manage/{{ $watchlist->id }}">Notifications <span class="badge">{{ count($watchlist->notifications) }}</span></a></li>
			<li class="active"><a href="/client/watchlists/companies/{{ $watchlist->id }}">Companies <span class="badge">{{ $watchlist->entities()->whereType('Company')->count() }}</span></a></li>
			<li><a href="/client/watchlists/individuals/{{ $watchlist->id }}">Individuals <span class="badge">{{ $watchlist->entities()->whereType('Individual')->count()  }}</span></a></li>
			<li><a href="/client/watchlists/subscribers/{{ $watchlist->id }}">Subscribers <span class="badge">{{ count($watchlist->subscribers) }}</span></a></li>
		</ul>
		<br>

		@if (count($errors) > 0)
			<div class="alert alert-danger alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				@if (isset($errors->default->toArray()[0]['value']))
					<strong>Warning!</strong> The following ABNs were not inserted. Please fix the items and upload the CSV again:<br>
					@foreach ($errors->default->toArray() as $errors)
						{{ $errors['value'] }}:
						@foreach ($errors['message']->toArray() as $messages)
							@foreach ($messages as $message)
								{{ $message }}
							@endforeach
						@endforeach
						<br>
					@endforeach
				@else
					<strong>Warning!</strong><br>
					@foreach ($errors->all() as $error)
						{{ $error }}<br>
					@endforeach
				@endif
			</div>
		@endif
		<p class="pull-right">
			<button type="button" onclick="add_entity();return false;" class="btn btn-primary btn-xs">Add New</button>
			<button type="button" class="btn btn-warning btn-xs import-entities-btn">Import CSV</button>
		</p>
		<br>

		<form id="delete_entities_form" action="/client/watchlists/delete-entities/{{ $watchlist->id }}" method="POST">
			{{ csrf_field() }}
			<table class="table table-bordered">
				<thead>
					<tr>
						<th style="text-align:center"><input type="checkbox" id="check_all_entities"></th>
						<th>Party Name</th>
						<th>ABN</th>
						<th>ACN</th>
						<th>Alerts</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					@if (count($watchlist->entities))
						@foreach ($entities as $entity)
							<tr>
								<td style="text-align:center"><input type="checkbox" name="checkbox_entities[]" value="{{ $entity->id }}"></td>
								<td>{{ $entity->party_name != '' ? $entity->party_name : ' - '}}</td>
								<td>{{ $entity->abn != NULL ? $entity->abn : '-'}}</td>
								<td>{{ $entity->acn != NULL ? $entity->acn : '-'}}</td>
								<td class="text-nowrap">
									@if ($entity->notifications()->count() > 0)
										<a href="#" onclick="show_alerts('{{ $entity->id }}');return false;">{{ $entity->notifications()->count() }} alerts</a>
									@else
										0 alerts
									@endif
								</td>
								<td class="text-center">
									<button type="button" onclick="delete_entity('{{ $entity->id }}');return false;" class="btn btn-default btn-xs">Delete</button>
								</td>
							</tr>
						@endforeach
					@else
						<tr>
							<td colspan="6" align="center">
								No entities for watchlist.
							</td>
						</tr>
					@endif
				</tbody>
			</table>
		</form>
		<p class="text-right">
			<button class="btn btn-info btn-xs delete-entities-btn">Delete Selected</button>
		</p>
	</div>
@endsection

@section('js')
	<script>
		function show_alerts(id)
		{
			$.ajax({
				url: '/client/watchlists/entity-notifications/'+id,
				type: "get",
				success: function(data){
					bootbox.alert(data);
				}
			});
		}

		$(document).ready(function(){
			$('.import-entities-btn').click(function(){
				var import_entities_modal = ''+
					'<form id="form_import_entities" action="/client/watchlists/import-entities/{{ $watchlist->id }}" method="POST" enctype="multipart/form-data" class="form-horizontal">'+
						'<p>'+
							'To download a template of the csv format, click the button below.<br><br>'+
							'<a href="/sample-import-entity-template.csv" target="_blank" class="btn btn-warning btn-sm">Download Import Entities Sample File</a>'+
						'</p>'+
						'<hr>'+
						'<input type="file" name="import_entities_file">'+
						'{{ csrf_field() }}'+
					'</form>';

				bootbox.dialog({
					title : 'Import Entities CSV Template to Watchlist',
					message : import_entities_modal,
					buttons : {
						cancel: {
							label: 'Cancel',
							className: 'btn-default'
						},
						success : {
							label : 'Continue',
							className : 'btn-primary',
							callback : function() {
								$('#form_import_entities').submit();
							}
						},
					},
				});
			});

			$('.delete-entities-btn').click(function(){
				bootbox.confirm({
					message: "Delete selected entities?",
					callback: function (result) {
						if (! result) {
							bootbox.hideAll();
							return false;
						}

						$('#delete_entities_form').submit();
					}
				});
			});

			$('#check_all_entities').click(function(){
				$("#delete_entities_form input:checkbox").prop('checked', $(this).prop("checked"));
			});
		});

		function delete_entity(entity_id)
		{
			var delete_entity_modal = ''+
				'<form action="/client/watchlists/delete-entity" method="POST" class="form-horizontal">'+
					'<div class="form-group">'+
						'<div class="col-md-12">Are you sure you want to delete this entity?</div>'+
						'<input type="hidden" value="'+entity_id+'" name="entity_id">'+
					'</div>'+
					'{{ csrf_field() }}'+
				'</form>';

			modalform.dialog({
				bootbox : {
					title : 'Delete Entity for {{ $watchlist->name }}',
					message : delete_entity_modal,
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

		function add_entity()
		{
			var add_entity_modal = ''+
				'<form action="/client/watchlists/add-company" method="POST" class="add-entity-form form-horizontal">'+
					'<p><small><i>Please choose the method you would like the receive matches on. ABN/ACN will result in the most accurate matches. If ABN/ACN is unknown, the ABR lookup will provide a list of suggestions based on a party search.</i></small></p>'+
					'<div class="form-group">'+
						'<label class="col-md-3 control-label party-name">Type</label>'+
						'<div class="col-md-3">'+
							'<label class="radio-inline"><input type="radio" name="type" value="abn_acn">ABN/ACN</label>'+
						'</div>'+
						'<div class="col-md-3">'+
							'<label class="radio-inline"><input type="radio" name="type" value="party_name">Party Name</label>'+
						'</div>'+
						'<div class="col-md-3">'+
							'<a href="/client/watchlists/add-entities-from-abr/{{ $watchlist->id }}" class="btn btn-default">ABR Lookup</a>'+
						'</div>'+
						'<input type="hidden" value="{{ $watchlist->id }}" name="watchlist_id">'+
					'</div>'+
					'<hr>'+
					'<div class="form-group party-name-div" style="display:none">'+
						'<label class="col-md-3 control-label party-name">Party Name</label>'+
						'<div class="col-md-8">'+
							'<input type="text" name="party_name" class="form-control">'+
						'</div>'+
					'</div>'+
					'<div class="form-group abn-acn-div" style="display:none">'+
						'<label class="col-md-3 control-label abn-acn">ABN/ACN</label>'+
						'<div class="col-md-8">'+
							'<input type="text" name="abn_acn" class="form-control">'+
						'</div>'+
					'</div>'+
					'{{ csrf_field() }}'+
				'</form>';

			bootbox.dialog({
				message : add_entity_modal,
				title : 'Add Entity to Watchlist',
				buttons : {
					danger : {
						label : 'Cancel',
						className : 'btn-default'
					},
					button : {
						label : 'Save',
						callback : function() {
							$('.add-entity-form').submit();
							return false;
						}
					}
				},
			});

			$('.add-entity-form input[type="radio"]').change(function(){
				if ($(this).val() == 'party_name') {
					$('.abn-acn-div').hide();
					$('.party-name-div').fadeIn();
				} else {
					$('.party-name-div').hide();
					$('.abn-acn-div').fadeIn();
				}
			});
		}
	</script>
@endsection
