<a href="{{ url('accounting/point/cut-off/payable') }}" class="btn {{\Request::segment(5)==''?'btn-primary':'btn-info'}}">
	List
</a>
<a href="{{ url('accounting/point/cut-off/payable/create') }}" class="btn {{\Request::segment(5)=='create'?'btn-primary':'btn-info'}}">
	Create
</a>
<a href="{{ url('temporary-access/cut off/point accounting cut off') }}" class="btn btn-info">
	Access
</a>
<a href="{{ url('accounting/point/cut-off/payable/request-approval') }}" class="btn {{\Request::segment(5)=='request-approval'?'btn-primary':'btn-info'}}">
    Request Approval
</a>

<br/><br/>