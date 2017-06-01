<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
</head>
<body style="padding:30px">

	<div class="row">
		<div class="col-md-6"><a href="?id={{ $result_id - 1 }}">&laquo; Prev</a></div>
		<div class="col-md-6 text-right"><a href="?id={{ $result_id + 1 }}">Next &raquo;</a></div>
	</div>

	<br>

	<table class="table table-bordered">
		<tr>
			@foreach (get_object_vars($record) as $prop => $value)
				@if ($prop != 'applications')
					<th>{{ $prop }}</th>
				@endif
			@endforeach
		</tr>
		<tr>
			@foreach (get_object_vars($record) as $prop => $value)
				@if ($prop != 'applications')
					<td>{!! nl2br(e($value)) !!}</td>
				@endif
			@endforeach
		</tr>
	</table>

	<h2>applications</h2>

	<table class="table table-bordered">
		<tr>
			<th></th>
			<?php $num_cols = 1; ?>
			@foreach (get_object_vars($record->applications[0]) as $prop => $value)
				@if (!is_array($value))
					<?php $num_cols++; ?>
					<th>{{ $prop }}</th>
				@endif
			@endforeach
		</tr>
		@foreach ($record->applications as $app_index => $app)
			<tr>
				<td><a class="btn btn-default btn-xs" data-toggle="collapse" data-target="#app-{{ $app_index }}">+/-</a></td>
				@foreach (get_object_vars($app) as $prop => $value)
					@if (!is_array($value))
						<td>{!! nl2br(e($value)) !!}</td>
					@endif
				@endforeach
			</tr>
			<tr class="collapse" id="app-{{ $app_index }}">
				<td colspan="{{ $num_cols }}">
					<h3><a class="btn btn-default btn-xs" data-toggle="collapse" data-target="#app-{{ $app_index }}-hearings">+/-</a> hearings</h3>

					<div id="app-{{ $app_index }}-hearings" class="collapse">
						@if ($app->hearings)
							<table class="table table-bordered">
								<tr>
									<th></th>
									<?php $num_cols = 1; ?>
									@foreach (get_object_vars($app->hearings[0]) as $prop => $value)
										@if (!is_array($value))
											<?php $num_cols++; ?>
											<th>{{ $prop }}</th>
										@endif
									@endforeach
								</tr>
								@foreach ($app->hearings as $hearing_index => $hearing)
									<tr>
										<td>
											@if ($hearing->orders)
												<a class="btn btn-default btn-xs" data-toggle="collapse" data-target="#hearing-{{ $hearing_index }}">+/-</a>
											@endif
										</td>
										@foreach (get_object_vars($hearing) as $prop => $value)
											@if (!is_array($value))
												<td>{!! nl2br(e($value)) !!}</td>
											@endif
										@endforeach
									</tr>
									@if ($hearing->orders)
										<tr class="collapse" id="hearing-{{ $hearing_index }}">
											<td colspan="{{ $num_cols }}">
												<h4>orders</h4>

												<table class="table table-bordered">
													<tr>
														@foreach (get_object_vars($hearing->orders[0]) as $prop => $value)
															<th>{{ $prop }}</th>
														@endforeach
													</tr>
													@foreach ($hearing->orders as $order)
														<tr>
															@foreach (get_object_vars($order) as $prop => $value)
																<td>{!! nl2br(e($value)) !!}</td>
															@endforeach
														</tr>
													@endforeach
												</table>
											</td>
										</tr>
									@endif
								@endforeach
							</table>
						@else
							<p><em>None</em></p>
						@endif
					</div>

					<h3><a class="btn btn-default btn-xs" data-toggle="collapse" data-target="#app-{{ $app_index }}-documents">+/-</a> documents</h3>

					<div id="app-{{ $app_index }}-documents" class="collapse">
						@if ($app->documents)
							<table class="table table-bordered">
								<tr>
									@foreach (get_object_vars($app->documents[0]) as $prop => $value)
										<th>{{ $prop }}</th>
									@endforeach
								</tr>
								@foreach ($app->documents as $document)
									<tr>
										@foreach (get_object_vars($document) as $prop => $value)
											<td>{!! nl2br(e($value)) !!}</td>
										@endforeach
									</tr>
								@endforeach
							</table>
						@else
							<p><em>None</em></p>
						@endif
					</div>

					<h3><a class="btn btn-default btn-xs" data-toggle="collapse" data-target="#app-{{ $app_index }}-parties">+/-</a> parties</h3>

					<div id="app-{{ $app_index }}-parties" class="collapse">
						@if ($app->parties)
							<table class="table table-bordered">
								<tr>
									@foreach (get_object_vars($app->parties[0]) as $prop => $value)
										<th>{{ $prop }}</th>
									@endforeach
								</tr>
								@foreach ($app->parties as $party)
									<tr>
										@foreach (get_object_vars($party) as $prop => $value)
											<td>{!! nl2br(e($value)) !!}</td>
										@endforeach
									</tr>
								@endforeach
							</table>
						@else
							<p><em>None</em></p>
						@endif
					</div>
				</td>
			</tr>
		@endforeach
	</table>

</body>
</html>
