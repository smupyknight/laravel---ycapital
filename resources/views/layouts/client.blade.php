<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="author" content="">
	<title>ALARES | {{$title or ''}}</title>
	<link href="/assets/client/css/bootstrap.css" rel="stylesheet">
	<link href="/assets/client/css/style.css" rel="stylesheet">

	<link rel="stylesheet" type="text/css" href="/assets/frontend/css/font-awesome.css">
	<link rel="stylesheet" type="text/css" href="/assets/frontend/css/dataTables.bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="/assets/frontend/css/plugins/iCheck/all.css">
	<link href="/assets/frontend/css/select2.min.css" rel="stylesheet" />
	<link href="/assets/frontend/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
	<link href="/assets/frontend/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
    <link href="/assets/client_new/css/custom.css" rel="stylesheet">

	@yield('css')
	<script>
		(function() {var s=document.createElement('script');
		  s.type='text/javascript';s.async=true;
		  s.src=('https:'==document.location.protocol?'https':'http') +
		  '://alares.groovehq.com/widgets/87c2f8dc-aa36-4f3d-b5ac-a1e8696034a7/ticket.js'; var q = document.getElementsByTagName('script')[0];q.parentNode.insertBefore(s, q);})();
	  </script>
</head>

<body>
<div class="outer-container">
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
          	<!--
            <li><a href="/client/dashboard">Dashboard</a></li>
            <li class="{{ Request::is( 'client/watchlists*') ? 'active' : '' }}"><a href="/client/watchlists">Watchlists</a></li>
            -->
            <li class="{{ Request::is( 'client/cases*') ? 'active' : '' }}"><a href="/client/cases">Cases</a></li>
            
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Support <span class="caret"></span></a>
              <ul class="dropdown-menu dropdown-menu-right">
                <li><a href="/clients/case-types">Case Types</a></li>
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
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>
		@yield('sidebar')
	<div class="main-container">
		<div class="col-xs-12">
			@yield('content')
		</div>
	</div>
	<div class="col-xs-12">
			@include('layouts.client_footer')
	</div>
</div>
<script src="/assets/client/js/jquery-1.11.2.js"></script>
<script src="/assets/client/js/bootstrap.min.js"></script>
<script src="/assets/client/js/bootbox.min.js"></script>
<script src="/assets/frontend/js/select2.min.js"></script>
<script src="/assets/client_new/js/cookie.js"></script>
<script type="text/javascript" src="/assets/frontend/js/plugins/iCheck/icheck.js"></script>
<script>
	$(document).ready(function () {

		initialize_select2('#party_representatives','{{ action('Client\CasesController@getPartyRepresentatives') }}','#party_representative_dropdown','#filter_party_representative');
		initialize_select2('#court_suburbs','{{ action('Client\CasesController@getCourtSuburbs') }}','#court_suburb_dropdown','#filter_court_suburb');
		initialize_select2('#hearing_types','{{ action('Client\CasesController@getHearingTypes') }}','#hearing_type_dropdown','#filter_hearing_type');
		initialize_select2('#case_types','{{ action('Client\CasesController@getCaseTypes') }}','#case_type_dropdown','#filter_case_type');

	});
</script>
@yield('js')
<script>

	function initialize_select2(select_div,action,dropdown_div,filter_input)
	{
		 $(select_div).select2({
			val : '123',
			width:'100%',
			placeholder: 'Enter Text',
			multiple: true,
			dropdownParent: $(dropdown_div),
			ajax: {
				url: action,
				delay : 1000,
				dataType: 'json',
				data: function (params) {
					return {
						q: params.term, // search term
						notification_date : $('#filter_notification_date').val(),
						court_type : $('#filter_court_type').val(),
						jurisdiction : $('#filter_jurisdiction').val(),
						case_type : $('#filter_case_type').val(),
						hearing_type : $('#filter_hearing_type').val(),
						hearing_date : $('#filter_hearing_date').val(),
						document_date : $('#filter_document_date').val(),
						court_suburb : $('#filter_court_suburb').val(),
						party_representative : $('#filter_party_representative').val(),
						state : $('#filter_state').val(),
						page: params.page
					};

				},
				processResults: function (data, params) {
					params.page = params.page || 1;

					var select2Data = $.map(data, function (obj) {
						var result = {
							id: obj,
							text: obj
						};

						return result;
					});
					return {
						results: select2Data
					};
				}
			},
			escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
			minimumInputLength: 1,
			templateResult: formatRepo, // omitted for brevity, see the source of this page
			templateSelection: formatRepoSelection,// omitted for brevity, see the source of this page

		}).on('change',function(e){
			$(filter_input).val($(this).val());
		});
	}

	function formatRepo (repo)
	{
		if (repo.loading) {
			return repo.text;
		}
		return repo.text;
	}

	function formatRepoSelection (repo)
	{
		return repo.text;
	}
</script>
</body>

</html>