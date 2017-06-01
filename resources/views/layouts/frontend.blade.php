<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>ALARES | {{$title}}</title>
	<meta charset="utf-8">
	<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport">
		<link rel="stylesheet" type="text/css" href="/assets/client_new/css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="/assets/client_new/css/font-awesome.css">
		<link rel="stylesheet" type="text/css" href="/assets/client_new/css/dataTables.bootstrap.min.css">
		<link href="/assets/client_new/css/select2.min.css" rel="stylesheet" />
		<link href="/assets/client_new/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
		<link rel="shortcut icon" href="/favicon.ico">
   		 <link href="/assets/client_new/css/custom.css" rel="stylesheet">
		@yield('css')
		<script>
            (function() {var s=document.createElement('script');
              s.type='text/javascript';s.async=true;
              s.src=('https:'==document.location.protocol?'https':'http') +
              '://alares.groovehq.com/widgets/87c2f8dc-aa36-4f3d-b5ac-a1e8696034a7/ticket.js'; var q = document.getElementsByTagName('script')[0];q.parentNode.insertBefore(s, q);})();
          </script>
	</head>
	<body class=" skin-1 top-navigation">
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
		            @if(!Auth::check())
		            	<li><a href="/" class="nav_links active">Home</a></li>
						<li><a href="/contact-us" class="nav_links">Contact Us</a></li>
						<li><a href="#" class="nav_links modal_link" data-toggle="modal" data-target="#login_modal">Log In</a></li>
						<li><a href="/contact-us" class="nav_btn">Enquire Now</a></li>
		            @else
		            	<li><a href="" class="nav_links">Support</a></li>
						<li><a href="{{Auth::user()->type == 'client' ? '/client' : '/admin'}}">Dashboard</a></li>
						<li class="manage_user">
								<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
										<i class="fa fa-user"></i>Welcome {{Auth::user()->name}}
								</a>
								<ul class="dropdown-menu">
										<li><a href="/client">Settings</a></li>
										<li class="divider"></li>
										<li><a href="/auth/logout">Logout</a></li>
								</ul>
						</li>
		            @endif
		          </ul>
		        </div><!--/.nav-collapse -->
		      </div>
		    </nav>
			@yield('content')
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
																	<label class="remember_text"> <input type="checkbox" name="remember_me" class="i-checks"> Remember me </label>
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
		<script type="text/javascript" src="/assets/frontend/js/jquery-1.11.2.min.js"></script>
		<script type="text/javascript" src="/assets/frontend/js/bootstrap.min.js"></script>
		<script src="/assets/frontend/js/select2.min.js"></script>
		<script src="/assets/frontend/js/plugins/toastr/toastr.min.js"></script>
	  	<script type="text/javascript" src="/assets/frontend/js/script.js"></script>
		<!-- iCheck -->
		<script type="text/javascript" src="/assets/frontend/js/plugins/iCheck/icheck.js"></script>
		<script>
					$(document).ready(function () {
							$('.i-checks').iCheck({
									checkboxClass: 'icheckbox_square-blue',
									radioClass: 'iradio_square-blue',
							});
					});
		</script>

		@yield('js')
	</body>
</html>