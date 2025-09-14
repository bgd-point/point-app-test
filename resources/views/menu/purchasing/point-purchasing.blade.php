@if(client_has_addon('basic'))
    @if(auth()->user()->may('menu.point.purchasing'))
    <div class="col-md-4 col-lg-3">
        <a href="{{url('purchasing/point')}}" class="widget widget-button">
            <div class="widget-content text-right clearfix">
                <i class="fa fa-4x fa-check-square-o push-bit pull-left"></i>
                <h4 class="widget-heading"><strong>Goods Purchase</strong></h4>
                <span class="text-muted"></span>
            </div>
        </a>
    </div>
    @endif
@endif
@if(client_has_addon('basic'))
    @if(auth()->user()->may('menu.point.purchasing.service'))
    @if(request()->get('database_name') == 'p_kbretail' 
    || request()->get('database_name') == 'p_bumiananta'
    || request()->get('database_name') == 'p_big'
    || request()->get('database_name') == 'p_bbl')
        <div class="col-md-4 col-lg-3">
            <a href="{{url('purchasing/point/service')}}" class="widget widget-button">
                <div class="widget-content text-right clearfix">
                    <i class="fa fa-4x fa-briefcase push-bit pull-left"></i>
                    <h4 class="widget-heading"><strong>Service Purchase</strong></h4>
                    <span class="text-muted"></span>
                </div>
            </a>
        </div>
    @endif
    @endif
@endif
@if(client_has_addon('basic'))
    @if(auth()->user()->may('menu.point.purchasing.fixed.assets'))
    <!-- <div class="col-md-4 col-lg-3">
        <a href="{{url('purchasing/point/fixed-assets')}}" class="widget widget-button">
            <div class="widget-content text-right clearfix">
                <i class="fa fa-4x fa-diamond push-bit pull-left"></i>
                <h4 class="widget-heading"><strong>Fixed Assets</strong></h4>
                <span class="text-muted"></span>
            </div>
        </a>
    </div> -->
    @endif
@endif
