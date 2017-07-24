<a href="{{ url('facility/bumi-shares/owner') }}" class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
    List
</a>
<a href="{{ url('facility/bumi-shares/owner/create') }}" class="btn {{\Request::segment(4)=='create'?'btn-primary':'btn-info'}}">
    Create
</a>
<a href="{{ url('temporary-access/owner/bumi shares owner group') }}" class="btn {{\Request::segment(4)=='access'?'btn-primary':'btn-info'}}">
   Access
</a>

<br/><br/>
