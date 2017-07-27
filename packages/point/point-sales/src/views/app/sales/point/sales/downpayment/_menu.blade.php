<a href="{{ url('sales/point/indirect/downpayment') }}"
   class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
    List Data
</a>
<a href="{{ url('sales/point/indirect/downpayment/create') }}"
   class="btn {{\Request::segment(4)=='create'?'btn-primary':'btn-info'}}">
    Create
</a>
<a href="{{ url('sales/point/indirect/downpayment/create-from-cutoff') }}"
   class="btn {{\Request::segment(4)=='create-from-cutoff'?'btn-primary':'btn-info'}}">
    Create from cutoff
</a>
<a href="{{ url('temporary-access/sales downpayment/point sales downpayment') }}" class="btn btn-info">
    Temporary Access
</a>
<a href="{{ url('sales/point/indirect/downpayment/request-approval') }}"
   class="btn {{\Request::segment(4)=='request-approval'?'btn-primary':'btn-info'}}">
    Request Approval
</a>

<br/><br/>
