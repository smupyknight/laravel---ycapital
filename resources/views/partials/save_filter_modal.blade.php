<div class="form-group">
    <label>Update an existing filter</label>
    <select name="my_filters_update" id="my_filters_update" class="form-control">
        <option value="">Select Filter</option>
        @foreach ($filters as $filter)
        <option>{{$filter->name}}</option>
        @endforeach
    </select>
	<hr>
    <label>Or create a new filter</label>
    <input type="text" name="my_filters_new" id="my_filters_new" class="form-control" placeholder="Create new filter">
</div>