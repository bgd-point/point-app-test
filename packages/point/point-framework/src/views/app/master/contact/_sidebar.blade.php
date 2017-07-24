<div id="page-content-sidebar">
    <!-- Collapsible Navigation -->
    <a href="javascript:void(0)" class="btn btn-block btn-default visible-xs" data-toggle="collapse" data-target="#inner-sidebar">Menu</a>
    <div id="inner-sidebar" class="collapse navbar-collapse remove-padding">
        <!-- Menu -->
        <div class="block-section">
            <ul class="nav nav-pills nav-stacked">
                @if(auth()->user()->may('read.supplier'))
                    <li class=" {{Request::segment(3)=='supplier'?'active':''}}">
                        <a href="{{ url('master/contact/supplier') }}">
                            <strong>Supplier</strong>
                        </a>
                    </li>
                @endif
                @if(auth()->user()->may('read.customer'))
                    <li class=" {{Request::segment(3)=='customer'?'active':''}}">
                        <a href="{{ url('master/contact/customer') }}">
                            <strong>Customer</strong>
                        </a>
                    </li>
                @endif
                @if(auth()->user()->may('read.employee'))
                    <li class=" {{Request::segment(3)=='employee'?'active':''}}">
                        <a href="{{ url('master/contact/employee') }}">
                            <strong>Employee</strong>
                        </a>
                    </li>
                @endif
                @if(auth()->user()->may('read.expedition'))
                    <li class=" {{Request::segment(3)=='expedition'?'active':''}}">
                        <a href="{{ url('master/contact/expedition') }}">
                            <strong>Expedition</strong>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
        <!-- END Menu -->
    </div>
    <!-- END Collapsible Navigation -->
</div>