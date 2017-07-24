@if(client_has_addon('pro') && auth()->user()->may('read.point.inventory.usage'))
    <div class="col-md-4 col-lg-3">
        <a href="{{url('inventory/point/inventory-usage')}}" class="widget widget-button">
            <div class="widget-content text-right clearfix">
                <i class="fa fa-4x fa-pencil-square-o push-bit pull-left"></i>
                <h4 class="widget-heading"><strong>Inventory Usage</strong></h4>
                <span class="text-muted"></span>
            </div>
        </a>
    </div>
@endif

@if(client_has_addon('pro') && auth()->user()->may('read.point.inventory.stock.correction'))
    <div class="col-md-4 col-lg-3">
        <a href="{{url('inventory/point/stock-correction')}}" class="widget widget-button">
            <div class="widget-content text-right clearfix">
                <i class="fa fa-4x fa-pencil-square-o push-bit pull-left"></i>
                <h4 class="widget-heading"><strong>Stock Correction</strong></h4>
                <span class="text-muted"></span>
            </div>
        </a>
    </div>
@endif

@if(auth()->user()->may('read.point.inventory.stock.opname'))
    <div class="col-md-4 col-lg-3">
        <a href="{{url('inventory/point/stock-opname')}}" class="widget widget-button">
            <div class="widget-content text-right clearfix">
                <i class="fa fa-4x fa-sliders push-bit pull-left"></i>
                <h4 class="widget-heading"><strong>Stock Opname</strong></h4>
                <span class="text-muted"></span>
            </div>
        </a>
    </div>
@endif
@if(client_has_addon('pro') && auth()->user()->may('read.point.inventory.transfer.item'))
    <div class="col-md-4 col-lg-3">
        <a href="{{url('inventory/point/transfer-item')}}" class="widget widget-button">
            <div class="widget-content text-right clearfix">
                <i class="fa fa-4x fa-truck push-bit pull-left"></i>
                <h4 class="widget-heading"><strong>Transfer Item</strong></h4>
                <span class="text-muted"></span>
            </div>
        </a>
    </div>
@endif

