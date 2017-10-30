<a href="{{ url('finance/point/cash-advance') }}" class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
    List Data
</a>
<a href="{{ url('finance/point/cash-advance/create') }}"
        class="btn {{\Request::segment(4)=='create'?'btn-primary':'btn-info'}}">
    Create
</a>
<a href="{{ url('temporary-access/cash advance/point finance cash advance') }}" class="btn btn-info">
    Temporary Access
</a>
<a href="{{ url('finance/point/cash-advance/request-approval') }}"
        class="btn {{\Request::segment(4)=='request-approval'?'btn-primary':'btn-info'}}">
    Request Approval
</a>

<br/><br/>
