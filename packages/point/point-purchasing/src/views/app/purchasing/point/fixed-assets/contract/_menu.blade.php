<a href="{{ url('purchasing/point/fixed-assets/contract') }}"
   class="btn {{\Request::segment(5)==''?'btn-primary':'btn-info'}}">
    List Data
</a>
<a href="{{ url('purchasing/point/fixed-assets/contract/create-step-1') }}"
   class="btn {{\Request::segment(5)=='create-step-1' || \Request::segment(5)=='create-step-2'?'btn-primary':'btn-info'}}">
    Create
</a>
<a href="{{ url('temporary-access/purchase order/point purchasing order') }}" class="btn btn-info">
    Temporary Access
</a>
<a href="{{ url('purchasing/point/fixed-assets/contract/request-approval') }}" class="btn {{\Request::segment(5)=='request-approval'?'btn-primary':'btn-info'}}">
    Request Approval
</a>

<br/><br/>
