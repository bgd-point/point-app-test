@if(auth()->user()->may('read.bumi.shares.broker'))
<a href="{{ url('facility/bumi-shares/broker') }}" class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
    List
</a>
@endif
@if(auth()->user()->may('create.bumi.shares.broker'))
<a href="{{ url('facility/bumi-shares/broker/create') }}" class="btn {{\Request::segment(4)=='create'?'btn-primary':'btn-info'}}">
    Create
</a>
@endif
@if(auth()->user()->may('create.role'))
<a href="{{ url('temporary-access/broker/bumi shares broker') }}" class="btn {{\Request::segment(4)=='access'?'btn-primary':'btn-info'}}">
    Access
</a> 
@endif

<br/><br/>
