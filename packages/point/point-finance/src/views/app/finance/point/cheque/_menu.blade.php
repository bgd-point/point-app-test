<a href="{{ url('finance/point/cheque') }}" class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
    List
</a>
<a href="{{ url('finance/point/cheque/list') }}" class="btn {{\Request::segment(4)=='pending'?'btn-primary':'btn-info'}}">
    List Cheque
</a>
<a href="{{ url('finance/point/cheque/out/choose-payable') }}" class="btn {{\Request::segment(4)=='out' ?'btn-primary':'btn-info'}}">
    Make a Payment
</a>
<a href="{{ url('finance/point/cheque/in/choose-receivable') }}" class="btn {{\Request::segment(4)=='in'?'btn-primary':'btn-info'}}">
    Receive Payment
</a>
<a href="{{ url('temporary-access/cheque payment/point finance cashier cheque') }}" class="btn btn-info">
    Temporary Access
</a>

<br/><br/>
