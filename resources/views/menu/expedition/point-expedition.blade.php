{{ \Log::info('expedition auth : '.auth()->user()->may('read.point.expedition.order')) }}
{{ \Log::info('expedition code : '.client_has_addon('premium')) }}
@if(client_has_addon('premium') && auth()->user()->may('read.point.expedition.order'))
    <div class="col-md-4 col-lg-3">
        <a href="{{url('expedition/point/expedition-order')}}" class="widget widget-button">
            <div class="widget-content text-right clearfix">
                <i class="fa fa-4x fa-pencil-square-o push-bit pull-left"></i>
                <h4 class="widget-heading"><strong>expedition order</strong></h4>
                <span class="text-muted"></span>
            </div>
        </a>
    </div>
@endif
@if(client_has_addon('pro') && auth()->user()->may('read.point.expedition.downpayment'))
    <div class="col-md-4 col-lg-3">
        <a href="{{url('expedition/point/downpayment')}}" class="widget widget-button">
            <div class="widget-content text-right clearfix">
                <i class="fa fa-4x fa-file-o push-bit pull-left"></i>
                <h4 class="widget-heading"><strong>downpayment</strong></h4>
                <span class="text-muted"></span>
            </div>
        </a>
    </div>
@endif
@if(client_has_addon('basic') && auth()->user()->may('read.point.expedition.invoice'))
    <div class="col-md-4 col-lg-3">
        <a href="{{url('expedition/point/invoice')}}" class="widget widget-button">
            <div class="widget-content text-right clearfix">
                <i class="fa fa-4x fa-fax push-bit pull-left"></i>
                <h4 class="widget-heading"><strong>invoice</strong></h4>
                <span class="text-muted"></span>
            </div>
        </a>
    </div>
@endif
@if(client_has_addon('basic') && auth()->user()->may('read.point.expedition.payment.order'))
    <div class="col-md-4 col-lg-3">
        <a href="{{url('expedition/point/payment-order')}}" class="widget widget-button">
            <div class="widget-content text-right clearfix">
                <i class="fa fa-4x fa-file-text push-bit pull-left"></i>
                <h4 class="widget-heading"><strong>payment order</strong></h4>
                <span class="text-muted"></span>
            </div>
        </a>
    </div>
@endif
