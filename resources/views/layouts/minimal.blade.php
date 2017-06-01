<!DOCTYPE html>
<html>
   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>ALARES | {{isset($title) ? $title : ''}}</title>
      <link href="/assets/frontend/css/bootstrap.css" rel="stylesheet">
      <link href="/assets/frontend/css/font-awesome/css/font-awesome.css" rel="stylesheet">
      <!-- FooTable -->
      <link href="/assets/frontend/css/plugins/footable/footable.core.css" rel="stylesheet">
      <link href="/assets/frontend/css/animate.css" rel="stylesheet">
      <link href="/assets/frontend/css/admin-style.css" rel="stylesheet">
      <link href="/assets/frontend/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
   </head>
   <body class='gray-bg'>
      @yield('content')

    <!-- Mainly scripts -->
    <script src="/assets/frontend/js/jquery-2.1.1.js"></script>
    <script src="/assets/frontend/js/bootstrap.min.js"></script>
    <script src="/assets/frontend/js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="/assets/frontend/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    <!-- Custom and plugin javascript -->
    <script src="/assets/frontend/js/plugins/pace/pace.min.js"></script>
      
      <!-- jQuery UI -->
      <script src="/assets/frontend/js/plugins/jquery-ui/jquery-ui.min.js"></script>
    
    
      @yield('js')
   </body>
</html>