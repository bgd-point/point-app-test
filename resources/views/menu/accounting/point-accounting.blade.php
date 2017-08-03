    @if(client_has_addon('basic') && auth()->user()->may('read.point.accounting.memo.journal'))
    <div class="col-md-4 col-lg-3">
        <a href="{{url('accounting/point/memo-journal')}}" class="widget widget-button">
            <div class="widget-content text-right clearfix">
                <i class="gi fa-4x gi-book push-bit pull-left"></i>
                <h4 class="widget-heading"><strong>Memo Journal</strong></h4>
                <span class="text-muted"></span>
            </div>
        </a>
    </div>
    @endif

    <div class="col-md-4 col-lg-3">
        <a href="{{url('accounting/point/cut-off')}}" class="widget widget-button">
            <div class="widget-content text-right clearfix">
                <i class="gi fa-4x gi-book push-bit pull-left"></i>
                <h4 class="widget-heading"><strong>Cut Off</strong></h4>
                <span class="text-muted"></span>
            </div>
        </a>
    </div>


