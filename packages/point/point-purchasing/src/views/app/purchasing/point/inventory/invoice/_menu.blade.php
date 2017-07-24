<a href="{{ url('purchasing/point/invoice') }}" class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
    List Data
</a>
@if(client_has_addon('pro') || client_has_addon('premium'))
    <a href="{{ url('purchasing/point/invoice/create-step-1') }}"
       class="btn {{\Request::segment(4)=='create-step-1' || \Request::segment(4)=='create-step-2'?'btn-primary':'btn-info'}}">
        Create
    </a>
@endif
<a href="{{ url('temporary-access/invoice/point purchasing invoice') }}"
   class="btn {{\Request::segment(4)=='access'?'btn-primary':'btn-info'}}">
    Temporary Access
</a>
{{--<a href="{{ url('purchasing/point/invoice/request-approval') }}" class="btn {{\Request::segment(4)=='request-approval'?'btn-primary':'btn-info'}}">
Request Approval
</a>--}}

<br/><br/>
