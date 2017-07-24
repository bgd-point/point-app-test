<a href="{{ url('sales/point/service/downpayment') }}"
   class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
    List Data
</a>
<a href="{{ url('sales/point/service/downpayment/create') }}"
   class="btn {{\Request::segment(5)=='create'?'btn-primary':'btn-info'}}">
    Create
</a>
<a href="{{ url('temporary-access/sales downpayment/point sales downpayment') }}" class="btn btn-info">
    Temporary Access
</a>
<a href="{{ url('sales/point/service/downpayment/request-approval') }}"
   class="btn {{\Request::segment(5)=='request-approval'?'btn-primary':'btn-info'}}">
    Request Approval
</a>

<br/><br/>
