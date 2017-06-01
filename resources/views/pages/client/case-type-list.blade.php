@extends('layouts.client_new')

@section('content')
<div class="row" style="margin-top:20px;margin-bottom:250px">
	@if (count($case_type_array) > 0)
	<div class="container-fluid" >
			@foreach ($case_type_array as $key => $value)
				<div class="grid-item state-header col-xs-2">
					<h1>{{ $key }}</h1>
					<ul>
					@foreach ($value as $case_type)
						<li>{{ $case_type }}</li>
					@endforeach
					</ul>
				</div>
			@endforeach
	</div>
	@else
	<div class="" style="min-height:400px">
		<br><br>
		<h3>The system is populating the list of case types for you, please check back tomorrow.</h3>
	</div>
	@endif
</div>
@endsection

@section('js')
	<script type="text/javascript" src="/assets/client_new/js/masonry.js"></script>
	<script>
	$(window).load(function(){
		$('.grid').masonry({
		  // options
		  columnWidth : 200,
		  itemSelector: '.grid-item',
		});
	});
	</script>
@endsection

