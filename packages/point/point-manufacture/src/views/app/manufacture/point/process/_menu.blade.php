<a href="{{ url('manufacture/point/process') }}" class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
    List data
</a>
<a href="{{ url('manufacture/point/process/create') }}"
   class="btn {{\Request::segment(4)=='create'?'btn-primary':'btn-info'}}">
    Create
</a>
<a href="{{ url('temporary-access/manufacture | process/point manufacture process') }}" class="btn btn-info">
    Temporary Access
</a>
<br/><br/> 
