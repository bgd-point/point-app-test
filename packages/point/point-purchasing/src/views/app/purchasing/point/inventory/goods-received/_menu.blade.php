<a href="{{ url('purchasing/point/goods-received') }}"
   class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
    List Data
</a>
<a href="{{ url('purchasing/point/goods-received/create-step-1') }}"
   class="btn {{\Request::segment(4)=='create-step-1' || \Request::segment(4)=='create-step-2'?'btn-primary':'btn-info'}}">
    Create
</a>
<a href="{{ url('temporary-access/goods received/point purchasing goods received') }}" class="btn btn-info">
    Temporary Access
</a>

<br/><br/>
