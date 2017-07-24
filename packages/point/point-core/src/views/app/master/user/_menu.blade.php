@if(auth()->user()->may('read.user'))
<a href="{{ url('master/user') }}" class="btn {{\Request::segment(2)=='user' && \Request::segment(3)==''?'btn-primary':'btn-info'}}">
    List
</a>
@endif
@if(auth()->user()->may('read.role'))
    <a href="{{ url('master/role') }}" class="btn {{\Request::segment(2)=='role'?'btn-primary':'btn-info'}}">
        <i class="fa fa-code-fork"></i> Role
    </a>
@endif
@if(auth()->user()->may('create.user'))
<a href="{{ url('master/user/create') }}" class="btn {{\Request::segment(2)=='user' && \Request::segment(3)=='create'?'btn-primary':'btn-info'}}">
    Create
</a>
@endif
@if(auth()->user()->may('create.role'))
<a href="{{ url('temporary-access/user/user') }}" class="btn btn-info">
    Temporary Access
</a>
@endif

<br/><br/>
