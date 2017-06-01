@extends('layouts.admin')

@section('content')
	<div class="container">
		<div class="wrapper wrapper-content animated fadeInRight ecommerce">
			<div class="row">
				<div class="col-lg-12">
					<h1>Edit User</h1>
				</div>
				<div class="ibox">
					<div class="ibox-title">
						<h5>Please enter the necessary details.</h5>
					</div>
					<div class="ibox-content">
						<form action="/admin/users/edit/{{ $data->id }}" method="POST">
							{{ csrf_field() }}
							<fieldset class="form-horizontal">
								<div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
									<label class="col-sm-2 control-label">Name:</label>
									<div class="col-sm-10">
										<input type="text" class="form-control" placeholder="Name" name="name" value="{{old('name',$data->name)}}">
										@if ($errors->has('name'))
											<span class="help-block"><strong>{{ $errors->first('name') }}</strong></span>
										@endif
									</div>
								</div>
								<div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
									<label class="col-sm-2 control-label">Email Address:</label>
									<div class="col-sm-10">
										<input type="text" class="form-control" placeholder="Email Address" name="email" value="{{old('email',$data->email)}}">
										@if ($errors->has('email'))
											<span class="help-block"><strong>{{ $errors->first('email') }}</strong></span>
										@endif
									</div>
								</div>
								<div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
									<label class="col-sm-2 control-label">Password:</label>
									<div class="col-sm-10">
										<input type="password" class="form-control" placeholder="Password" name="password" value="">
										<i><small>Note: Leave blank to remain unchanged</small></i>
										@if ($errors->has('password'))
											<span class="help-block"><strong>{{ $errors->first('password') }}</strong></span>
										@endif
									</div>
								</div>
								<div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
									<label class="col-sm-2 control-label">Confirm Password:</label>
									<div class="col-sm-10">
										<input type="password" class="form-control" placeholder="Confirm Password" name="password_confirmation" value="">
										@if ($errors->has('password_confirmation'))
											<span class="help-block"><strong>{{ $errors->first('confirm_password') }}</strong></span>
										@endif
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label">Type:</label>
									<div class="col-sm-10">
										<select name="type" class="form-control" id="type">
											<option value="client">Subscriber</option>
											<option value="admin">Admin</option>
										</select>
									</div>
								</div>
								<div class="form-group{{ $errors->has('company_subscribed') ? ' has-error' : '' }}" id="company_subscribed">
									<label class="col-sm-2 control-label">Company Subscribed:</label>
									<div class="col-sm-10">
										<select name="company_subscribed" class="form-control" id="company_subscribed">
											@foreach ($companies as $company)
												<option {{old('company_subscribed',$data->company_subscribed) == $company->id ? 'selected' : ''}} value="{{$company->id}}">{{$company->name}}</option>
											@endforeach
										</select>
										@if ($errors->has('company_subscribed'))
											<span class="help-block"><strong>{{ $errors->first('company_subscribed') }}</strong></span>
										@endif
									</div>
								</div>
								<div class="form-group{{ $errors->has('states') ? ' has-error' : '' }}" id="states_subscribed">
									<label class="col-sm-2 control-label">States Subscribed:</label>
									<div class="col-sm-10">
										<select name="states[]" class="form-control" multiple="multiple" id="states">
											<option value="act">ACT</option>
											<option value="nsw">NSW</option>
											<option value="nt">NT</option>
											<option value="qld">QLD</option>
											<option value="vic">VIC</option>
											<option value="wa">WA</option>
											<option value="federal">Federal</option>
										</select>
										<div class="btn-group btn-group-sm">
											<button type="button" class="btn btn-default" style="margin-top:5px" id="select_all_states">Select All</button>
											<button type="button" class="btn btn-default" style="margin-top:5px" id="clear_all_states">Clear</button>
										</div>
										@if ($errors->has('states'))
											<span class="help-block"><strong>{{ $errors->first('states') }}</strong></span>
										@endif
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label">Permissions:</label>
									<div class="col-sm-10">
										<div class="checkbox"><label><input type="checkbox" name="can_access_watchlists" value="1"{{ $data->can_access_watchlists ? ' checked' : '' }}> Watchlists</label></div>
									</div>
								</div>
								<div class="row">
									<div class="form-group">
										<div class="col-sm-4 col-sm-offset-10">
											<a href="/admin/users" class="btn btn-white cancel-btn" type="button">Cancel</a>
											<button class="btn btn-primary apply-submit-btn" type="submit">Submit</button>
										</div>
									</div>
								</div>
							</fieldset>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('js')
   <script>
	  $(document).ready(function(){
		 @if (old('states',!$data->states->isEmpty() ? $data->states : []))
			var data = [];
			@foreach (old('states',$data->states) as $state)
			   @if (isset($state->states))
				data.push('{{$state->states}}');
			   @else
				data.push('{{$state}}');
			   @endif
			@endforeach
			$('#states').select2().val(data).trigger('change');
		 @endif
		 @if (old('type',$data->type) && old('type',$data->type) == 'admin')
			$('#type').val('admin');
			$('#states').select2({
			   placeholder: "Select States"
			});
			$('#states_subscribed').fadeOut();
			$('#company_subscribed').fadeOut();
		 @endif
		 $('#type').on('change',function(){
			if (this.value != 'client') {
			   $('#states_subscribed').hide();
			   $('#company_subscribed').hide();
			   return;
			}
			$('#states_subscribed').fadeIn();
			$('#company_subscribed').fadeIn();
			return;
		 });
		 $('#states').select2({
			placeholder: "Select States"
		 });
		 $('#select_all_states').click(function(){
			$('#states').select2().val(['act','nsw','qld','vic','wa','federal']).trigger('change');
		 });
		 $('#clear_all_states').click(function(){
			$('#states').select2().val(null).trigger('change');
		 });
	  });
   </script>
@endsection
