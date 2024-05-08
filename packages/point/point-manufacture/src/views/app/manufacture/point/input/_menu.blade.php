<a href="{{ url('manufacture/point/process-io/'. $process->id .'/input') }}"
   class="btn {{\Request::segment(6)==''?'btn-primary':'btn-info'}}">
    List data
</a>
<a href="{{ url('manufacture/point/process-io/'. $process->id .'/input/choose-formula' ) }}"
   class="btn {{\Request::segment(6)=='choose-formula'?'btn-primary':'btn-info'}}">
    Choose Formula
</a>
<!-- <a href="{{ url('manufacture/point/process-io/'. $process->id .'/input/create') }}"
   class="btn {{\Request::segment(6)=='create'?'btn-primary':'btn-info'}}">
    Create
</a> -->
<a href="{{ url('temporary-access/manufacture | input/point manufacture input') }}" class="btn btn-info">
    Temporary Access
</a>
<a href="{{ url('manufacture/point/process-io/'. $process->id .'/input/request-approval') }}"
   class="btn {{\Request::segment(6)=='request-approval'?'btn-primary':'btn-info'}}">
    Request Approval
</a>

<br/><br/>
