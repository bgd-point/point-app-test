<a href="{{ url('expedition/point/payment-order') }}" class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
    List Data
</a>
<a href="{{ url('expedition/point/payment-order/create-step-1') }}"
   class="btn {{\Request::segment(4)=='create-step-1' || \Request::segment(4)=='create-step-2'?'btn-primary':'btn-info'}}">
    Create
</a>
<a href="{{ url('temporary-access/payment-order/point expedition invoice') }}"
   class="btn {{\Request::segment(4)=='access'?'btn-primary':'btn-info'}}">
    Temporary Access
</a>
<a href="{{ url('expedition/point/payment-order/request-approval') }}"
   class="btn {{\Request::segment(4)=='request-approval'?'btn-primary':'btn-info'}}">
    Request Approval
</a>

<br/><br/>
