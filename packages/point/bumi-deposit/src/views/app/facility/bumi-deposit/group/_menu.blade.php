@if(auth()->user()->may('read.bumi.deposit.group'))
<a href="{{ url('facility/bumi-deposit/group') }}" class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
    List
</a>
@endif
@if(auth()->user()->may('create.bumi.deposit.group'))
<a href="{{ url('facility/bumi-deposit/group/create') }}" class="btn {{\Request::segment(4)=='create'?'btn-primary':'btn-info'}}">
    Create
</a>
@endif
@if(auth()->user()->may('create.role'))
<a href="{{ url('temporary-access/group/bumi deposit group') }}" class="btn btn-info">
    Access
</a>
@endif

<br/><br/>
