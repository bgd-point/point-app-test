<a href="{{ url('sales/point/indirect/delivery-order') }}"
   class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
    List Data
</a>
<a href="{{ url('sales/point/indirect/delivery-order/create-step-1') }}"
   class="btn {{\Request::segment(4)=='create-step-1' || \Request::segment(4)=='create-step-2'?'btn-primary':'btn-info'}}">
    Create
</a>
<a href="{{ url('temporary-access/goods delivery/point sales delivery order') }}" class="btn btn-info">
    Temporary Access
</a>
<a href="{{ url('sales/point/indirect/delivery-order/request-approval') }}"
   class="btn {{\Request::segment(4)=='request-approval'?'btn-primary':'btn-info'}}">
    Request Approval
</a>

<br/><br/>
