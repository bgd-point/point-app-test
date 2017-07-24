<a href="{{ url('inventory/point/stock-correction') }}" class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
    List Data
</a>
<a href="{{ url('inventory/point/stock-correction/create') }}" class="btn {{\Request::segment(4)=='create'?'btn-primary':'btn-info'}}">
    Create
</a>
<a href="{{ url('inventory/point/stock-correction/request-approval') }}" class="btn {{\Request::segment(4)=='access'?'btn-primary':'btn-info'}}">
    Send Request Approval
</a>
<a href="{{ url('temporary-access/stock correction/point inventory stock correction') }}" class="btn {{\Request::segment(4)=='access'?'btn-primary':'btn-info'}}">
    Temporary Access
</a>
<br/><br/>
