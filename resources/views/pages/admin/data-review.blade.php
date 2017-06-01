@extends('layouts.admin')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
   <div class="col-lg-10">
	  <h2>Data Review</h2>
   </div>
   <div class="col-lg-2">
   </div>
</div>
<form action="/admin/data-review" method="POST">
<div class="wrapper wrapper-content animated fadeInRight ecommerce">
   <div class="ibox-content m-b-sm border-bottom">
		 {{ csrf_field() }}
		 <div class="row">
			<div class="col-sm-4">
			   <div class="form-group">
				  <label class="control-label" for="id">Case ID</label>
				  <input type="text" id="case_id" name="case_id"  placeholder="Case ID" class="form-control">
			   </div>
			</div>
			<div class="col-sm-4">
			   <div class="form-group">
				  <label class="control-label" for="jurisdiction">Jurisdiction</label>
				  <select name="jurisdiction" id="jurisdiction" class="form-control">
					<option value="">Select Jurisdiction</option>
					@foreach ($court_types as $court_type)
					<option>{{$court_type->court_type}}</option>
					@endforeach
				  </select>
			   </div>
			</div>
			<div class="col-sm-4">
			   <div class="form-group">
				  <label class="control-label" for="id">Case Name</label>
				  <input type="text" id="name" name="name"  placeholder="Case Name" class="form-control">
			   </div>
			</div>
		 </div>
		 <div class="row">
			<div class="col-sm-4">
			   <div class="form-group">
				  <label class="control-label" for="reason">Reason</label>
				  <select name="reason" id="reason" class="form-control">
					<option value="">Select Reason</option>
					@foreach ($notes as $note)
					<option>{{$note}}</option>
					@endforeach
				  </select>
			   </div>
			</div>
			<div class="col-sm-4">
			   <div class="form-group">
				  <label class="control-label" for="date_added">Date added</label>
				  <div class="input-group date">
					 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="date_added" type="text" class="form-control" name="date_added" placeholder="Select Date">
				  </div>
			   </div>
			</div>

			<div class="col-sm-4 pull-right">
			   <div class="form-group">
				  <label class="control-label" for="amount">States</label>
				  <select name="states[]" class="form-control" multiple="multiple" id="states">
					 <option value="act">ACT</option>
					 <option value="nsw">NSW</option>
					 <option value="qld">QLD</option>
					 <option value="vic">VIC</option>
					 <option value="wa">WA</option>
					 <option value="federal">Federal</option>
				  </select>
				  <div class="btn-group btn-group-sm">
					 <button type="button" class="btn btn-default" style="margin-top:5px" id="select_all_states">Select All</button>
					 <button type="button" class="btn btn-default" style="margin-top:5px" id="clear_all_states">Clear</button>
				  </div>
			   </div>
			   <div class="pull-right">
				  <button type="button" class="btn btn-white" id="clear_all_btn">Clear All</button>
				  <button type="button" class="search-btn-data-review btn btn-warning" >Search</button>
			   </div>
			</div>
   </div>
   <div class="row">
	  <div class="col-lg-12">
		 <div class="ibox">
			<div class="ibox-content">
				<table id="table_data" class="table dt-responsive table-bordered custom_data_table" cellspacing="0" width="100%">
						<thead>
								<tr>
									<th><input type="checkbox" name="check_all" id="check_all"></th>
									<th class="no-sort"></th>
									<th>Date Filed</th>
									<th>Jurisdiction</th>
									<th>Case ID</th>
				  <th>Case Name</th>
									<th>Case Type</th>
									<th>Reason</th>
									<th>Actions</th>
								</tr>
						</thead>
						<tbody id="table_content">
						</tbody>
				</table>
			</div>
		 </div>
	  </div>
   </div>
</div>
</form>
<meta name="_token" content="{!! csrf_token() !!}"/>

@endsection

