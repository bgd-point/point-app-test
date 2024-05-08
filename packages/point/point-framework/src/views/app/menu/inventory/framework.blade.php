@if(\Auth::user()->may('read.inventory.report'))
    <!-- <div class="col-md-4 col-lg-3">
        <a href="{{url('inventory/report')}}" class="widget widget-button">
            <div class="widget-content text-right clearfix">
                <i class="gi fa-4x gi-book push-bit pull-left"></i>
                <h4 class="widget-heading"><strong>Inventory Report</strong></h4>
                <span class="text-muted"></span>
            </div>
        </a>
    </div> -->
@endif
@if(\Auth::user()->may('read.inventory.value.report'))
    <div class="col-md-4 col-lg-3">
        <a href="{{url('inventory/value-report')}}" class="widget widget-button">
            <div class="widget-content text-right clearfix">
                <i class="gi fa-4x gi-book push-bit pull-left"></i>
                <h4 class="widget-heading"><strong>Inventory Report</strong></h4>
                <span class="text-muted">With Value</span>
            </div>
        </a>
    </div>
@endif
