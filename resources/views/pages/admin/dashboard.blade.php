@extends('layouts.admin')
@section('content')
<div class="wrapper wrapper-content">

	<div class="row">

		<div class="col-lg-3">
			<div class="ibox float-e-margins">
				<div class="ibox-title">
					<h5>Total Subscribers</h5>
				</div>
				<div class="ibox-content">
					<h1 class="no-margins">{{ $metrics['num_subscribers'] }} <i class="fa fa-user"></i></h1>
				</div>
			</div>
		</div>

		<div class="col-lg-3">
			<div class="ibox float-e-margins">
				<div class="ibox-title">
					<h5>Active Subscribers</h5>
					<span class="label label-info pull-right">Past 5 Minutes</span>
				</div>
				<div class="ibox-content">
					<h1 class="no-margins">{{ $metrics['num_active_subscribers'] }} <i class="fa fa-user" style="color: #23c6c8;"></i></h1>
				</div>
			</div>
		</div>

		<div class="col-lg-3 emails-sent" >
			<div class="ibox float-e-margins">
				<div class="ibox-title">
					<h5>Emails Sent</h5>
				</div>
				<div class="ibox-content">

				</div>
			</div>
		</div>

		<div class="col-lg-3 emails-today">
			<div class="ibox float-e-margins">
				<div class="ibox-title">
					<h5>Emails Today</h5>
					<span class="label label-info pull-right">Since 12am</span>
				</div>
				<div class="ibox-content">

				</div>
			</div>
		</div>

	</div>

	<div class="row">

		<div class="col-lg-3">
			<div class="ibox float-e-margins">
				<div class="ibox-title">
					<h5>Companies</h5>
				</div>
				<div class="ibox-content">
					<h1 class="no-margins">{{ $metrics['num_companies'] }} <i class="fa fa-users"></i></h1>
				</div>
			</div>
		</div>

		<div class="col-lg-3">
			<div class="ibox float-e-margins">
				<div class="ibox-title">
					<h5>Total Cases Scraped</h5>
				</div>
				<div class="ibox-content">
					<h1 class="no-margins">{{ $metrics['num_cases_scraped'] }} <i class="fa fa-gavel"></i></h1>
				</div>
			</div>
		</div>

		<div class="col-lg-3">
			<div class="ibox float-e-margins">
				<div class="ibox-title">
					<h5>Cases Scraped Today</h5>
					<span class="label label-info pull-right">Since 12am</span>
				</div>
				<div class="ibox-content">
					<h1 class="no-margins">{{ $metrics['num_cases_scraped_today'] }} <i class="fa fa-gavel" style="color: #23c6c8;"></i></h1>
				</div>
			</div>
		</div>

		<div class="col-lg-3">
			<div class="ibox float-e-margins">
				<div class="ibox-title">
					<h5>Last Scrape</h5>
				</div>
				<div class="ibox-content">
					<p class="no-margins">{{ $metrics['last_scrape_time']->setTimezone(new DateTimeZone('Australia/Brisbane'))->format('j F Y g:ia') }} <i class="fa fa-search"></i></p>
				</div>
			</div>
		</div>

	</div>

	<div class="row">
		 <div class="col-lg-12">
			<div class="ibox float-e-margins graph">
				<div class="ibox-title">
					<h5>Emails</h5>
					<div class="pull-right">
						<div class="btn-group">
							<a id="today" href="javascript:;" onclick="loadData('today')" class="btn btn-xs btn-white">Today</a>
							<a id="monthly" href="javascript:;"  onclick="loadData('monthly')" class="btn btn-xs btn-white">Monthly</a>
							<a id="annual" href="javascript:;" onclick="loadData('annual')" class="btn btn-xs btn-white">Annual</a>
						</div>
					</div>
				</div>
				<div class="ibox-content">
					<div class="row">
						<div class="col-lg-9">
							<div class="flot-chart">
								<div class="flot-chart-content text-center" id="flot-dashboard-chart"></div>
							</div>
						</div>
						<div class="col-lg-3 records ">

						</div>
					</div>
				</div>
			</div>
		 </div>
	</div>
