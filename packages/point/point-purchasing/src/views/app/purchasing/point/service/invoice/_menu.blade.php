<a href="{{ url('purchasing/point/service/invoice') }}" class="btn {{\Request::segment(5)==''?'btn-primary':'btn-info'}}">
    List Data
</a>
<a href="{{ url('purchasing/point/service/invoice/create') }}" class="btn {{\Request::segment(5)=='create'?'btn-primary':'btn-info'}}">
    Create
</a>
<a href="{{ url('temporary-access/invoice/point purchasing service invoice') }}" class="btn btn-info">
    Temporary Access
</a>
<a href="{{ url('purchasing/point/service/invoice/request-approval') }}" class="btn {{\Request::segment(5)=='request-approval'?'btn-primary':'btn-info'}}">
    Request Approval
</a>
<br/><br/>
