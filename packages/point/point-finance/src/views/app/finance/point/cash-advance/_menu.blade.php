<a href="{{ url('finance/point/cash-advance') }}" class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
    List Data
</a>
<a href="{{ url('finance/point/cash-advance/create') }}"
        class="btn {{\Request::segment(4)=='create'?'btn-primary':'btn-info'}}">
    Create
</a>
<a href="{{ url('temporary-access/cash advance/point finance cash advance') }}" class="btn btn-info">
    Temporary Access
</a>
<a href="{{ url('finance/point/cash-advance/request-approval') }}"
        class="btn {{\Request::segment(4)=='request-approval'?'btn-primary':'btn-info'}}">
    Request Approval
</a>
@if(isset($cash_advance)
    && !$cash_advance->handed_over
    && $cash_advance->formulir->approval_status === 1
    && $cash_advance->formulir->form_status === 0)
<a href="{{ $cash_advance->id }}/hand-over"
   class="btn btn-info">
    Sudah diserahkan
</a>
@endif

<br/><br/>
