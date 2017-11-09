@if(access_is_allowed_to_view('read.allocation'))
<a href="{{ url('master/allocation') }}" class="btn {{\Request::segment(3)==''?'btn-primary':'btn-info'}}">
    List Data
</a>
@endif
@if(access_is_allowed_to_view('create.allocation'))
<a href="{{ url('master/allocation/create') }}" class="btn {{\Request::segment(3)=='create'?'btn-primary':'btn-info'}}">
    Create
</a>
@endif
@if(access_is_allowed_to_view('create.role'))
<a href="{{ url('temporary-access/allocation/allocation') }}" class="btn {{\Request::segment(3)=='access'?'btn-primary':'btn-info'}}">
    Temporary Access
</a>
@endif

<br/><br/>
