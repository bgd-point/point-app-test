<a href="{{ url('finance/point/bank') }}" class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
    List
</a>
<a href="{{ url('finance/point/bank/out/choose-payable') }}" class="btn {{\Request::segment(4)=='out' ?'btn-primary':'btn-info'}}">
    Make a Payment
</a>
<a href="{{ url('finance/point/bank/in/choose-receivable') }}" class="btn {{\Request::segment(4)=='in'?'btn-primary':'btn-info'}}">
    Receive Payment
</a>
<a href="{{ url('temporary-access/bank payment/point finance cashier bank') }}" class="btn btn-info">
    Temporary Access
</a>

<br/><br/>
