<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
		<title>ALARES | {{ $title or '' }}</title>

		<!-- Bootstrap -->
		<link href="/assets/client_new/css/bootstrap.min.css" rel="stylesheet">
		<link href="/assets/client_new/css/font-awesome.css" rel="stylesheet">
		<link href="/assets/client_new/css/select2.css" rel="stylesheet">
		<link href="/assets/client_new/css/custom.css" rel="stylesheet">
		@yield('css')
		<script>
			(function() {var s=document.createElement('script');
				s.type='text/javascript';s.async=true;
				s.src=('https:'==document.location.protocol?'https':'http') +
				'://alares.groovehq.com/widgets/87c2f8dc-aa36-4f3d-b5ac-a1e8696034a7/ticket.js'; var q = document.getElementsByTagName('script')[0];q.parentNode.insertBefore(s, q);})();
		</script>

		<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
			<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
			<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>

		<!-- Fixed navbar -->
		<nav class="navbar navbar-default navbar-fixed-top">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-left" href="/"><img src="/assets/frontend/images/alares-logo.png" height="65px"></a>
				</div>
				<div id="navbar" class="collapse navbar-collapse pull-right">
					<ul class="nav navbar-nav">
						@if (Auth::check())
							@if (Auth::user()->isAdmin())
								<li class="{{ Request::is( 'admin*') ? 'active' : '' }}"><a href="/admin">Admin Dashboard</a></li>
							@endif
							@if (Auth::user()->can_access_watchlists)
								<li class="{{ Request::is( 'client/watchlists*') ? 'active' : '' }}"><a href="/client/watchlists">Watchlists</a></li>
							@endif
							@if (count(Auth::user()->getStates()))
								<li class="{{ Request::is( 'client/cases*') ? ' active' : '' }}">
									<a href="/client/cases">Cases</a>
								</li>
							@endif
							@if (Auth::user()->isAdmin())
								<li class="{{ Request::is( 'client/cases/by-party*') ? ' active' : '' }}">
									<a href="/client/cases/by-party">Party Search</a>
								</li>
							@endif
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Support <span class="caret"></span></a>
								<ul class="dropdown-menu dropdown-menu-right">
									<li><a href="/client/cases/types">Case Types</a></li>
								</ul>
							</li>
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">My Account <span class="caret"></span></a>
								<ul class="dropdown-menu dropdown-menu-right">
									<li><a href="/client/settings">Settings</a></li>
									<li role="separator" class="divider"></li>
									<li><a href="/auth/logout">Logout</a></li>
								</ul>
							</li>
						@else
							<li><a href="/" class="nav_links active">Home</a></li>
							<li><a href="/contact-us" class="nav_links">Contact Us</a></li>
							<li><a href="#" class="nav_links modal_link" data-toggle="modal" data-target="#login_modal">Log In</a></li>
							<li><a href="/contact-us" class="nav_btn">Enquire Now</a></li>
						@endif
					</ul>
				</div><!--/.nav-collapse -->
			</div>
		</nav>

		<div class="content">
			@yield('content')
		</div>

		@include('layouts.footer')

		@if(!Auth::check())
			<div id="login_modal" class="modal fade" data-backdrop="true" data-keyboard="true">
				<div class="modal-dialog custom_modal_dialog" role="document">
					<div class="modal-content">
						<div class="modal-header custom_modal_header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						</div>
						<div class="modal-body login_bg">
							<div class="col-xs-12 login_box">
								<div class="welcome_part">
									<span class="welcome_text">Welcome to</span>
									<img src="/assets/frontend/images/alares-logo-no-subtitle.png" style="height:100px" class="img-responsive">
									<span class="welcome_text">Client Portal</span>
									<span class="not_yet_text">Not a member yet?</span>
									<a href="/contact-us" class="img_link enquiry_link">ENQUIRE NOW</a>
								</div>
								<div class="login_part">
									<span class="login_text">Log into your account</span>
									@if (count($errors) > 0)
										<br><br><div class="col-xs-12 alert alert-danger" role="alert">
											<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
											<span class="sr-only">Error:</span>
											Invalid username/password
										</div>
									@endif
									<form method="POST" action="/auth/login">
										{!! csrf_field() !!}
										<div class="form-group uname_form_group custom_form_group">
											<input type="email" class="form-control" id="" name="email" value="{{old('email')}}" required placeholder="Username">
										</div>
										<div class="form-group password_form_group custom_form_group">
											<input type="password" class="form-control" id="" name="password" value="{{old('password')}}" required placeholder="Password">
										</div>
										<p>By logging in you agree to be bound by our <a href="#">terms and conditions</a>.</p>
										<button type="submit" class="btn btn-default custom_submit">LOG IN</button>
										<span class="forgot_password"><a href="/password/email">Forgot your password?</a></span>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		@endif

		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
		<!-- Include all compiled plugins (below), or include individual files as needed -->
		<script src="/assets/client_new/js/bootstrap.min.js"></script>
		<script src="/assets/client_new/js/bootbox.min.js"></script>
		<script src="/assets/client_new/js/modalform.js"></script>
		<script src="/assets/client_new/js/typeahead.js"></script>
		<script src="/assets/client_new/js/handlebars.js"></script>
		<script src="/assets/client_new/js/cookie.js"></script>
		@yield('js')
	</body>
</html>
