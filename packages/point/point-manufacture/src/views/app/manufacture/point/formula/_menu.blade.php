<a href="{{ url('manufacture/point/formula') }}" class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
    List data
</a>
<a href="{{ url('manufacture/point/formula/create') }}"
   class="btn {{\Request::segment(4)=='create'?'btn-primary':'btn-info'}}">
    Create
</a>
<a href="{{ url('temporary-access/manufacture | formula/point manufacture formula') }}" class="btn btn-info">
    Temporary Access
</a>
<a href="{{ url('manufacture/point/formula/request-approval') }}"
   class="btn {{\Request::segment(4)=='request-approval'?'btn-primary':'btn-info'}}">
    Request Approval
</a>
<br/><br/>