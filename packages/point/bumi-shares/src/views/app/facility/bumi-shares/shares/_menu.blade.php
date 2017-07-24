<a href="{{ url('facility/bumi-shares/shares') }}" class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
    List
</a>
<a href="{{ url('facility/bumi-shares/shares/create') }}" class="btn {{\Request::segment(4)=='create'?'btn-primary':'btn-info'}}">
    Create
</a>
<a href="{{ url('temporary-access/shares/bumi shares') }}" class="btn {{\Request::segment(4)=='access'?'btn-primary':'btn-info'}}">
   Access
</a>

<br/><br/>
