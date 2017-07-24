@if(auth()->user()->may('read.point.finance.payment.order'))
<a href="{{ url('finance/point/payment-order') }}" class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
    List
</a>
@endif
@if(auth()->user()->may('create.point.finance.payment.order'))
<a href="{{ url('finance/point/payment-order/create-step-1') }}" class="btn {{\Request::segment(4)=='create-step-1' || \Request::segment(4)=='create-step-2'?'btn-primary':'btn-info'}}">
    Create
</a>
<a href="{{ url('finance/point/payment-order/request-approval') }}" class="btn {{\Request::segment(4)=='request-approval'?'btn-primary':'btn-info'}}">
    Request approval
</a>
@endif
@if(auth()->user()->may('create.role'))
<a href="{{ url('temporary-access/payment order/point finance payment order') }}" class="btn {{\Request::segment(4)=='access'?'btn-primary':'btn-info'}}">
    Temporary Access
</a>
@endif

<br/><br/>