@section('js')
		<!-- Data picker -->
	<script src="/assets/frontend/js/plugins/datapicker/bootstrap-datepicker.js"></script>

		<!-- Page-Level Scripts -->
	<script>
	$(window).load(function(){
			   $('.search-btn-data-review').trigger('click');
	})

		$(document).ready(function() {

			$('.search-btn-data-review').click(function(){
				var states = $('#states').val();
				var url = 'state='+states+'&page=1';
				filter_results(url);
			})

			$('#case_type').select2();

		   @if (isset($data['states']))
			  var data = [];
			  @foreach ($data['states'] as $state)
				 data.push('{{$state}}');
			  @endforeach
			  $('#states').select2().val(data).trigger('change');
		   @endif

		   $('#select_all_states').click(function(){
			  $('#states').select2().val(['act','nsw','qld','vic','wa','federal']).trigger('change');
		   });

		   $('#clear_all_states').click(function(){
			  $('#states').select2().val(null).trigger('change');
		   });

		   $('#clear_all_btn').click(function(){
			  $('#case_id').val('');
			  $('#reason').val('');
			  $('#jurisdiction').val('');
			  $('#case_type').val('all');
			  $('#name').val('');
			  $('#date_added').val('');
			  $('#states').select2().val(null).trigger('change');
			  $('#global_search').val('');
		   });

		   $('#states').select2({
			  placeholder: "Select States"
		   });

			$('#date_added').datepicker({
					todayBtn: "linked",
					keyboardNavigation: false,
					forceParse: false,
					calendarWeeks: true,
					autoclose: true
			});


			$(document).on('click', '.pagination a', function (e) {
				filter_results($(this).attr('href').split('?')[1]);
				e.preventDefault();
			});

			$('#check_all').change(function(){
				$('[id^="checkbox_"]').prop('checked', $(this).prop("checked"));
			});

		});

		function submit_form()
		{
			var app_or_rej;
			if (! $('#radio_approve').prop('checked') && ! $('#radio_reject').prop('checked')) {
				bootbox.alert('Select whether to Approve or Reject selected entries');
				return false;
			}
			app_or_rej = $('#radio_approve').prop('checked') ? 'Approve' : 'Reject';
			bootbox.confirm(app_or_rej+" selected entries?", function(result) {
			  if (result) {
				  $('form').submit();
			  }
			});
			return false;
		}

		function approve_data(id)
		{
		$('[id^="checkbox_"]').prop('checked',false);
			$('#checkbox_'+id).prop('checked',true);
			$('#radio_approve').prop('checked',true);
			$('.submit-selected-btn').click();
		}

		function reject_data(id)
		{
		$('[id^="checkbox_"]').prop('checked',false);
			$('#checkbox_'+id).prop('checked',true);
			$('#radio_reject').prop('checked',true);
			$('.submit-selected-btn').click();
		}

	function create_input(name,value,selector,type)
	{
	  if ($(selector).hasClass('data-review-input-editable')) {
		return;
	  }
	  $(selector).addClass('data-review-input-editable');
	  $(selector).html('<input class="form-control" type="text" id="'+name+'" name="'+name+'" value="'+value+'">');

	  if (type == 'date') {
		$('#'+name).datepicker({
			todayBtn: "linked",
			keyboardNavigation: false,
			forceParse: false,
			calendarWeeks: true,
			autoclose: true
		});
	  }
	}

		function filter_results(url)
		{
			$('#table_data').fadeIn();
			$('#table_content').html('<tr><td colspan="12" id="breakdown_loader" class="text-center"><i class="fa fa-spinner fa-spin breakdown-spinner"></i></td></tr>');
			$.ajax({
				url: '/admin/data-review/state-scrape-results?'+url,
				type: "get",
				data:
					{
						'_token': $('meta[name=_token]').attr('content'),
						'case_id' : $('#case_id').val(),
						'case_name' : $('#name').val(),
						'date_added' : $('#date_added').val(),
				  'reason' : $('#reason').val(),
				  'jurisdiction' : $('#jurisdiction').val(),
						'case_type' : $('#case_type').val(),
					},
				success: function(html){
					$('#table_content').hide();
					$('#table_content').html(html);
					$('#table_content').fadeIn();
				}
			});
		}
	</script>
@endsection
