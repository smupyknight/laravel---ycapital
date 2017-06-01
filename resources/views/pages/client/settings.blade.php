@extends('layouts.client_new')

@section('content')
<div class="container">
	<h1>User Settings</h1>
	<hr>
	<div class="row">
		<form action="" method="POST" class="form-horizontal">
			<div class="form-group{{ $errors->has('timezone') ? ' has-error' : '' }}">
				<label class="col-lg-2 control-label">Timezone</label>
				<div class="col-lg-4">
					<select name="timezone" class="form-control">
						@foreach (\DateTimeZone::listIdentifiers(\DateTimeZone::AUSTRALIA) as $timezone)
							<option value="{{ $timezone }}" {{old('timezone',Auth::user()->timezone)==$timezone ? 'selected' : '' }}>{{ preg_replace('%.*/%', '', str_replace('_', ' ', $timezone)) }} ({{ (new DateTime('now', new DateTimeZone($timezone)))->format('g:ia') }})</option>
						@endforeach
					</select>
					@if ($errors->has('timezone'))
						<span class="help-block">
							<strong>{{ $errors->first('timezone') }}</strong>
						</span>
					@endif
				</div>
			</div>
			{{ csrf_field() }}
			<div class="form-group">
				<div class="col-lg-4 col-lg-offset-2">
					<button type="submit" class="btn btn-primary">Save</button>
				</div>
			</div>
		</form>
	</div>

</div>
@endsection
