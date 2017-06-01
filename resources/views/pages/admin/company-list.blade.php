@extends('layouts.admin')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
   <div class="col-lg-10">
      <h2>Company List</h2>
   </div>
   <div class="col-lg-2">
   </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight ecommerce">
   <div class="row">
      <div class="col-lg-12">
         <div class="ibox">
            <div class="ibox-content">
               <div class="pull-right">
                  <a href="/admin/companies/add" class="btn btn-primary">Add Company</a>
               </div>
               <table class="footable table table-stripped toggle-arrow-tiny" data-page-size="15">
                  <thead>
                     <tr>
                        <th>Name</th>
                        <th>ABN</th>
                        <th>ACN</th>
                        <th>Billing Address</th>
                        <th>Billing Email</th>
                        <th class="text-right">Action</th>
                     </tr>
                  </thead>
                  <tbody>
                     @foreach ($companies as $company)
                     <tr>
                        <td>{{$company->name}}</td>
                        <td>{{$company->abn}}</td>
                        <td>{{$company->acn}}</td>
                        <td>{{$company->billing_address}}</td>
                        <td>{{$company->billing_email}}</td>
                        <td class="text-right">
                           <div class="btn-group">
                              <a href="/admin/companies/edit/{{ $company->id }}" class="btn-white btn btn-xs">Edit</a>
                              <button class="btn-white btn btn-xs" onclick="delete_company({{ $company->id }}, '{{ addslashes($company->name) }}')">Delete</button>
                           </div>
                        </td>
                     </tr>
                     @endforeach
                  </tbody>
                  <tfoot>
                     <tr>
                        <td colspan="7">
                           <ul class="pagination pull-right">
                              {!!$companies->render()!!}
                           </ul>
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
   <script>
      function delete_company(id,name)
      {
         bootbox.confirm("Delete company: "+name+"?", function(result) {
            if (result) {
               window.location="/admin/companies/delete/"+id;
            }
         });
      }
   </script>
@endsection
