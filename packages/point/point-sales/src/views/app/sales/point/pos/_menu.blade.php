@if(auth()->user()->may('read.point.sales.pos.pricing'))
<a href="{{ url('sales/point/pos') }}" class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
    List
</a>
@endif
@if(auth()->user()->may('create.point.sales.pos.pricing'))
<a href="{{ url('sales/point/pos/create') }}" class="btn {{\Request::segment(4)=='create'?'btn-primary':'btn-info'}}">
    Create
</a>
@endif
@if(auth()->user()->may('create.role'))
<a href="{{ url('temporary-access/Point of Sales/point sales pos') }}" class="btn btn-info">
    Temporary Access
</a>
@endif

<br/><br/>
