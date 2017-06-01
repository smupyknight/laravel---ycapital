@extends('layouts.admin')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
   <div class="col-lg-10">
      <h2>Registered Users</h2>
   </div>
   <div class="col-lg-2">
   </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight ecommerce">
   <div class="ibox-content m-b-sm border-bottom">
      <form action="/admin/users" method="POST">
         {{ csrf_field() }}
         <div class="row">
            <div class="col-sm-4">
               <div class="form-group">
                  <label class="control-label" for="id">User ID</label>
                  <input type="text" id="id" name="id" value="{{isset($data['id']) ? $data['id'] : ''}}" placeholder="User ID" class="form-control">
               </div>
            </div>
            <div class="col-sm-4">
               <div class="form-group">
                  <label class="control-label" for="status">Account status</label>
                  <select name="status" class="form-control" id="status">
                     <option {{ (isset($data['status']) && $data['status'] == 'all') ? 'selected="selected"' : '' }} value="all">All</option>
                     <option {{ (isset($data['status']) && $data['status'] == 'active') ? 'selected="selected"' : '' }} value="active">Active</option>
                     <option {{ (isset($data['status']) && $data['status'] == 'inactive') ? 'selected="selected"' : '' }} value="inactive">Inactive</option>
                  </select>
               </div>
            </div>
            <div class="col-sm-4">
               <div class="form-group">
                  <label class="control-label" for="name">Name</label>
                  <input type="text" id="name" name="name" value="{{isset($data['name']) ? $data['name'] : ''}}" placeholder="Name" class="form-control">
               </div>
            </div>
         </div>
         <div class="row">
            <div class="col-sm-4">
               <div class="form-group">
                  <label class="control-label" for="date_added">Date added</label>
                  <div class="input-group date">
                     <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="date_added" type="text" class="form-control" name="date_added" value="{{isset($data['date_added']) ? $data['date_added'] : ''}}" placeholder="Select Date">
                  </div>
               </div>
            </div>
            <div class="col-sm-4">
               <div class="form-group">
                  <label class="control-label" for="date_modified">Date modified</label>
                  <div class="input-group date">
                     <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input id="date_modified" type="text" class="form-control" name="date_modified" value="{{isset($data['date_modified']) ? $data['date_modified'] : ''}}" placeholder="Select Date">
                  </div>
               </div>
            </div>
            <div class="col-sm-4">
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
                  <button type="submit" class="btn btn-warning" >Search</button>
               </div>
            </div>
         </div>
      </form>
   </div>
   <div class="row">
      <div class="col-lg-12">
         <div class="ibox">
            <div class="ibox-content">
               <div class="pull-right">
                  <a href="/admin/users/add" class="btn btn-primary">Add User</a>
               </div>
               <table class="footable table table-stripped toggle-arrow-tiny" data-page-size="15">
                  <thead>
                     <tr>
                        <th data-hide='phone'>User ID</th>
                        <th>Name</th>
                        <th>States Subscribed</th>
                        <th>Watchlist Subscription</th>
                        <th data-hide="phone">Date added</th>
                        <th data-hide="phone,tablet" >Date modified</th>
                        <th data-hide="phone">Status</th>
                        <th data-hide="phone">Watchlists</th>
                        <th data-hide="phone">Watchlist Entities</th>
                        <th class="text-right">Action</th>
                     </tr>
                  </thead>
                  <tbody>
                     @foreach ($users as $user)
                     <tr>
                        <td>
                           {{ucwords($user->id)}}
                        </td>
                        <td>
                           {{ucwords($user->name)}}
                        </td>
                        <td>
                           @if (!$user->states->isEmpty())
                              @foreach ($user->states as $state)
                                 <span class="label label-{{$state->states}}">{{strtoupper($state->states)}}</span>
                              @endforeach
                           @endif
                        </td>
                        <td>
                           {{ $user->can_access_watchlists ? 'Yes' : 'No' }}
                        </td>
                        <td>
                           {{$user->created_at->format('F d, Y')}}
                        </td>
                        <td>
                           {{$user->updated_at->format('F d, Y')}}
                        </td>
                        <td>
                           <span class="label label-{{$user->status}}">{{ucwords($user->status)}}</span>
                        </td>
                        <td>
                           {{ count($user->watchlists) }}
                        </td>
                        <td>
                           {{ $user->getNumWatchlistEntities($user->id) }}
                        </td>
                        <td class="text-right">
                           @if ($user->status != 'inactive')
                           <div class="btn-group">
                              <a href="/admin/users/edit/{{ $user->id }}" class="btn-white btn btn-xs">Edit</a>
                              <button class="btn-white btn btn-xs" onclick="delete_user({{ $user->id }}, '{{ addslashes($user->name) }}')">Delete</button>
                              <a href="#" onclick="impersonate_user('{{ $user->id }}','{{ addslashes($user->name) }}');return false;" class="btn-white btn btn-xs">Impersonate</a>
                           </div>
                           @endif
                        </td>
                     </tr>
                     @endforeach
                  </tbody>
                  <tfoot>
                     <tr>
                        <td colspan="9">
                           <ul class="pagination pull-right"></ul>
                        </td>
                     </tr>
                  </tfoot>
               </table>
            </div>
         </div>
      </div>
   </div>
</div>

@endsection

@section('js')
		<!-- FooTable -->
    <script src="/assets/frontend/js/plugins/footable/footable.all.min.js"></script>
		<!-- Data picker -->
    <script src="/assets/frontend/js/plugins/datapicker/bootstrap-datepicker.js"></script>
		<!-- Page-Level Scripts -->
    <script>

         function delete_user(id,name)
         {
            bootbox.confirm("Delete client "+name+"?", function(result) {
               if (result) {
                  window.location="/admin/users/delete/"+id;
               }
            });
         }

         function impersonate_user(id,name)
         {
            var form = ''+
               '<form action="/admin/users/impersonate/'+id+'" method="post" class="form-horizontal impersonate-form">'+
                  'You are about to impersonate '+name+'. When you are done, click Log out to return to your admin account.'+
                  '{{ csrf_field() }}'+
               '</form>';

            bootbox.dialog({
               title: 'Impersonate user',
               message: form,
               buttons: {
                  cancel: {
                     label: 'Cancel',
                     className: 'btn-default'
                  },
                  submit: {
                     label: 'Continue',
                     className: 'btn-primary',
                     callback : function() {
                        $('.impersonate-form').submit();
                     }
                  }
               }
            });
         }

			$(document).ready(function() {
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
                  $('#id').val('');
                  $('#status').val('all');
                  $('#name').val('');
                  $('#date_added').val('');
                  $('#date_modified').val('');
                  $('#states').select2().val(null).trigger('change');
                  $('#global_search').val('');
               });

               $('#states').select2({
                  placeholder: "Select States"
               });

					$('.footable').footable();

					$('#date_added').datepicker({
							todayBtn: "linked",
							keyboardNavigation: false,
							forceParse: false,
							calendarWeeks: true,
							autoclose: true
					});

					$('#date_modified').datepicker({
							todayBtn: "linked",
							keyboardNavigation: false,
							forceParse: false,
							calendarWeeks: true,
							autoclose: true
					});

			});
    </script>
@endsection
