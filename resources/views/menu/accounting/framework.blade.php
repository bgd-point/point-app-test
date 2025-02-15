@if(\Auth::user()->may('read.balance.sheet'))
    <div class="col-md-4 col-lg-3">
        <a href="{{url('accounting/balance-sheet')}}" class="widget widget-button">
            <div class="widget-content text-right clearfix">
                <i class="fa fa-4x fa-book push-bit pull-left"></i>
                <h4 class="widget-heading"><strong>Balance Sheet</strong></h4>
                <span class="text-muted"></span>
            </div>
        </a>
    </div>
@endif
@if(\Auth::user()->may('read.trial.balance'))
    <div class="col-md-4 col-lg-3">
        <a href="{{url('accounting/trial-balance')}}" class="widget widget-button">
            <div class="widget-content text-right clearfix">
                <i class="fa fa-4x fa-book push-bit pull-left"></i>
                <h4 class="widget-heading"><strong>Trial Balance</strong></h4>
                <span class="text-muted"></span>
            </div>
        </a>
    </div>
@endif
@if(\Auth::user()->may('read.profit.and.loss'))
    <div class="col-md-4 col-lg-3">
        <a href="{{url('accounting/profit-and-loss?month_from='.date('m').'&year_from='.date('Y').'&month_to='.date('m').'&year_to='.date('Y'))}}" class="widget widget-button">
            <div class="widget-content text-right clearfix">
                <i class="fa fa-4x fa-book push-bit pull-left"></i>
                <h4 class="widget-heading"><strong>Profit & Loss</strong></h4>
                <span class="text-muted"></span>
            </div>
        </a>
    </div>
@endif
@if(\Auth::user()->may('read.general.ledger'))
    <div class="col-md-4 col-lg-3">
        <a href="{{url('accounting/general-ledger')}}" class="widget widget-button">
            <div class="widget-content text-right clearfix">
                <i class="fa fa-4x fa-book push-bit pull-left"></i>
                <h4 class="widget-heading"><strong>General Ledger</strong></h4>
                <span class="text-muted"></span>
            </div>
        </a>
    </div>
@endif
@if(\Auth::user()->may('read.sub.ledger'))
    <div class="col-md-4 col-lg-3">
        <a href="{{url('accounting/sub-ledger')}}" class="widget widget-button">
            <div class="widget-content text-right clearfix">
                <i class="fa fa-4x fa-book push-bit pull-left"></i>
                <h4 class="widget-heading"><strong>Sub Ledger</strong></h4>
                <span class="text-muted"></span>
            </div>
        </a>
    </div>
@endif
@if(\Auth::user()->may('read.cashflow') && request()->get('database_name') != 'p_personalfinance')
    {{--<div class="col-md-4 col-lg-3">--}}
        {{--<a href="{{url('accounting/cashflow')}}" class="widget widget-button">--}}
            {{--<div class="widget-content text-right clearfix">--}}
                {{--<i class="fa fa-4x fa-book push-bit pull-left"></i>--}}
                {{--<h4 class="widget-heading"><strong>Cashflow</strong></h4>--}}
                {{--<span class="text-muted"></span>--}}
            {{--</div>--}}
        {{--</a>--}}
    {{--</div>--}}
@endif
