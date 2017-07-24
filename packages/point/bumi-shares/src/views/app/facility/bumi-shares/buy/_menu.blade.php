@if(auth()->user()->may('read.bumi.shares.buy'))
<a href="{{ url('facility/bumi-shares/buy') }}" class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
    List
</a>
@endif
@if(auth()->user()->may('create.bumi.shares.buy'))
<a href="{{ url('facility/bumi-shares/buy/create-step-1') }}" class="btn {{\Request::segment(4)=='create-step-1' || \Request::segment(4)=='create-step-2'?'btn-primary':'btn-info'}}">
    Create
</a>
@endif
@if(auth()->user()->may('create.role'))
<a href="{{ url('temporary-access/buy/bumi shares buy') }}" class="btn {{\Request::segment(4)=='access'?'btn-primary':'btn-info'}}">
   Access
</a>
@endif
@if(auth()->user()->may('create.bumi.shares.buy'))
<a href="{{ url('facility/bumi-shares/buy/request-approval') }}" class="btn {{\Request::segment(4)=='request-approval'?'btn-primary':'btn-info'}}">
    Request Approval
</a>
@endif

<br/><br/>
