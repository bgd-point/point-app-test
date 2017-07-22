@if(client_has_addon('bumi-deposit'))

    @if(auth()->user()->may('menu.bumi.deposit'))
        <div class="col-md-4 col-lg-3">
            <a href="{{ url( 'facility/bumi-deposit' ) }}" class="widget widget-button">
                <div class="widget-content text-right clearfix">
                    <i class="fa fa-4x fa-book push-bit pull-left"></i>
                    <h4 class="widget-heading"><strong>Deposit</strong></h4>
                    <span class="text-muted">Bumi Deposit</span>
                </div>
            </a>
        </div>
    @endif

@endif
