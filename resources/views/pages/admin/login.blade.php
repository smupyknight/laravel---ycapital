@extends('layouts.minimal')

@section('content')

	<div class="middle-box text-center loginscreen animated fadeInDown">
		<div>
			<div>
				<img src="/assets/frontend/images/alares-login-logo.png" style="" class="login-logo">
			</div>

			@if (isset($message))
				<div class="alert alert-danger">
					{{ $message or '' }}
				</div>
			@endif

			<form class="m-t" role="form" method="POST" action="/auth/login">
				{!! csrf_field() !!}
				<div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
					<input type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="Email" required="">
					@if ($errors->has('email'))
						<span class="help-block">
							<strong>{{ $errors->first('email') }}</strong>
						</span>
					@endif
				</div>
				<div class="form-group">
					<input type="password" class="form-control" name="password" placeholder="Password" required="">
					@if ($errors->has('password'))
						<span class="help-block">
							<strong>{{ $errors->first('password') }}</strong>
						</span>
					@endif
					<p><input type="checkbox" name="conditions_agree"> I agree to be bound by the <a href="/terms-and-conditions">terms and conditions</a>.</p>
					@if ($errors->has('conditions_agree'))
						<span class="help-block">
							<strong>{{ $errors->first('conditions_agree') }}</strong>
						</span>
					@endif
				</div>
				<button type="submit" class="btn btn-primary block full-width m-b">Login</button>

				<a href="/password/email"><small>Forgot password?</small></a>
			</form>
			<p class="m-t"> <small>Alares Systems Pty Ltd </p>
		</div>
	</div>
@endsection
