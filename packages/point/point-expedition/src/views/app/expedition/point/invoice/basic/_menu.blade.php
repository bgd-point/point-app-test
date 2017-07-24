<a href="{{ url('expedition/point/invoice/basic') }}" class="btn {{\Request::segment(5)==''?'btn-primary':'btn-info'}}">
    List Data
</a>
<a href="{{ url('expedition/point/invoice/basic/create') }}"
   class="btn {{\Request::segment(5)=='create'?'btn-primary':'btn-info'}}">
    Create
</a>
<a href="{{ url('temporary-access/invoice/point expedition invoice') }}"
   class="btn {{\Request::segment(4)=='access'?'btn-primary':'btn-info'}}">
    Temporary Access
</a>

<br><br>
