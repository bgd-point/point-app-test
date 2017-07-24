<a href="{{ url('inventory/point/inventory-usage') }}" class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
    List Data
</a>
<a href="{{ url('inventory/point/inventory-usage/create') }}" class="btn {{\Request::segment(4)=='create'?'btn-primary':'btn-info'}}">
    Create
</a>
<a href="{{ url('inventory/point/inventory-usage/request-approval') }}" class="btn {{\Request::segment(4)=='access'?'btn-primary':'btn-info'}}">
    Send Request Approval
</a>
<a href="{{ url('temporary-access/inventory usage/point inventory usage') }}" class="btn {{\Request::segment(5)=='access'?'btn-primary':'btn-info'}}">
    Temporary Access
</a>
<br/><br/>
