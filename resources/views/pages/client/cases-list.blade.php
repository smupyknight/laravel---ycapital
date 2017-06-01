@extends('layouts.client_new')

@section('content')
	<div class="container-fluid">
		<div class="loading" style="display: none;">Loading&#8230;</div>
		<div class="row">
			<!-- filter sidebar -->
			<div id="filter-sidebar" class="col-xs-6 col-sm-2 visible-sm visible-md visible-lg collapse sidebar">
				<form action="?" method="get" class="well">
					@if (count($filters))
						<div>
							<h4 data-toggle="collapse" data-target="#filter"><i class="fa fa-fw fa-caret-right parent-expanded"></i> Saved Filter</h4>
							<div id="filter" class="list-group collapse">
								@foreach ($filters as $filter)
									<a href="?{{ $filter->getQueryString() }}" class="list-group-item">{{ $filter->name }}</a>
								@endforeach
							</div>
						</div>
					@endif
					<div>
						<h4 data-toggle="collapse" data-target="#states"><i class="fa fa-fw fa-caret-{{ Request::has('state') ? 'down' : 'right' }} parent-expanded"></i> State</h4>
						<div id="states" class="list-group collapse{{ Request::has('state') ? ' in' : '' }}">
							@foreach ($states as $state)
								<label class="list-group-item list-checkbox-item">
									<input type="radio" name="state" {{ Request::get('state') == $state ? 'checked' : '' }} value="{{ $state }}"> {{ strtoupper($state) }}
									<i class="fa fa-check pull-right"></i>
								</label>
							@endforeach
						</div>
					</div>
					<div>
						<h4 data-toggle="collapse" data-target="#court_types"><i class="fa fa-fw fa-caret-{{ Request::has('court_types') ? 'down' : 'right' }} parent-expanded"></i> Court Type</h4>
						<div id="court_types" class="list-group collapse{{ Request::has('court_types') ? ' in' : '' }}">
							@foreach ($court_types as $court_type)
								<label class="list-group-item list-checkbox-item">
									<input type="checkbox" name="court_types[]" value="{{ $court_type }}"{{ in_array($court_type, $request->get('court_types', [])) ? ' checked' : '' }}> {{ $court_type }}
									<i class="fa fa-check pull-right"></i>
								</label>
							@endforeach
						</div>
					</div>
					<div>
						<h4 data-toggle="collapse" data-target="#notification_date"><i class="fa fa-fw fa-caret-{{ Request::has('notification_date') ? 'down' : 'right' }} parent-expanded"></i> Notification Date</h4>
						<div id="notification_date" class="list-group collapse{{ Request::has('notification_date') ? ' in' : '' }}">
							<label class="list-group-item list-checkbox-item">
								<input type="radio" name="notification_date" value="today"{{ $request->notification_date == 'today' ? ' checked' : '' }}> Today
								<i class="fa fa-check pull-right"></i>
							</label>
							<label class="list-group-item list-checkbox-item">
								<input type="radio" name="notification_date" value="l7d"{{ $request->notification_date == 'l7d' ? ' checked' : '' }}> Last 7 days
								<i class="fa fa-check pull-right"></i>
							</label>
							<label class="list-group-item list-checkbox-item">
								<input type="radio" name="notification_date" value="l30d"{{ $request->notification_date == 'l30d' ? ' checked' : '' }}> Last 30 days
								<i class="fa fa-check pull-right"></i>
							</label>
						</div>
					</div>
					<div>
						<h4 data-toggle="collapse" data-target="#case_type"><i class="fa fa-fw fa-caret-{{ Request::has('case_types') ? 'down' : 'right' }} parent-expanded"></i> Case Type</h4>
						<div id="case_type" class="collapse{{ Request::has('case_types') ? ' in' : '' }}">
							<select name="case_types[]" class="form-control" multiple="multiple" style="width: 100%">
								@foreach ($case_types as $type)
									<option value="{{ $type }}"{{ in_array($type, $request->get('case_types', [])) ? ' selected' : '' }}>{{ $type }}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div>
						<h4 data-toggle="collapse" data-target="#hearing_type"><i class="fa fa-fw fa-caret-{{ Request::has('hearing_types') ? 'down' : 'right' }} parent-expanded"></i> Hearing Type</h4>

						<div id="hearing_type" class="collapse{{ Request::has('hearing_types') ? ' in' : '' }}">
							<select name="hearing_types[]" class="form-control" multiple="multiple" style="width: 100%">
								@foreach ($hearing_types as $type)
									<option value="{{ $type }}"{{ in_array($type, $request->get('hearing_types', [])) ? ' selected' : '' }}>{{ $type }}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div>
						<h4 data-toggle="collapse" data-target="#hearing_date"><i class="fa fa-fw fa-caret-{{ Request::has('hearing_date') ? 'down' : 'right' }} parent-expanded"></i> Next Event</h4>
						<div id="hearing_date" class="list-group collapse{{ Request::has('hearing_date') ? ' in' : '' }}">
							<label class="list-group-item list-checkbox-item">
								<input type="radio" name="hearing_date" value="n7d"{{ $request->hearing_date == 'n7d' ? ' checked' : '' }}> Next 7 days
								<i class="fa fa-check pull-right"></i>
							</label>
							<label class="list-group-item list-checkbox-item">
								<input type="radio" name="hearing_date" value="n30d"{{ $request->hearing_date == 'n30d' ? ' checked' : '' }}> Next 30 days
								<i class="fa fa-check pull-right"></i>
							</label>
							<label class="list-group-item list-checkbox-item">
								<input type="radio" name="hearing_date" value="n90d"{{ $request->hearing_date == 'n90d' ? ' checked' : '' }}> Next 90 days
								<i class="fa fa-check pull-right"></i>
							</label>
						</div>
					</div>
					<div>
						<h4 data-toggle="collapse" data-target="#document_date"><i class="fa fa-fw fa-caret-{{ Request::has('document_date') ? 'down' : 'right' }} parent-expanded"></i> Document Date</h4>
						<div id="document_date" class="list-group collapse{{ Request::has('document_date') ? ' in' : '' }}">
							<label class="list-group-item list-checkbox-item">
								<input type="radio" name="document_date" value="l1d"{{ $request->document_date == 'l1d' ? ' checked' : '' }}> Yesterday
								<i class="fa fa-check pull-right"></i>
							</label>
							<label class="list-group-item list-checkbox-item">
								<input type="radio" name="document_date" value="l7d"{{ $request->document_date == 'l7d' ? ' checked' : '' }}> Last 7 days
								<i class="fa fa-check pull-right"></i>
							</label>
							<label class="list-group-item list-checkbox-item">
								<input type="radio" name="document_date" value="l30d"{{ $request->document_date == 'l30d' ? ' checked' : '' }}> Last 30 days
								<i class="fa fa-check pull-right"></i>
							</label>
						</div>
					</div>
					<div>
						<h4 data-toggle="collapse" data-target="#court_suburb"><i class="fa fa-fw fa-caret-{{ Request::has('court_suburbs') ? 'down' : 'right' }} parent-expanded"></i> Court Suburb</h4>
						<div id="court_suburb" class="collapse{{ Request::has('court_suburbs') ? ' in' : '' }}">
							<select name="court_suburbs[]" class="form-control" multiple="multiple" style="width: 100%">
								@foreach($court_suburbs as $suburb)
									<option value="{{ $suburb }}"{{ in_array($suburb, $request->get('court_suburbs', [])) ? ' selected' : '' }}>{{ $suburb }}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div>
						<h4 data-toggle="collapse" data-target="#party_representative"><i class="fa fa-fw fa-caret-{{ Request::has('party_representatives') ? 'down' : 'right' }} parent-expanded"></i> Party Representative</h4>
						<div id="party_representative" class="list-group collapse{{ Request::has('party_representatives') ? ' in' : '' }}">
							<select name="party_representatives[]" class="form-control" multiple="multiple" style="width: 100%">
								@foreach($party_representatives as $representative)
									<option value="{{ $representative }}"{{ in_array($representative, $request->get('party_representatives', [])) ? ' selected' : '' }}>{{ $representative }}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div>
						<hr>
						<a href="/client/cases" class="btn btn-sm btn-default">Reset</a>
						<a href="/clients/export-csv?{{ http_build_query(Request::all()) }}" target="_blank" class="btn btn-sm btn-default" >Export</a>
						<button type="button" class="btn btn-sm btn-default btn-save-filter">Save</button>
						<button type="submit" class="btn btn-sm btn-primary search-btn"><i class="fa fa-search"></i> Search</button>
					</div>
					<input type="hidden" name="per_page" value="{{ $cases->perPage() }}">
				</form>
			</div>
			<div class="cases-list">
				<div class="col-sm-10">
					<div class="form-inline">
						<div class="form-group">
							<label>Total results:</label>
							<p class="form-control-static">{{ number_format($cases->total()) }}</p>
						</div>
						<div class="form-group pull-right">
							<label>Rows per page:</label>
							<select name="per_page" class="form-control">
								<option value="20"{{ $cases->perPage() == 20 ? ' selected' : '' }}>20</option>
								<option value="50"{{ $cases->perPage() == 50 ? ' selected' : '' }}>50</option>
								<option value="100"{{ $cases->perPage() == 100 ? ' selected' : '' }}>100</option>
							</select>
						</div>
					</div>
					<br>

					<table class="table table-responsive table-bordered table-hover cases-table" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th></th>
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
								@foreach($case->applications as $application)
									<tr data-toggle="collapse" data-target="#accordion_{{ $case->id }}" class="accordion-toggle">
										<td class="text-center icon"><i class="fa fa-plus"></i></td>
										<td>{{ $case->notification_time ? (new \DateTime($case->notification_time))->setTimezone(new \DateTimeZone($case->timezone))->format('F d, Y') : '' }}</td>
										<td>{{ $case->state }} {{ $case->court_type }}</td>
										<td>{{ $case->suburb }}</td>
										<td>{{ $case->jurisdiction }}</td>
										<td><a href="/client/cases/view/{{ $case->id }}" class="view-case-link" target="_blank">{{ $case->case_name }}</a></td>
										<td>{{ $case->case_type }}</td>
										<td>{{ $case->getNextHearingDate() ? (new \Carbon\Carbon($case->getNextHearingDate()))->setTimezone($case->timezone)->format('F d, Y') : '' }}</td>
										<td>{{ $case->case_no }}</td>
									</tr>
									<tr id="accordion_{{ $case->id }}" class="collapse">
										<td colspan="12" style="padding:0">
											<div class="boxed-row">
												<button style="display:none" type="button" class="btn btn-sm btn-primary pull-right expand-applications-btn">Expand All</button>
												<table class="table cases-inner-table" cellspacing="0" width="100%">
													<tbody>
														<tr>
															<td colspan="13" class="header-row">Parties</td>
														</tr>
														<tr>
															<td class="inner_table_container">
																@if ($application->parties)
																	<table class="table text-center" cellspacing="0" width="100%">
																		<thead>
																			<tr>
																				<th>Name</th>
																				<th>Role</th>
																				<th>Representative</th>
																				<th>Address</th>
																				<th>Phone</th>
																				<th>ABN/ACN</th>
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
																					<td>{{ $party->abn != '' ? $party->abn : $party->acn }}</td>
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
															</td>
														</tr>
														<tr>
															<td colspan="13" class="text-left header-row">Hearings</td>
														</tr>
														<tr>
															<td>
																@if ($application->hearings)
																	<table class="table text-center" cellspacing="0" width="100%">
																		<thead>
																			<tr>
																				<th>Next Event</th>
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
																					<td>{{ (new \DateTime($hearing->datetime))->setTimezone(new \DateTimeZone($case->timezone))->format('F d, Y') }} {{ (new \DateTime($hearing->datetime))->setTimezone(new \DateTimeZone($case->timezone))->format('H:i:s') != '00:00:00' ? (new \DateTime($hearing->datetime))->setTimezone(new \DateTimeZone($case->timezone))->format('H:i:s') : '' }}</td>
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
															</td>
														</tr>
														<tr>
															<td colspan="13" class="text-left header-row">Documents</td>
														</tr>
														<tr>
															<td>
																@if ($application->documents)
																	<table class="table text-center" cellspacing="0" width="100%">
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
																					<td>{{ (new \DateTime($document->datetime))->setTimezone(new \DateTimeZone($case->timezone))->format('F d, Y') }}</td>
																					<td>{{ (new \DateTime($document->datetime))->setTimezone(new \DateTimeZone($case->timezone))->format('H:i:s') != '00:00:00' ? (new \DateTime($document->datetime))->setTimezone(new \DateTimeZone($case->timezone))->format('H:i:s') : '' }}</td>
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
															</td>
														</tr>
														<tr>
															<td colspan="13" class="end-of-record" onclick="$(this).closest('.collapse').collapse('hide');">
																<i class="fa fa-minus"></i> <b>END OF RECORD</b>
															</td>
														</tr>
													</tbody>
												</table>
											</div>
										</td>
									</tr>
								@endforeach
							@endforeach
						</tbody>
					</table>
					<div class="text-center">
						{!! $cases->appends(Request::all())->render() !!}
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('css')
	<style type="text/css">
		.list-checkbox-item {
			font-weight: normal;
		}
		.list-checkbox-item input {
			display: none;
		}
		.list-checkbox-item .fa-check {
			display: none;
		}
		.list-checkbox-item input:checked + .fa-check {
			display: inline-block;
		}
	</style>
