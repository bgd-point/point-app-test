<a href="{{ url('purchasing/point/fixed-assets/invoice') }}" class="btn {{\Request::segment(5)==''?'btn-primary':'btn-info'}}">
    List Data
</a>
@if(client_has_addon('pro') || client_has_addon('premium'))
    <a href="{{ url('purchasing/point/fixed-assets/invoice/create-step-1') }}"
       class="btn {{\Request::segment(5)=='create-step-1' || \Request::segment(5)=='create-step-2'?'btn-primary':'btn-info'}}">
        Create
    </a>
@endif
<a href="{{ url('temporary-access/invoice/point purchasing invoice') }}"
   class="btn {{\Request::segment(5)=='access'?'btn-primary':'btn-info'}}">
    Temporary Access
</a>

<br/><br/>
