<a href="{{ url('inventory/point/transfer-item') }}" class="btn {{ \Request::segment(4) == '' ? 'btn-primary' : 'btn-info'}}">
    List data
</a>
<a href="{{ url('inventory/point/transfer-item/send/create') }}" class="btn {{ \Request::segment(4) == 'send' && \Request::segment(5) == 'create' ? 'btn-primary' : 'btn-info'}}">
    Send
</a>
<a href="{{ url('inventory/point/transfer-item/received/') }}" class="btn {{ \Request::segment(4) == 'received' ? 'btn-primary' : 'btn-info'}}">
    Receive
</a>
<a href="{{ url('inventory/point/transfer-item/send/request-approval') }}" class="btn {{ \Request::segment(4) == 'access' ? 'btn-primary' : 'btn-info'}}">
    Send Request Approval
</a>
<a href="{{ url('temporary-access/transfer item/point inventory transfer item') }}" class="btn btn-info">
	Temporary Access	
</a>

<br><br>
