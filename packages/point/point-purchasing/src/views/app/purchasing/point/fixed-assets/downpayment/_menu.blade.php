<a href="{{ url('purchasing/point/fixed-assets/downpayment') }}" class="btn {{\Request::segment(5)==''?'btn-primary':'btn-info'}}">
    List Data
</a>
<a href="{{ url('purchasing/point/fixed-assets/downpayment/create') }}"
   class="btn {{\Request::segment(5)=='create'?'btn-primary':'btn-info'}}">
    Create
</a>
<a href="{{ url('temporary-access/purchase downpayment/point purchasing downpayment') }}" class="btn btn-info">
    Temporary Access
</a>
<a href="{{ url('purchasing/point/fixed-assets/downpayment/request-approval') }}"
   class="btn {{\Request::segment(5)=='request-approval'?'btn-primary':'btn-info'}}">
    Request Approval
</a>

<br/><br/>
