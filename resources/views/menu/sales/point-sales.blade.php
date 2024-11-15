@if(client_has_addon('basic'))
    @if(auth()->user()->may('menu.point.sales.pos'))
        <div class="col-md-4 col-lg-3">
            <a href="{{url('sales/point/pos/menu')}}" class="widget widget-button">
                <div class="widget-content text-right clearfix">
                    <i class="fa fa-4x fa-print push-bit pull-left"></i>
                    <h4 class="widget-heading"><strong>Point Of Sales</strong></h4>
                    <span class="text-muted">Sales</span>
                </div>
            </a>
        </div>
    @endif
@endif

@if(client_has_addon('basic'))
    @if(auth()->user()->may('menu.point.sales'))
        <div class="col-md-4 col-lg-3">
            <a href="{{url('sales/point/indirect')}}" class="widget widget-button">
                <div class="widget-content text-right clearfix">
                    <i class="fa fa-4x fa-clipboard push-bit pull-left"></i>
                    <h4 class="widget-heading"><strong>Goods Sales</strong></h4>
                    <span class="text-muted"></span>
                </div>
            </a>
        </div>
    @endif
@endif


@if(client_has_addon('basic'))
    @if(auth()->user()->may('menu.point.sales.service'))
        <div class="col-md-4 col-lg-3">
            <a href="{{url('sales/point/service')}}" class="widget widget-button">
                <div class="widget-content text-right clearfix">
                    <i class="fa fa-4x fa-briefcase push-bit pull-left"></i>
                    <h4 class="widget-heading"><strong>Service Sales</strong></h4>
                    <span class="text-muted"></span>
                </div>
            </a>
        </div>
    @endif
@endif
