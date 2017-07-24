<a href="{{ url('purchasing/point/purchase-requisition') }}"
   class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
    List Data
</a>
<a href="{{ url('purchasing/point/purchase-requisition/create') }}"
   class="btn {{\Request::segment(4)=='create'?'btn-primary':'btn-info'}}">
    Create
</a>
<a href="{{ url('temporary-access/purchase requisition/point purchasing requisition') }}" class="btn btn-info">
    Temporary Access
</a>
<a href="{{ url('purchasing/point/purchase-requisition/request-approval') }}"
   class="btn {{\Request::segment(4)=='request-approval'?'btn-primary':'btn-info'}}">
    Request Approval
</a>

<br/><br/>
