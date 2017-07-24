<a href="{{ url('sales/point/indirect/invoice') }}" class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
    List Data
</a>
@if(client_has_addon('pro') || client_has_addon('premium'))
    <a href="{{ url('sales/point/indirect/invoice/create-step-1') }}"
       class="btn {{\Request::segment(4)=='create-step-1' || \Request::segment(4)=='create-step-2'?'btn-primary':'btn-info'}}">
        Create
    </a>
@endif
<a href="{{ url('temporary-access/invoice/point sales invoice') }}"
   class="btn {{\Request::segment(4)=='access'?'btn-primary':'btn-info'}}">
    Temporary Access
</a>

<br/><br/>
