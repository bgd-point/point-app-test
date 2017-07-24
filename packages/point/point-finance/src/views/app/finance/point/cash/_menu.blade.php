<a href="{{ url('finance/point/cash') }}" class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
    List
</a>
<a href="{{ url('finance/point/cash/out/choose-payable') }}" class="btn {{\Request::segment(4)=='out' ?'btn-primary':'btn-info'}}">
    Make a Payment
</a>
<a href="{{ url('finance/point/cash/in/choose-receivable') }}" class="btn {{\Request::segment(4)=='in'?'btn-primary':'btn-info'}}">
    Receive Payment
</a>
<a href="{{ url('temporary-access/cash payment/point finance cashier cash') }}" class="btn btn-info">
    Temporary Access
</a>

<br/><br/>
