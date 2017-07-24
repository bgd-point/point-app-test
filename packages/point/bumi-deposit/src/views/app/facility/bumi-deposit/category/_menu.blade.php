@if(auth()->user()->may('read.bumi.deposit.category'))
<a href="{{ url('facility/bumi-deposit/category') }}" class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
    List
</a>
@endif
@if(auth()->user()->may('create.bumi.deposit.category'))
<a href="{{ url('facility/bumi-deposit/category/create') }}" class="btn {{\Request::segment(4)=='create'?'btn-primary':'btn-info'}}">
    Create
</a>
@endif
@if(auth()->user()->may('create.role'))
<a href="{{ url('temporary-access/category/bumi deposit group') }}" class="btn btn-info">
    Access
</a>
@endif

<br/><br/>
