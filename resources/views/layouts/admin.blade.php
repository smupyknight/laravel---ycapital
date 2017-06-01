<!DOCTYPE html>
<html>
   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>ALARES | {{$title}}</title>
      <link href="/assets/frontend/css/bootstrap.css" rel="stylesheet">
      <link href="/assets/frontend/css/font-awesome/css/font-awesome.css" rel="stylesheet">
      <!-- FooTable -->
      <link href="/assets/frontend/css/plugins/footable/footable.core.css" rel="stylesheet">
      <link href="/assets/frontend/css/animate.css" rel="stylesheet">
      <link href="/assets/frontend/css/admin-style.css" rel="stylesheet">
      <link href="/assets/frontend/css/plugins/datapicker/datepicker3.css" rel="stylesheet">

      <link href="/assets/frontend/css/select2.min.css" rel="stylesheet" />

      <script>
        (function() {var s=document.createElement('script');
          s.type='text/javascript';s.async=true;
          s.src=('https:'==document.location.protocol?'https':'http') +
          '://alares.groovehq.com/widgets/87c2f8dc-aa36-4f3d-b5ac-a1e8696034a7/ticket.js'; var q = document.getElementsByTagName('script')[0];q.parentNode.insertBefore(s, q);})();
      </script>
   </head>
   <body>
    <div id="wrapper">
      <nav class="navbar-default navbar-static-side" role="navigation">
         <div class="sidebar-collapse">
            <ul class="nav metismenu" id="side-menu">
               <li class="nav-header">
                  <div class="dropdown profile-element">
                     <span>
                     <img alt="image" class="img-circle" src="/assets/frontend/images/admin/profile_small.jpg" />
                     </span>
                     <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                     <span class="clear"> <span class="block m-t-xs"> <strong class="font-bold">{{Auth::user()->name}}</strong>
                     </span> <span class="text-muted text-xs block">Administrator <b class="caret"></b></span> </span> </a>
                     <ul class="dropdown-menu animated fadeInRight m-t-xs">
                        <li><a href="#profile.html">Profile</a></li>
                        <li class="divider"></li>
                        <li><a href="/auth/logout">Logout</a></li>
                     </ul>
                  </div>
                  <div class="logo-element">
                     IN+
                  </div>
               </li>
               <li>
                  <a href="/admin/dashboard"><i class="fa fa-th-large"></i> <span class="nav-label">Dashboard</span></a>
               </li>
               <li>
                  <a href="/admin/users"><i class="fa fa-user"></i> <span class="nav-label">Users</span></a>
               </li>
               <li>
                  <a href="/admin/companies"><i class="fa fa-user"></i> <span class="nav-label">Companies</span></a>
               </li>
                <li>
                    <a href="/client/cases"><i class="fa fa-user"></i> <span class="nav-label">Client Cases</span></a>
                </li>
               <li>
                  <a href="/admin/data-review"><i class="fa fa-edit"></i> <span class="nav-label">Data Review</span></a>
               </li>
               <li>
                  <a href="/admin/settings"><i class="fa fa-cog"></i> <span class="nav-label">Settings</span></a>
               </li>
            </ul>
         </div>
      </nav>
      <div id="page-wrapper" class="gray-bg">
				<div class="row border-bottom">
					 <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
							<div class="navbar-header">
								 <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i> </a>
								 <form method="POST" id="global_search_form" class="navbar-form-custom" action="/admin/users">
										<div class="form-group">
                        {{csrf_field()}}
											 <input type="text" placeholder="Search for something..." class="form-control" value="{{isset($data['global_search']) ? $data['global_search'] : ''}}" name="global_search" id="global_search">
										</div>
								 </form>
							</div>
							<ul class="nav navbar-top-links navbar-right">
								 <li>
										<a href="/auth/logout">
										<i class="fa fa-sign-out"></i> Log out
										</a>
								 </li>
							</ul>
					 </nav>
				</div>
				@yield('content')
				<div class="footer">
						<div>
								<strong>Copyright</strong> Y Capital &copy; 2016
						</div>
				</div>
		  </div>
		</div>

    <!-- Mainly scripts -->
    <script src="/assets/frontend/js/jquery-2.1.1.js"></script>
    <script src="/assets/frontend/js/select2.min.js"></script>
    <script src="/assets/frontend/js/bootbox.min.js"></script>
    <script src="/assets/frontend/js/bootstrap.min.js"></script>
    <script src="/assets/frontend/js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="/assets/frontend/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    <!-- Custom and plugin javascript -->
    <script src="/assets/frontend/js/inspinia.js"></script>
    <script src="/assets/frontend/js/plugins/pace/pace.min.js"></script>

		<!-- jQuery UI -->
		<script src="/assets/frontend/js/plugins/jquery-ui/jquery-ui.min.js"></script>

    <script>
      $(document).ready(function(){
        $('#global_search').on('keyup',function(e){
          if (e.which == 13) {
            $('#global_search_form').submit();
          }
        });
      });
    </script>

		@yield('js')
	</body>
</html>