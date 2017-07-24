<a href="{{ url('purchasing/point/purchase-order') }}"
   class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
    List Data
</a>
<a href="{{ url('purchasing/point/purchase-order/create-step-1') }}"
   class="btn {{\Request::segment(4)=='create-step-1' || \Request::segment(4)=='create-step-2'?'btn-primary':'btn-info'}}">
    Create
</a>
<a href="{{ url('temporary-access/purchase order/point purchasing order') }}" class="btn btn-info">
    Temporary Access
</a>
<a href="{{ url('purchasing/point/purchase-order/request-approval') }}" class="btn {{\Request::segment(4)=='request-approval'?'btn-primary':'btn-info'}}">
    Request Approval
</a>

<br/><br/>
