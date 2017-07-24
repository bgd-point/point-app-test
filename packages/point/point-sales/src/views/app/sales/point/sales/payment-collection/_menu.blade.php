<a href="{{ url('sales/point/indirect/payment-collection') }}"
   class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
    List Data
</a>
<a href="{{ url('sales/point/indirect/payment-collection/create-step-1') }}"
   class="btn {{\Request::segment(4)=='create-step-1' || \Request::segment(4)=='create-step-2'?'btn-primary':'btn-info'}}">
    Create
</a>
<a href="{{ url('temporary-access/payment-collection/point sales invoice') }}"
   class="btn {{\Request::segment(4)=='access'?'btn-primary':'btn-info'}}">
    Temporary Access
</a>
<a href="{{ url('sales/point/indirect/payment-collection/request-approval') }}"
   class="btn {{\Request::segment(4)=='request-approval'?'btn-primary':'btn-info'}}">
    Request Approval
</a>

<br/><br/>
