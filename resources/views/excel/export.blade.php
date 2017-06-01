<tr>
    @foreach ($headers as $header)
    <td>{{$header}}</td>
    @endforeach
</tr>

@foreach ($results as $result)
    <tr>
        @foreach ($result as $data)
        <td>{{$data}}</td>
        @endforeach
    </tr>
@endforeach