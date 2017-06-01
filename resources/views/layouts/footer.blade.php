@if (Request::is('/'))
	<div class="row footer_text">
		<div class="container">
			<div class="col-xs-12">
				<span class="footer_text_title">See something we can improve on?</span>
				<span class="footer_text_subtitle">Please don’t hesitate to contact us <a style="color:white;text-decoration:underline" href="{{url('contact-us')}}">here</a> so we can better improve your experience.</span>
			</div>
		</div>
	</div>
@endif

<div class="footer">
	<div class="container">
		<div class="row">
			<div class="col-xs-12 col-sm-4 links_container left_links">
				<a href="/">Home</a>
				<a href="/contact-us">Contact Us</a>
				<a href="#" class="nav_links modal_link" data-toggle="modal" data-target="#login_modal">Log In</a>
			</div>
			<div class="col-xs-12 col-sm-4">
				<div class="soial_icon_container">
					<a href="#"><object id="svg1" data="/assets/frontend/svgs/icon2.svg" type="image/svg+xml" class=""></object></a>
					<a href="#"><object id="svg2" data="/assets/frontend/svgs/icon1.svg" type="image/svg+xml" class=""></object></a>
					<a href="#"><object id="svg3" data="/assets/frontend/svgs/icon3.svg" type="image/svg+xml" class=""></object></a>
					<a href="#"><object id="svg4" data="/assets/frontend/svgs/icon4.svg" type="image/svg+xml" class=""></object></a>
					<a href="#"><object id="svg5" data="/assets/frontend/svgs/icon5.svg" type="image/svg+xml" class=""></object></a>
				</div>
				<div class="copyright">
					<span>&#169; 2016. All rights reserved.</span>
					<span><a href="/" style="color:white"><h2>Alares Systems Pty Ltd</h2></a></span>
					<span>ABN: 60 612 673 953</span>
				</div>
			</div>
			<div class="col-xs-12 col-sm-4 links_container right_links">
				<a href="/privacy-policy">Privacy Policy</a>
				<a href="/terms-and-conditions">Terms & Conditions</a>
			</div>
		</div>
	</div>
</div>
