<div id="sidebar">
    <!-- Sidebar Brand -->
    <div id="sidebar-brand" class="themed-background">
        <a href="/" class="sidebar-title">
            <img src="{{asset('core/assets/img/logo.png')}}" style="height:30px">@if((\Request::segment(3) != 'pos') && (\Request::segment(4) != 'create' )) <span style="text-transform: lowercase !important">BETA</span>@endif
        </a>
    </div>
    <!-- END Sidebar Brand -->

    <!-- Wrapper for scrolling functionality -->
    <div id="sidebar-scroll">
        <!-- Sidebar Content -->
        <div class="sidebar-content">
            <!-- Sidebar Navigation -->
            <ul class="sidebar-nav">
                <li>
                    <a href="{{url('/')}}" class="{{\Request::segment(1)==''?'active':''}}"><i class="gi gi-compass sidebar-nav-icon"></i><span class="sidebar-nav-mini-hide">Dashboard</span></a>
                </li> 
                <li>
                    <a href="{{url(Request::url())}}"><i class="fa fa-refresh sidebar-nav-icon"></i><span class="sidebar-nav-mini-hide">Refresh</span></a>
                </li>

                <li class="sidebar-separator">
                    <i class="fa fa-ellipsis-h"></i>
                </li> 
                
                @if(auth()->user()->may('menu.master') || auth()->user()->id == 1)
                <li id="sidebar-menu-master">
                    <a href="{{url('/master')}}" class="{{\Request::segment(1)=='master'?'active':''}}"><i class="fa fa-briefcase sidebar-nav-icon"></i> <span class="sidebar-nav-mini-hide">Master</span></a>
                </li>
                @endif

                @if(env('CLIENT_BASE') == 'FRAMEWORK')
                    @include('framework::app.include.sidebar')
                @else
                    @include('app.include.sidebar')
                @endif

                @if(auth()->user()->may('menu.facility'))
                <li id="sidebar-menu-facility">
                    <a href="{{url('/facility')}}" class="{{\Request::segment(1)=='facility'?'active':''}}"><i class="fa fa-skyatlas sidebar-nav-icon"></i> <span class="sidebar-nav-mini-hide">Facility</span></a>
                </li> 
                @endif
            </ul>
            <!-- END Sidebar Navigation --> 
        </div>
        <!-- END Sidebar Content -->
    </div>
    <!-- END Wrapper for scrolling functionality -->

    <!-- Sidebar Extra Info -->
    <div id="sidebar-extra-info" class="sidebar-content sidebar-nav-mini-hide"> 
        <div class="text-center"> 
            <small><span id="year-copy"></span> &copy; {{env('SOFTWARE_NAME')}}</small>
        </div>
    </div>
    <!-- END Sidebar Extra Info -->
</div>
