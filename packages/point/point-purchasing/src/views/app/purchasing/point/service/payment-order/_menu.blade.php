<a href="{{ url('purchasing/point/service/payment-order') }}"
   class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
    List Data
</a>
<a href="{{ url('purchasing/point/service/payment-order/create-step-1') }}"
   class="btn {{\Request::segment(4)=='create-step-1' || \Request::segment(4)=='create-step-2'?'btn-primary':'btn-info'}}">
    Create
</a>
<a href="{{ url('temporary-access/payment-order/point purchasing invoice') }}"
   class="btn {{\Request::segment(4)=='access'?'btn-primary':'btn-info'}}">
    Temporary Access
</a>
<a href="{{ url('purchasing/point/service/payment-order/request-approval') }}"
   class="btn {{\Request::segment(4)=='request-approval'?'btn-primary':'btn-info'}}">
    Request Approval
</a>
<a href="{{ url('purchasing/point/service/payment-order/report') }}" class="btn {{\Request::segment(5)=='report'?'btn-primary':'btn-info'}}">
    Report
</a>
<br/><br/>
