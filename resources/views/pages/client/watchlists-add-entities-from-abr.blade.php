@extends('layouts.client_new', [
	'title' => 'Add Entities from ABR',
])

@section('content')
	<div class="container">
		<a href="/client/watchlists/manage/{{ $watchlist->id }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Back to watchlist</a>
		<h2>Add entities to {{ $watchlist->name }}</h2>
		<p>Add entities to your watchlist by searching the Australian Business Registry.</p>

		<form action="/client/watchlists/lookup-name" method="get" id="search-form">
			<div class="form-group">
				<div class="input-group">
					<span class="input-group-addon">Search:</span>
					<input type="text" name="search" value="{{ $request->search }}" class="form-control" placeholder="Enter trading name">
					<span class="input-group-btn">
						<button type="submit" class="btn btn-default">Search</button>
					</span>
				</div>
			</div>
		</form>
		<hr>
		<div class="results-section results-section-loading" style="display:none">
			<div class="text-center" style="padding:50px 0">
				<img src="/assets/client/js/plugins/justified-gallery/loading.gif">
			</div>
		</div>
		<div class="results-section results-section-table" style="display:none">
			<table class="table">
				<thead>
					<tr>
						<th>ABN</th>
						<th>Status</th>
						<th>Legal Name</th>
						<th>State</th>
						<th>Postcode</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
		<div class="results-section results-section-no-results" style="display:none">
			<p>There were no results for your search phrase.</p>
		</div>
		<div class="results-section results-section-error" style="display:none">
			<p>Sorry, something went wrong. Please try again later.</p>
		</div>
	</div>
@endsection

@section('js')
	<script>
		// Listen for form submit and make it an AJAX request instead
		$('#search-form').on('submit', function(event) {
			event.preventDefault();

			$('.results-section').hide();
			$('.results-section-loading').show();

			$.ajax({
				url: '/client/watchlists/lookup-name',
				method: 'get',
				data: $(this).serialize(),
				success: function(response) {
					if (!response.length) {
						$('.results-section').hide();
						$('.results-section-no-results').show();
						return;
					}

					$('tbody').empty();

					var button = $('<button type="button" class="btn btn-primary btn-add" />').text('Add to watchlist');

					response.forEach(function(result) {
						var name = '';

						if (result.legalName) {
							name = result.legalName.fullName;
						} else if (result.mainTradingName) {
							name = result.mainTradingName.organisationName;
						} else if (result.mainName) {
							name = result.mainName.organisationName;
						}

						var this_button = button.clone(true);
						this_button.data('name', name);
						this_button.data('abn', result.ABN.identifierValue);

						var tr = $('<tr/>');
						tr.append($('<td/>').text(result.ABN.identifierValue.replace(/(\d{2})(\d{3})(\d{3})(\d{3})/, '$1 $2 $3 $4')));
						tr.append($('<td/>').text(result.ABN.identifierStatus));
						tr.append($('<td/>').text(name));
						tr.append($('<td/>').text(result.mainBusinessPhysicalAddress.stateCode));
						tr.append($('<td/>').text(result.mainBusinessPhysicalAddress.postcode));
						tr.append($('<td/>').append(this_button));

						$('tbody').append(tr);
					});

					$('.results-section').hide();
					$('.results-section-table').show();
				},
				error: function() {
					$('.results-section').hide();
					$('.results-section-error').show();
				}
			});
		});

		// Handle clicking "Add to watchlist" buttons
		$(document).on('click', 'button.btn-add', function() {
			$(this).removeClass('btn-primary').addClass('btn-default').text('Adding...').prop('disabled', 'disabled');
			var button = this;

			$.ajax({
				url: '/client/watchlists/add-entity',
				method: 'post',
				data: {
					watchlist_id: {{ $watchlist->id }},
					abn_acn: $(this).data('abn'),
					party_name: $(this).data('name'),
					_token: '{{ csrf_token() }}'
				},
				success: function() {
					$(button).removeClass('btn-default').addClass('btn-success').html('<i class="fa fa-check"></i> Added');
				},
				error: function() {
					$(button).removeClass('btn-default').addClass('btn-danger').html('<i class="fa fa-times"></i> Error');
				}
			});
		});

		$('input[name="search"]').focus();

		@if ($request->search)
			$('#search-form').submit();
		@endif
	</script>
@endsection
