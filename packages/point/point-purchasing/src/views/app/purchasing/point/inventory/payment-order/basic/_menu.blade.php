<a href="{{ url('purchasing/point/payment-order/basic') }}" class="btn {{\Request::segment(5)==''?'btn-primary':'btn-info'}}">
    List Data
</a>
<a href="{{ url('purchasing/point/payment-order/basic/create-step-1') }}"
   class="btn {{\Request::segment(5)=='create-step-1' || \Request::segment(5)=='create-step-2'?'btn-primary':'btn-info'}}">
    Create
</a>
<a href="{{ url('temporary-access/payment-order/point purchasing basic payment order') }}"
   class="btn {{\Request::segment(5)=='access'?'btn-primary':'btn-info'}}">
    Temporary Access
</a>
<a href="{{ url('purchasing/point/payment-order/basic/request-approval') }}"
   class="btn {{\Request::segment(5)=='request-approval'?'btn-primary':'btn-info'}}">
    Request Approval
</a>

<br/><br/>
