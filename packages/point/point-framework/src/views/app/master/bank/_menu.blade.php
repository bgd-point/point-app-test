@if(access_is_allowed_to_view('read.bank'))
<a href="{{ url('master/bank') }}" class="btn {{\Request::segment(3)==''?'btn-primary':'btn-info'}}">
    List Data
</a>
@endif
@if(access_is_allowed_to_view('create.role'))
<a href="{{ url('temporary-access/bank/bank') }}" class="btn {{\Request::segment(3)=='access'?'btn-primary':'btn-info'}}">
    Temporary Access
</a> 
@endif

<br/><br/>
