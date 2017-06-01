@extends('layouts.admin')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
   <div class="col-lg-10">
	  <h2>Settings</h2>
   </div>
   <div class="col-lg-2">
   </div>
</div>
<form action="" method="POST">
	{{ csrf_field() }}
	<div class="wrapper wrapper-content animated fadeInRight ecommerce">
	   <div class="ibox-content m-b-sm border-bottom">
	   		<div>

			  <!-- Nav tabs -->
			  <ul class="nav nav-tabs" role="tablist">
			    <li role="presentation" class="active"><a href="#cases" aria-controls="cases" role="tab" data-toggle="tab">Cases</a></li>
			    <li role="presentation"><a href="#alerts" aria-controls="alerts" role="tab" data-toggle="tab">Alerts</a></li>
			  </ul>

			  <!-- Tab panes -->
			  <div class="tab-content">
			    <div role="tabpanel" class="tab-pane active" id="cases">
			    	<br>
				  <label class="control-label" for="id">Jurisdiction</label><br><br>

					<label> Criminal : </label> &nbsp; 
						<input type="radio" {{$criminal_jurisdiction && $criminal_jurisdiction->value == 1 ? 'checked' : ''}} name="criminal_jurisdiction" id="criminal_jurisdiction_on" value="1"> <label for="criminal_jurisdiction_on">&nbsp;On&nbsp;</label>
						<input type="radio" {{$criminal_jurisdiction && $criminal_jurisdiction->value == 0 ? 'checked' : ''}} name="criminal_jurisdiction" id="criminal_jurisdiction_off" value="0"> <label for="criminal_jurisdiction_off">&nbsp;Off&nbsp;</label>
			    </div>
			    <div role="tabpanel" class="tab-pane" id="alerts">
			    	<br>
			    	<p><i>This will send alerts to the listed emails if no cases were scraped on that day.</i></p>
			    	<label> Enable : </label> &nbsp; 
						<input type="radio" {{$enable_alerts && $enable_alerts->value == 1 ? 'checked' : ''}} name="enable_alerts" id="enable_alerts_on" value="1"> <label for="enable_alerts_on">&nbsp;On&nbsp;</label>
						<input type="radio" {{ !$enable_alerts || $enable_alerts->value == 0 ? 'checked' : ''}} name="enable_alerts" id="enable_alerts_off" value="0"> <label for="enable_alerts_off">&nbsp;Off&nbsp;</label>

					<br><br>
					Email list : <button class="btn btn-primary btn-xs" onclick="add_email_alert();return false;">Add</button>
					<br><br>
					<div class="well">
						@foreach ($alert_emails as $email)
							<div>
								{{ $email->email }} <button class="btn btn-xs btn-danger" onclick="delete_email_alert('{{$email->id}}');return false;">Delete</button>
							</div>
						@endforeach
					</div>

			    </div>
			  </div>

			</div>
			 <div class="row">
			 	<div class="col-sm-12">
			 		<div class="pull-right">
			 			<button type="submit" class="btn btn-primary">Save</button>
			 		</div>
			 	</div>
			 </div>
		</div>
	</div>
</form>

@endsection

@section('js')
<script src="/assets/client_new/js/modalform.js"></script>
<script>
	function add_email_alert()
	{
		var form = ''+
				'<form action="/admin/settings/add-email-alert" method="post" class="form-horizontal">'+
					'<div class="form-group">'+
						'<label class="col-md-4 control-label">Email</label>'+
						'<div class="col-md-8">'+
							'<input type="text" name="email" value="" class="form-control">'+
						'</div>'+
					'</div>'+
					'{{ csrf_field() }}'+
				'</form>';

			modalform.dialog({
				bootbox: {
					title: 'Add Email Alert',
					message: form,
					buttons: {
						cancel: {
							label: 'Cancel',
							className: 'btn-default'
						},
						submit: {
							label: 'Add',
							className: 'btn-primary'
						}
					}
				},
			});
	}


	function delete_email_alert(id)
	{
		var form = ''+
				'<form action="/admin/settings/delete-email-alert/'+id+'" method="post" class="form-horizontal">'+
					'Delete email from list?'+
					'{{ csrf_field() }}'+
				'</form>';

			modalform.dialog({
				bootbox: {
					title: 'Delete Email Alert',
					message: form,
					buttons: {
						cancel: {
							label: 'Cancel',
							className: 'btn-default'
						},
						submit: {
							label: 'Delete',
							className: 'btn-primary'
						}
					}
				},
			});
	}
</script>
@endsection

