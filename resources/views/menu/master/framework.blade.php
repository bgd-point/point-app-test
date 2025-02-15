@if(auth()->user()->may('read.supplier') || auth()->user()->may('read.customer') || auth()->user()->may('read.employee') || auth()->user()->may('read.expedition'))
    <div class="col-md-4 col-lg-3">
        <a href="{{url('master/contact')}}" class="widget widget-button">
            <div class="widget-content text-right clearfix">
                <i class="fa fa-4x fa-users push-bit pull-left"></i>
                <h4 class="widget-heading"><strong>contact</strong></h4>
                <span class="text-muted">Supplier, Customer, Employee, Expedition</span>
            </div>
        </a>
    </div>
@endif
@if(auth()->user()->may('read.warehouse') && request()->get('database_name') != 'p_personalfinance')
    <div class="col-md-4 col-lg-3">
        <a href="{{url('master/warehouse')}}" class="widget widget-button">
            <div class="widget-content text-right clearfix">
                <i class="fa fa-4x fa-home push-bit pull-left"></i>
                <h4 class="widget-heading"><strong>Warehouse</strong></h4>
                <span class="text-muted">Multiple warehouse</span>
            </div>
        </a>
    </div>
@endif
@if(auth()->user()->may('read.coa'))
    <div class="col-md-4 col-lg-3">
        <a href="{{url('master/coa')}}" class="widget widget-button">
            <div class="widget-content text-right clearfix">
                <i class="fa fa-4x fa-book push-bit pull-left"></i>
                <h4 class="widget-heading"><strong>chart of accounts</strong></h4>
                <span class="text-muted"></span>
            </div>
        </a>
    </div>
@endif
@if(auth()->user()->may('read.item') && request()->get('database_name') != 'p_personalfinance')
    <div class="col-md-4 col-lg-3">
        <a href="{{url('master/item')}}" class="widget widget-button">
            <div class="widget-content text-right clearfix">
                <i class="fa fa-4x fa-cubes push-bit pull-left"></i>
                <h4 class="widget-heading"><strong>Item</strong></h4>
                <span class="text-muted"></span>
            </div>
        </a>
    </div>
@endif
@if(auth()->user()->may('read.allocation') && request()->get('database_name') != 'p_personalfinance')
    <div class="col-md-4 col-lg-3">
        <a href="{{url('master/allocation')}}" class="widget widget-button">
            <div class="widget-content text-right clearfix">
                <i class="fa fa-4x fa-align-left push-bit pull-left"></i>
                <h4 class="widget-heading"><strong>Allocation</strong></h4>
                <span class="text-muted"></span>
            </div>
        </a>
    </div>
@endif
{{--@if(auth()->user()->may('read.fixed.assets.item'))--}}
    {{--<div class="col-md-4 col-lg-3">--}}
        {{--<a href="{{url('master/fixed-assets-item')}}" class="widget widget-button">--}}
            {{--<div class="widget-content text-right clearfix">--}}
                {{--<i class="fa fa-4x fa-building push-bit pull-left"></i>--}}
                {{--<h4 class="widget-heading"><strong>Fixed Asset Item</strong></h4>--}}
                {{--<span class="text-muted"></span>--}}
            {{--</div>--}}
        {{--</a>--}}
    {{--</div>--}}
{{--@endif   --}}
@if(auth()->user()->may('read.service') && request()->get('database_name') != 'p_personalfinance')
    <div class="col-md-4 col-lg-3">
        <a href="{{url('master/service')}}" class="widget widget-button">
            <div class="widget-content text-right clearfix">
                <i class="fa fa-4x fa-briefcase push-bit pull-left"></i>
                <h4 class="widget-heading"><strong>Service</strong></h4>
                <span class="text-muted"></span>
            </div>
        </a>
    </div>
@endif
