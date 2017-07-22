@if(client_has_addon('ksp'))
    @if(auth()->user()->may('menu.ksp'))
    <div class="col-md-4 col-lg-3">
        <a href="{{url('facility/ksp')}}" class="widget widget-button">
            <div class="widget-content text-right clearfix">
                <i class="fa fa-4x fa-book push-bit pull-left"></i>
                <h4 class="widget-heading"><strong>KSP</strong></h4>
                <span class="text-muted">Koperasi Simpan Pinjam</span>
            </div>
        </a>
    </div>
    @endif
@endif
