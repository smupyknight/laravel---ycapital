@extends('layouts.client_new')
@section('content')
<div class="container">
    <div class="row">
    	<div class="col-lg-12">
    		<h1>Notifications</h1>
    	</div>
        <div class="col-lg-12">
        	<h2>Entities</h2>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>
                            Party Name
                        </th>
                        <th>
                        	ABN/ACN
                        </th>
                        <th>
                            Alerts
                        </th>
                        <th>
                        	Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            Bill's Construction
                        </td>
                        <td>
                            45 154 997 846
                        </td>
                        <td>
                            <a href="">73 Alerts</a>
                        </td>
                        <td>
                        	<button type="button" class="btn btn-default btn-xs">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="/assets/client/js/jquery.dataTables.min.js" type="text/javascript">
</script>
<script src="/assets/client/js/script.js" type="text/javascript">
</script>
<script src="/assets/client/js/plugins/datapicker/bootstrap-datepicker.js">
</script>
@endsection
