<a href="{{ url('purchasing/point/service/purchase-order') }}" class="btn {{\Request::segment(5)==''?'btn-primary':'btn-info'}}">
    List Data
</a>
<a href="{{ url('purchasing/point/service/purchase-order/create') }}" class="btn {{\Request::segment(5)=='create'?'btn-primary':'btn-info'}}">
    Create
</a>
<a href="{{ url('temporary-access/purchase-order/point purchasing service invoice') }}" class="btn btn-info">
    Temporary Access
</a>
<a href="{{ url('purchasing/point/service/purchase-order/request-approval') }}" class="btn {{\Request::segment(5)=='request-approval'?'btn-primary':'btn-info'}}">
    Request Approval
</a>
<br/><br/>
