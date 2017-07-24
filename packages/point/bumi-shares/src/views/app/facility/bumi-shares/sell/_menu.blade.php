<a href="{{ url('facility/bumi-shares/sell') }}" class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
   List
</a>
<a href="{{ url('facility/bumi-shares/sell/create-step-1') }}" class="btn {{\Request::segment(4)=='create-step-1' || \Request::segment(4)=='create-step-2'?'btn-primary':'btn-info'}}">
   Create
</a>
<a href="{{ url('temporary-acces/sell shares/bumi shares sell') }}" class="btn {{\Request::segment(4)=='access'?'btn-primary':'btn-info'}}">
   Access
</a>
<a href="{{ url('facility/bumi-shares/sell/request-approval') }}" class="btn {{\Request::segment(4)=='request-approval'?'btn-primary':'btn-info'}}">
   Request Approval
</a>

<br/><br/>
