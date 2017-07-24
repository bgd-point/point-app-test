<a href="{{ url('accounting/point/memo-journal') }}" class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
	List
</a>
<a href="{{ url('accounting/point/memo-journal/create') }}" class="btn {{\Request::segment(4)=='create'?'btn-primary':'btn-info'}}">
	Create
</a>
<a href="{{ url('accounting/point/memo-journal/request-approval') }}" class="btn {{\Request::segment(4)=='request-approval'?'btn-primary':'btn-info'}}">
    Request Approval
</a>
<a href="{{ url('temporary-access/memo journal/point accounting memo journal') }}" class="btn btn-info">
	Temporary Access
</a>

<br/><br/>
