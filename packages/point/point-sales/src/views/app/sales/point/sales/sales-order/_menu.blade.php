<a href="{{ url('sales/point/indirect/sales-order') }}"
   class="btn {{\Request::segment(5)==''?'btn-primary':'btn-info'}}">
    List Data
</a>
<a href="{{ url('sales/point/indirect/sales-order/create-step-1') }}"
   class="btn {{\Request::segment(5)=='create' ||
                \Request::segment(5)=='create-step-1' ||
                \Request::segment(5)=='create-step-2' ? 'btn-primary' : 'btn-info'}}">
    Create
</a>
<a href="{{ url('temporary-access/sales order/point sales order') }}" class="btn btn-info">
    Temporary access
</a>
<a href="{{ url('sales/point/indirect/sales-order/request-approval') }}"
   class="btn {{\Request::segment(5)=='request-approval'?'btn-primary':'btn-info'}}">
    Request Approval
</a>

<br/><br/>
