@extends('layouts.frontend')
@section('content')
	<div class="row banner_bg bg_img_container">
			<div class="text_container">
			</div>
			<div class="container">
					<div class="col-xs-12 img_text">
							<span class="banner_title">Contact ALARES</span>
							<span class="banner_sub_text"></span>
					</div>
			</div>
	</div>
	<div class="row contact_form">
			<div class="col-xs-12 col-sm-8">
					@if (count($errors) > 0)
							<div class="alert alert-danger">
									<ul>
											@foreach ($errors->all() as $error)
													<li>{{ $error }}</li>
											@endforeach
									</ul>
							</div>
					@elseif(isset($message))
						<div class="alert alert-success">
							<i class="fa fa-check"></i> {{$message}}.
						</div>
							
					@endif
					<form class="custom_form" action="/contact-us" method="POST">
					{!! csrf_field() !!}
							<div class="form-group form-inline">
									<label for="enquiry_type">Enquiry Type</label>
									<select class="form-control" name="enquiry_type">
										<option value="new_enquiry" {{old('enquiry_type')=="new_enquiry" ? "selected" : ""}}>New Enquiry</option>
										<option value="upgrade" {{old('enquiry_type')=="upgrade" ? "selected" : ""}}>Upgrade</option>
										<option value="feedback" {{old('enquiry_type')=="feedback" ? "selected" : ""}}> Feedback</option>
										<option value="support" {{old('enquiry_type')=="support" ? "selected" : ""}}>Tech Support</option>
									</select>
							</div>
							<div class="form-group">
									<label for="name">Name</label>
									<input type="text" class="form-control" id="" name="name" value="{{old('name')}}" placeholder="First & Last Name">
							</div>
							<div class="form-group">
									<label for="email">Email</label>
									<input type="text" class="form-control" id="" name="email" value="{{old('email')}}" placeholder="Example@domain.com">
							</div>
							<div class="form-group">
									<label for="mobile">Mobile</label>
									<input type="text" class="form-control" id="" name="mobile" value="{{old('mobile')}}" placeholder="Mobile Number">
							</div>
							<div class="form-group">
									<label for="message">Message</label>
									<textarea class="form-control" rows="3" name="message" placeholder="">{{old('message')}}</textarea>
							</div>
							<div class="form-group">
									<label for="answer">2 + 3 = ?</label>
									<input type="text" class="form-control" id="" name="answer" value="{{old('answer')}}" placeholder="Your Answer">
									<input type="submit" class="img_link update_link get_in_touch_btn" value="GET IN TOUCH">
									</a>
							</div>
					</form>
			</div>
	</div>
@endsection