</div>
@endsection

@section('js')

	<!-- Flot -->
	<script src="/assets/frontend/js/plugins/flot/jquery.flot.js"></script>
	<script src="/assets/frontend/js/plugins/flot/jquery.flot.tooltip.min.js"></script>
	<script src="/assets/frontend/js/plugins/flot/jquery.flot.spline.js"></script>
	<script src="/assets/frontend/js/plugins/flot/jquery.flot.resize.js"></script>
	<script src="/assets/frontend/js/plugins/flot/jquery.flot.pie.js"></script>
	<script src="/assets/frontend/js/plugins/flot/jquery.flot.symbol.js"></script>
	<script src="/assets/frontend/js/plugins/flot/jquery.flot.time.js"></script>
	<!-- Peity -->
	<script src="/assets/frontend/js/plugins/peity/jquery.peity.min.js"></script>
	<script src="/assets/frontend/js/demo/peity-demo.js"></script>
	<!-- Jvectormap -->
	<script src="/assets/frontend/js/plugins/jvectormap/jquery-jvectormap-2.0.2.min.js"></script>
	<script src="/assets/frontend/js/plugins/jvectormap/jquery-jvectormap-world-mill-en.js"></script>
	<!-- EayPIE -->
	<script src="/assets/frontend/js/plugins/easypiechart/jquery.easypiechart.js"></script>
	<!-- Sparkline -->
	<script src="/assets/frontend/js/plugins/sparkline/jquery.sparkline.min.js"></script>
	<!-- Sparkline demo data  -->
	<script src="/assets/frontend/js/demo/sparkline-demo.js"></script>
	<script>
		$(document).ready(function() {
			loadData('today');
		 });
		function loadData(period){
			$('#flot-dashboard-chart').html('<span id="breakdown_loader" class="center-block"><i class="fa fa-spinner fa-spin breakdown-spinner "></i></span>');
			var today = $('#today').text();
			var monthly = $('#monthly').text();
			var annual = $('#annual').text();

			$('#today').removeClass('active');
			$('#monthly').removeClass('active');
			$('#annual').removeClass('active');

			if(today.toLowerCase()==period){
				$('#today').addClass('active');
			}
			if(monthly.toLowerCase()==period){
				$('#monthly').addClass('active');
			}
			if(annual.toLowerCase()==period){
				$('#annual').addClass('active');
			}
			var url='{{ action('Admin\DashboardController@getFetchEmailMetrics',['#time']) }}';
			url = url.replace('#time', period);
			$.ajax({
				url:url,
				type:"get",
				success:function(response){



					// Draw graph region

					var data_accepted = [];
					var data_engaged = [];

					$.each(response.time_based,function (k,v) {
						var date = new Date(v.ts.date).getTime();
						data_accepted.push([date,v.count_sent]);
						data_engaged.push([date, v.count_unique_confirmed_opened]);
					})
					var datasets = [{
								label: "Emails Accepted",
								data: data_accepted,
								yaxis: 1,
								color: "#1ab394",
								lines: {
									lineWidth:1,
									show: true,
									fill: true,
									fillColor: {
										colors: [
											{ opacity: 0.2 },
											{ opacity: 0.4 }
										]
									}
								}
							}, {
								label: "Engaged Subscribers",
								data: data_engaged,
								yaxis: 1,
								color: "#1C84C6",
								lines: {
									lineWidth:1,
									show: true,
									fill: true,
									fillColor: {
										colors: [
											{ opacity: 0.2 },
											{ opacity: 0.4 }
										]
									}
								}
							}];

					function gd(year, month, day) {
						return new Date(year, month - 1, day).getTime();
					}
					var arr = { "today" : "hour", "monthly" : "day", "annual": "month" };

					{{--tickSize: [1, "{{ ['today' => 'hour', 'monthly' => 'day', 'annual' => 'month'][$graph_type] }}"],--}}
					$.plot($("#flot-dashboard-chart"), datasets, {
						xaxis: {
							mode: "time",
							tickSize: [1,arr[response.duration]],
							tickLength: 0,
							axisLabel: "Date",
							axisLabelUseCanvas: true,
							axisLabelFontSizePixels: 12,
							axisLabelFontFamily: 'Arial',
							axisLabelPadding: 10,
							color: "#d5d5d5",
							timezone: "browser"
						},
						yaxes: [{
							position: "left",
							color: "#d5d5d5",
							axisLabelUseCanvas: true,
							axisLabelFontSizePixels: 12,
							axisLabelFontFamily: 'Arial',
							axisLabelPadding: 3
						}],
						legend: {
							noColumns: 1,
							labelBoxBorderColor: "#000000",
							position: "nw"
						},
						grid: {
							hoverable: false,
							borderWidth: 0
						}
					});


					//endregion

					$('.emails-sent').closest('div').find('.ibox-content').html(
							'<h1 class="no-margins">'+response.count_sent_alltime+' <i class="fa fa-envelope"></i></h1>');
					$('.emails-today').closest('div').find('.ibox-content').html(
							'<h1 class="no-margins">'+response.count_sent_today+'<i class="fa fa-envelope" style="color: #23c6c8;"></i></h1>');

					var data = '<ul class="stat-list"><li><h2 class="no-margins">'+ response.period.count_clicked +'</h2><small>Total clicks</small>';
					if (response.period.count_accepted){
						data +=  '<div class="stat-percent">'+ parseInt(parseInt(response.period.count_clicked) / parseInt(response.period.count_accepted) * 100, 0)+' %</div>'
								+'<div class="progress progress-mini">'
								+'<div style="width: '+ parseInt(parseInt(response.period.count_clicked) / parseInt(response.period.count_accepted) * 100, 0) +'%;" class="progress-bar"></div>'
								+'</div></li>';
					}else {
						data += '<div class="stat-percent">N/A</div><div class="progress progress-mini"><div style="width:0%;" class="progress-bar"></div></div></li>';
					}

					data += '<li>'+
							'<h2 class="no-margins">'+response.period.count_rendered+'</h2>'+
							'<small>Total opens</small>';
					if (response.period.count_accepted) {
						data += '<div class="stat-percent">'+parseInt(response.period.count_rendered / response.period.count_accepted * 100, 0)+'%</div>' +
								'<div class="progress progress-mini">' +
								'<div style="width: '+parseInt(response.period.count_rendered / response.period.count_accepted * 100, 0)+'%;" class="progress-bar"></div>' +
								'</div></li>';
					}
					else {
						data += '<div class="stat-percent">N/A</div><div class="progress progress-mini">' +
								'<div style="width:0%;" class="progress-bar"></div></div></li>';
					}
					data += '<li>'+
							'<h2 class="no-margins">'+response.period.count_unique_confirmed_opened+'</h2>'+
							'<small>Total engagement</small>';

					if (response.period.count_accepted) {
						data += '<div class="stat-percent">' + parseInt(response.period.count_unique_confirmed_opened / response.period.count_accepted * 100, 0) + '%</div>' +
								'<div class="progress progress-mini">' +
								'<div style="width: ' + parseInt(response.period.count_unique_confirmed_opened / response.period.count_accepted * 100, 0) + '%;" class="progress-bar"></div>' +
								'</div></li>';
					}else{
						data += '<div class="stat-percent">N/A</div>'+
								'<div class="progress progress-mini">'+
								'<div style="width:0%;" class="progress-bar"></div></div></li>'
					}

					data += '</ul>';
					$('.records').html(data);
					console.log(response);
				}
			});
		}

	</script>
@endsection
