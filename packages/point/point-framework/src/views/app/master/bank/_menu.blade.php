@if(access_is_allowed_to_view('read.bank'))
<a href="{{ url('master/bank') }}" class="btn {{\Request::segment(3)==''?'btn-primary':'btn-info'}}">
    List Data
</a>
@endif
@if(access_is_allowed_to_view('create.bank'))
<a href="{{ url('master/bank/create') }}" class="btn {{\Request::segment(3)=='create'?'btn-primary':'btn-info'}}">
    Create
</a>
@endif
@if(access_is_allowed_to_view('create.user'))
<a href="{{ url('master/bank/set-user') }}" class="btn {{\Request::segment(3)=='set-user'?'btn-primary':'btn-info'}}">
    Set User
</a>
@endif
@if(access_is_allowed_to_view('create.role'))
<a href="{{ url('temporary-access/bank/bank') }}" class="btn {{\Request::segment(3)=='access'?'btn-primary':'btn-info'}}">
    Temporary Access
</a> 
@endif

<br/><br/>
