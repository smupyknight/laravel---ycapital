@extends('layouts.admin')
@section('content')
   <div class="container">
      <div class="wrapper wrapper-content animated fadeInRight ecommerce">
         <div class="row">
            <div class="col-lg-12">
               <h1>{{$form_title}}</h1>
            </div>
            <div class="ibox">
               <div class="ibox-title">
                  <h5>Please enter the necessary details.</h5>
               </div>
               <div class="ibox-content">
                  <form action="" method="POST">
                     {{ csrf_field() }}
                     <fieldset class="form-horizontal">
                        <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                           <label class="col-sm-2 control-label">Name:</label>
                           <div class="col-sm-10">
                              <input type="text" class="form-control" placeholder="Name" name="name" value="{{isset($company) ? $company->name : old('name')}}">
                              @if ($errors->has('name'))
                                 <span class="help-block"><strong>{{ $errors->first('name') }}</strong></span>
                              @endif
                           </div>
                        </div>
                        <div class="form-group{{ $errors->has('abn') ? ' has-error' : '' }}">
                           <label class="col-sm-2 control-label">ABN:</label>
                           <div class="col-sm-10">
                              <input type="text" class="form-control" placeholder="ABN" name="abn" value="{{isset($company) ? old('abn',$company->abn) : old('abn')}}">
                              @if ($errors->has('abn'))
                                 <span class="help-block"><strong>{{ $errors->first('abn') }}</strong></span>
                              @endif
                           </div>
                        </div>
                        <div class="form-group{{ $errors->has('acn') ? ' has-error' : '' }}">
                           <label class="col-sm-2 control-label">ACN:</label>
                           <div class="col-sm-10">
                              <input type="text" class="form-control" placeholder="ACN" name="acn" value="{{isset($company) ? old('acn',$company->acn) : old('acn')}}">
                              @if ($errors->has('acn'))
                                 <span class="help-block"><strong>{{ $errors->first('acn') }}</strong></span>
                              @endif
                           </div>
                        </div>
                        <div class="form-group{{ $errors->has('billing_address') ? ' has-error' : '' }}">
                           <label class="col-sm-2 control-label">Billing Address:</label>
                           <div class="col-sm-10">
                              <textarea class="form-control" rows="3" placeholder="Billing Address" name="billing_address">{{isset($company) ? old('billing_address',$company->billing_address) : old('billing_address')}}</textarea>
                              @if ($errors->has('billing_address'))
                                 <span class="help-block"><strong>{{ $errors->first('billing_address') }}</strong></span>
                              @endif
                           </div>
                        </div>
                        <div class="form-group{{ $errors->has('billing_email') ? ' has-error' : '' }}">
                           <label class="col-sm-2 control-label">Email:</label>
                           <div class="col-sm-10">
                              <input type="text" class="form-control" placeholder="Email" name="billing_email" value="{{isset($company) ? old('billing_email',$company->billing_email) : old('billing_email')}}">
                              @if ($errors->has('billing_email'))
                                 <span class="help-block"><strong>{{ $errors->first('billing_email') }}</strong></span>
                              @endif
                           </div>
                        </div>
                        <div class="row">
                           <div class="form-group">
                              <div class="col-sm-4 col-sm-offset-10">
                                 <a href="/admin/companies" class="btn btn-white cancel-btn" type="button">Cancel</a>
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