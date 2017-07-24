@if(auth()->user()->may('read.ksp.loan.application'))
<a href="{{ url('facility/ksp/loan-application') }}" class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
    List
</a>
@endif
@if(auth()->user()->may('create.ksp.loan.application'))
<a href="{{ url('facility/ksp/loan-application/create') }}" class="btn {{\Request::segment(4)=='create-step-1' || \Request::segment(4)=='create-step-2'?'btn-primary':'btn-info'}}">
    Create
</a>
@endif
@if(auth()->user()->may('create.role'))
<a href="{{ url('temporary-access/ksp/loan-application') }}" class="btn {{\Request::segment(4)=='access'?'btn-primary':'btn-info'}}">
   Access
</a>
@endif
@if(auth()->user()->may('create.ksp.loan.application'))
<a href="{{ url('facility/ksp/loan-application/request-approval') }}" class="btn {{\Request::segment(4)=='request-approval'?'btn-primary':'btn-info'}}">
    Request Approval
</a>
@endif

<br/><br/>
