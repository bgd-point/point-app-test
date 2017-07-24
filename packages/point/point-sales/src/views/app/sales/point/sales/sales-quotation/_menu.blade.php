<a href="{{ url('sales/point/indirect/sales-quotation') }}"
   class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
    List Data
</a>
<a href="{{ url('sales/point/indirect/sales-quotation/create') }}"
   class="btn {{\Request::segment(4)=='create'?'btn-primary':'btn-info'}}">
    Create
</a>
<a href="{{ url('temporary-access/sales quotation/point sales quotation') }}" class="btn btn-info">
    Temporary Access
</a>
<a href="{{ url('sales/point/indirect/sales-quotation/request-approval') }}"
   class="btn {{\Request::segment(4)=='request-approval'?'btn-primary':'btn-info'}}">
    Request Approval
</a>

<br/><br/>
