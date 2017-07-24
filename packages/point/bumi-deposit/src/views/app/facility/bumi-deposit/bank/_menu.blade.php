@if(auth()->user()->may('read.bumi.deposit.bank'))
<a href="{{ url('facility/bumi-deposit/bank') }}" class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
    List
</a>
@endif
@if(auth()->user()->may('create.bumi.deposit.bank'))
<a href="{{ url('facility/bumi-deposit/bank/create') }}" class="btn {{\Request::segment(4)=='create'?'btn-primary':'btn-info'}}">
    Create
</a>
@endif
@if(auth()->user()->may('create.role'))
<a href="{{ url('temporary-access/bank/bumi deposit bank') }}" class="btn btn-info">
    Access
</a>
@endif

<br/><br/>
