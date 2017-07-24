<a href="{{ url('manufacture/point/process-io/'. $process->id.'/output' ) }}"
   class="btn {{\Request::segment(3)=='output'?'btn-primary':'btn-info'}}">
    List data
</a>
<a href="{{ url('manufacture/point/process-io/'. $process->id.'/output/create-step-1' ) }}"
   class="btn {{\Request::segment(4)=='create-step-1' || \Request::segment(4)=='create-step-2'?'btn-primary':'btn-info'}}">
    create
</a>
<a href="{{ url('temporary-access/manufacture | output/point manufacture output') }}" class="btn btn-info">
    Temporary access
</a>

<br/><br/>