@endsection

@section('js')
	<script src="/assets/client_new/js/select2.full.js"></script>
	<script src="/assets/client_new/js/modalform.js"></script>
	<script>
		$('[name="case_types[]"]').select2();
		$('[name="hearing_types[]"]').select2();
		$('[name="court_suburbs[]"]').select2();
		$('[name="party_representatives[]"]').select2();
		$( document ).ready(function() {
			$('#filter-sidebar [data-toggle="collapse"]').on("click", function(event) {
				$(this).find('i').toggleClass('fa-caret-right').toggleClass('fa-caret-down');
			})
		});

		$('.btn-save-filter').on('click', function() {
			var html = ''+
				'<form action="/client/filters/create" method="post">'+
					@if (count($filters))
						'<div class="form-group">'+
							'<select name="filter_id" class="form-control">'+
								'<option value="">Update existing filter</option>'+
								@foreach ($filters as $filter)
									'<option value="{{ $filter->id }}">{{ addslashes($filter->name) }}</option>'+
								@endforeach
							'</select>'+
						'</div>'+
						'<p>Or create a new filter:</p>'+
					@endif
					'<div class="form-group">'+
						'<input type="text" name="name" class="form-control" placeholder="Give your filter a name">'+
					'</div>'+
					'<input type="hidden" name="fields">'+
					'{{ csrf_field() }}'+
				'</form>';

			modalform.dialog({
				bootbox: {
					title: 'Save Filter',
					message: html,
					buttons: {
						cancel: {
							label: 'Cancel',
							className: 'btn-default'
						},
						submit: {
							label: 'Save Filter',
							className: 'btn-primary'
						}
					}
				}
			});

			$('.modal [name="fields"]').val($('#filter-sidebar form').serialize());

			$('.modal [name="filter_id"]').on('change', function() {
				$('.modal form').attr('action', '/client/filters/' + ($(this).val() ? 'edit' : 'create'));
			});
		});

		$('select[name="per_page"]').on('change', function() {
			$('form input[name="per_page"]').val($(this).val());
			$('form input[name="per_page"]').closest('form').submit();
		});

		$(document).on('click', '.btn-add-to-watchlist', function(event) {
			add_to_watchlist(event);
		});

		/**
		 * Pseudo code for how this modal works:
		 *   If party has an ABN:
		 *       User chooses existing watchlist or create new
		 *       Modal posts to add-entity-from-case-list
		 *   Else (party has no ABN):
		 *       User chooses existing watchlist or create new
		 *       If the user chose an existing watchlist:
		 *           Replace listener on submit button with one that redirects
		 *           to add-entities-from-abr/{watchlist_id}
		 *       Else (user is creating new watchlist):
		 *           Post to /client/watchlists/add
		 *           Read watchlist ID from response
		 *           Redirect to add-entities-from-abr/{watchlist_id}
		 *       End if
		 *   End if
		 */
		function add_to_watchlist(event)
		{
			var form = ''+
				'<form action="/client/watchlists/foo" method="post" class="form-horizontal">'+
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
					// If the party has no ABN, the user has just created a new
					// watchlist and they need to be redirected to the ABR page
					// to add entities.
					if (!$(event.target).data('abn')) {
						document.location = '/client/watchlists/add-entities-from-abr/' + data.watchlist_id + '?search=' + $(event.target).data('name');
						return;
					}

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

			if ($(event.target).data('abn')) {
				$('.modal form').attr('action', '/client/watchlists/add-entity-from-case-list');
			} else {
				$('.modal form').attr('action', '/client/watchlists/add');
			}

			// When the watchlist dropdown is changed
			$('.modal [name="watchlist_id"]').on('change', function() {
				var watchlist = this;
				// Show or hide the name field if needed
				if ($(watchlist).val() == 'new') {
					$('.modal [name="name"]').closest('.form-group').show();
					$('.modal [name="name"]').focus();
				} else {
					$('.modal [name="name"]').closest('.form-group').hide();
				}

				// If the party has no ABN
				if (!$(event.target).data('abn')) {
					// If selecting an existing watchlist, change the submit
					// behaviour so it redirects to the ABR page instead
					$('.modal form').off('submit');

					if ($(watchlist).val() == 'new') {
						$('.modal form').on('submit', modalform.onsubmit);
					} else {
						$('.modal form').on('submit', function (event) {
							event.preventDefault();
							document.location = '/client/watchlists/add-entities-from-abr/' + $(watchlist).val() + '?search=' + $('.modal [name="party_name"]').val();
						});
					}
				}
			}).trigger('change');

			$('.modal [name="party_name"]').val($(event.target).data('name'));
			$('.modal [name="abn_acn"]').val($(event.target).data('abn'));

			// Populate watchlist dropdown
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
		}

		$("tr[id^='accordion_']").on('show.bs.collapse', function () {
			$("tr[id^='accordion_']").collapse('hide');
			$('table > tbody > .accordion-toggle').not('[data-target="#' + $(this).attr('id') + '"]').addClass('darken');
			$(this).prev().find('.fa').removeClass("fa-plus").addClass("fa-minus");
		});

		$("tr[id^='accordion_']").on('hide.bs.collapse', function () {
			$('table > tbody > .accordion-toggle').removeClass('darken');
			$(this).prev().find('.fa').removeClass("fa-minus").addClass("fa-plus");
		});

		$(function () {
			$("tr[id^='accordion_']").on('shown.bs.collapse', function () {
				var offset = $(this).prev().offset();

				$('html,body').animate({
					scrollTop: offset.top - $('.navbar-fixed-top').height()
				}, 500);
			});
		});

		// Prevent accordion from opening while browsing away
		$('.view-case-link').on('click', function(event) {
			event.stopPropagation();
		});

		// When notification date filter is changed, unset next event filter and
		// vice versa. Only one of these filters can be used at a time.
		$('[name="notification_date"]').on('click', function() {
			$('[name="hearing_date"]').removeAttr('checked');
		});

		$('[name="hearing_date"]').on('click', function() {
			$('[name="notification_date"]').removeAttr('checked');
		});
	</script>
@endsection
