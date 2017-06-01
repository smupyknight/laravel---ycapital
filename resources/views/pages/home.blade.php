@extends('layouts.client_new')
@section('content')
	<div class="row home-banner">
		<div class="banner-overlay"></div>
		<div class="container">
			<div class="col-xs-12" id="banner_text">
				<h1>Early warning system for adverse legal court actions</h1>
				<ul class="list-inline">
					<li>Winding up applications</li>
					<li>Money owing claims</li>
					<li>Set aside statutory demands</li>
					<li>Many more</li>
				</ul>
				<div class="text-center" id="enquiry_button">
					<a href="/contact-us" class="img_link enquiry_link">ENQUIRE NOW</a>
				</div>
			</div>
		</div>
	</div>
	<div class="container">
		<div class="row headline-section">
			<div class="col-lg-12 text-center">
				<h2>Adverse legal action is a leading indicator for financial stress – be informed before it’s too late.<br>
				Get notified instantly if your customers, suppliers, creditors, lenders of partners are involved in an adverse court proceeding.</h2>
			</div>
		</div>
	</div>
	<div class="row details_container">
		<div class="container">
			<hr>
			<div class="col-xs-12 col-sm-4">
				<img src="/assets/frontend/images/icon1.png" class="img-responsive">
				<span class="icon_text">Instant alerts</span>
				<p class="text-center updates_detail">Get updated when new court proceedings are registered in Australia </p>
			</div>
			<div class="col-xs-12 col-sm-4">
				<img src="/assets/frontend/images/icon2.png" class="img-responsive">
				<span class="icon_text">Daily reports</span>
				<p class="text-center updates_detail">A comprehensive list of updates and opportunities, direct to your mailbox daily</p>
			</div>
			<div class="col-xs-12 col-sm-4">
				<img src="/assets/frontend/images/icon3.png" class="img-responsive">
				<span class="icon_text">Customised watchlists</span>
				<p class="text-center updates_detail">Watch for legal activity involving clients, suppliers and other parties for instant alerts</p>
			</div>
		</div>
	</div>
	<div class="row updates_container">
		<div class="container">
			<div class="col-xs-12 col-sm-6">
				<img src="/assets/frontend/images/pc_icon_3.png" class="img-responsive">
			</div>
			<div class="col-xs-12 col-sm-6" style="margin-top:30px">
				<span class="updates_title">Comprehensive updates at the click of a button.</span>
				<span class="updates_detail">Customise your search results based on states, courts and actions of interests.</span>
				<a href="/contact-us" class="img_link update_link">LEARN MORE</a>
			</div>
		</div>
	</div>
@endsection

@section('js')
<script>
	$(document).ready(function(){
		@if (count($errors) > 0)
			$('#login_modal').modal();
		@endif
	});
</script>
@endsection
