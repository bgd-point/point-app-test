@if(client_has_addon('bumi-shares'))
    @if(auth()->user()->may('menu.bumi.shares'))
    <div class="col-md-4 col-lg-3">
        <a href="{{url('facility/bumi-shares')}}" class="widget widget-button">
            <div class="widget-content text-right clearfix">
                <i class="fa fa-4x fa-book push-bit pull-left"></i>
                <h4 class="widget-heading"><strong>Shares</strong></h4>
                <span class="text-muted">Bumi Shares</span>
            </div>
        </a>
    </div>
    @endif
@endif
