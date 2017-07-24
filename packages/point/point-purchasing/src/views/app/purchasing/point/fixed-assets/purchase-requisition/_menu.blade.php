<a href="{{ url('purchasing/point/fixed-assets/purchase-requisition') }}" class="btn {{\Request::segment(5)==''?'btn-primary':'btn-info'}}">
    List data
</a>
<a href="{{ url('purchasing/point/fixed-assets/purchase-requisition/create') }}"
   class="btn {{\Request::segment(5)=='create'?'btn-primary':'btn-info'}}">
    Create
</a>
<a href="{{ url('temporary-access/fixed asset/point purchasing fixed asset') }}" class="btn btn-info">
    Temporary Access
</a>
<a href="{{ url('purchasing/point/fixed-assets/purchase-requisition/request-approval') }}"
   class="btn {{\Request::segment(5)=='request-approval'?'btn-primary':'btn-info'}}">
    Request Approval
</a>
<br/><br/>
