@if(client_has_addon('basic') && auth()->user()->may('menu.point.finance.payment.order'))
<div class="col-md-4 col-lg-3">
    <a href="{{url('finance/point/payment-order')}}" class="widget widget-button">
        <div class="widget-content text-right clearfix">
            <i class="fa fa-4x fa-file-text push-bit pull-left"></i>
            <h4 class="widget-heading"><strong>Payment Order</strong></h4>
            <span class="text-muted"></span>
        </div>
    </a>
</div>
@endif

@if(client_has_addon('basic') && auth()->user()->may('create.point.finance.cash.advance'))
    <div class="col-md-4 col-lg-3">
        <a href="{{url('finance/point/cash-advance')}}" class="widget widget-button">
            <div class="widget-content text-right clearfix">
                <i class="fa fa-4x fa-file-text push-bit pull-left"></i>
                <h4 class="widget-heading"><strong>Cash Advance</strong></h4>
                <span class="text-muted"></span>
            </div>
        </a>
    </div>
@endif

@if(client_has_addon('basic') && auth()->user()->may('read.point.finance.cashier.cash'))
<div class="col-md-4 col-lg-3">
    <a href="{{url('finance/point/cash')}}" class="widget widget-button">
        <div class="widget-content text-right clearfix">
            <i class="fa fa-4x fa-usd push-bit pull-left"></i>
            <h4 class="widget-heading"><strong>Cash</strong></h4>
            <span class="text-muted"></span>
        </div>
    </a>
</div>
@endif

@if(client_has_addon('basic') && auth()->user()->may('read.point.finance.cashier.bank'))
<div class="col-md-4 col-lg-3">
    <a href="{{url('finance/point/bank')}}" class="widget widget-button">
        <div class="widget-content text-right clearfix">
            <i class="fa fa-4x fa-credit-card push-bit pull-left"></i>
            <h4 class="widget-heading"><strong>Bank</strong></h4>
            <span class="text-muted"></span>
        </div>
    </a>
</div>
@endif

@if(client_has_addon('pro') && auth()->user()->may('read.point.finance.cashier.bank'))
{{--<div class="col-md-4 col-lg-3">--}}
    {{--<a href="{{url('finance/point/wesel')}}" class="widget widget-button">--}}
        {{--<div class="widget-content text-right clearfix">--}}
            {{--<i class="fa fa-4x fa-pencil-square-o push-bit pull-left"></i>--}}
            {{--<h4 class="widget-heading"><strong>Cheque</strong></h4>--}}
            {{--<span class="text-muted"></span>--}}
        {{--</div>--}}
    {{--</a>--}}
{{--</div>--}}
@endif

@if(client_has_addon('basic') && auth()->user()->may('read.point.finance.debts.aging.report'))
<div class="col-md-4 col-lg-3">
    <a href="{{url('finance/point/debts-aging-report')}}" class="widget widget-button">
        <div class="widget-content text-right clearfix">
            <i class="fa fa-4x fa-file-o push-bit pull-left"></i>
            <h4 class="widget-heading"><strong>Debts Aging Report</strong></h4>
            <span class="text-muted"></span>
        </div>
    </a>
</div>
@endif

@if(client_has_addon('basic') && auth()->user()->may('read.allocation.report'))
<div class="col-md-4 col-lg-3">
    <a href="{{url('finance/point/allocation-report')}}" class="widget widget-button">
        <div class="widget-content text-right clearfix">
            <i class="fa fa-4x fa-file-o push-bit pull-left"></i>
            <h4 class="widget-heading"><strong>Allocation Report</strong></h4>
            <span class="text-muted"></span>
        </div>
    </a>
</div>
@endif

@if(client_has_addon('basic') && auth()->user()->may('read.allocation.report'))
<div class="col-md-4 col-lg-3">
    <a href="{{url('finance/point/allocation-report-cash-flow')}}" class="widget widget-button">
        <div class="widget-content text-right clearfix">
            <i class="fa fa-4x fa-file-o push-bit pull-left"></i>
            <h4 class="widget-heading"><strong>Allocation Report (Cash Flow)</strong></h4>
            <span class="text-muted"></span>
        </div>
    </a>
</div>
@endif

@if(client_has_addon('basic') && auth()->user()->may('read.point.finance.bank.report'))
<div class="col-md-4 col-lg-3">
    <a href="{{url('finance/point/report/bank')}}" class="widget widget-button">
        <div class="widget-content text-right clearfix">
            <i class="fa fa-4x fa-file-o push-bit pull-left"></i>
            <h4 class="widget-heading"><strong>Bank Report</strong></h4>
            <span class="text-muted"></span>
        </div>
    </a>
</div>
@endif

@if(client_has_addon('basic') && auth()->user()->may('read.point.finance.cash.report'))
<div class="col-md-4 col-lg-3">
    <a href="{{url('finance/point/report/cash')}}" class="widget widget-button">
        <div class="widget-content text-right clearfix">
            <i class="fa fa-4x fa-file-o push-bit pull-left"></i>
            <h4 class="widget-heading"><strong>Cash Report</strong></h4>
            <span class="text-muted"></span>
        </div>
    </a>
</div>
@endif
