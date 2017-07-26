<a href="{{ url('purchasing/point/invoice/basic') }}" class="btn {{\Request::segment(5)==''?'btn-primary':'btn-info'}}">
    List Data
</a>
<a href="{{ url('purchasing/point/invoice/basic/create') }}"
   class="btn {{\Request::segment(5)=='create' ?'btn-primary':'btn-info'}}">
    Create
</a>
<a href="{{ url('temporary-access/invoice/basic/point purchasing basic invoice') }}"
   class="btn {{\Request::segment(5)=='access'?'btn-primary':'btn-info'}}">
    Temporary Access
</a>

<br/><br/>
