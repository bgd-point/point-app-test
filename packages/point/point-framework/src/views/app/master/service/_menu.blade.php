@if(access_is_allowed_to_view('read.service'))
<a href="{{ url('master/service') }}" class="btn {{\Request::segment(3)==''?'btn-primary':'btn-info'}}">
    List Data
</a>
@endif
@if(access_is_allowed_to_view('create.service'))
<a href="{{ url('master/service/create') }}" class="btn {{\Request::segment(3)=='create'?'btn-primary':'btn-info'}}">
    Create
</a>
@endif
@if(access_is_allowed_to_view('create.service'))
<a href="{{ url('master/service/import') }}" class="btn {{\Request::segment(3)=='import'?'btn-primary':'btn-info'}}">
    Import
</a>
@endif
@if(access_is_allowed_to_view('create.role'))
<a href="{{ url('temporary-access/service/service') }}" class="btn {{\Request::segment(3)=='access'?'btn-primary':'btn-info'}}">
    Temporary Access
</a>
@endif

<br/><br/>
