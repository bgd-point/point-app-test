<div id="page-content-sidebar"> 
    <!-- Collapsible Navigation -->
    <a href="javascript:void(0)" class="btn btn-block btn-default visible-xs" data-toggle="collapse" data-target="#inner-sidebar">Menu</a>
    <div id="inner-sidebar" class="collapse navbar-collapse remove-padding">
        <!-- Menu -->
        <div class="block-section">
            <h4 class="inner-sidebar-header">
                Settings
            </h4>
            <ul class="nav nav-pills nav-stacked">
                <li class=" {{Request::segment(2)==''?'active':''}}">
                    <a href="{{ url('settings') }}">
                        <strong>Themes</strong>
                    </a>
                </li>
                @if(\Point\Core\Models\Setting::userChangePasswordAllowed() == "true")
                <li class=" {{Request::segment(2)=='password'?'active':''}}">
                    <a href="{{ url('settings/password') }}">
                        <strong>Password</strong>
                    </a>
                </li>
                @endif
                <li class=" {{Request::segment(2)=='notification'?'active':''}}">
                    <a href="{{ url('settings/notification') }}">
                        <strong>Notification</strong>
                    </a>
                </li>
            </ul>
            @if(auth()->user()->may('menu.setting'))
            <h4 class="inner-sidebar-header">
                Administrator
            </h4>

            <ul class="nav nav-pills nav-stacked">
                <li class=" {{Request::segment(2)=='config'?'active':''}}">
                    <a href="{{ url('settings/config') }}">
                        <strong>Config</strong>
                    </a>
                </li>
                <li class=" {{Request::segment(2)=='end-notes'?'active':''}}">
                    <a href="{{ url('settings/end-notes') }}">
                        <strong>End Notes</strong>
                    </a>
                </li>
                <li class=" {{Request::segment(2)=='logo'?'active':''}}">
                    <a href="{{ url('settings/logo') }}">
                        <strong>Company Logo</strong>
                    </a>
                </li>
                @if(app('request')->input('database_name') == 'p_test')
                <li class=" {{Request::segment(2)=='reset-database'?'active':''}}">
                    <a href="{{ url('settings/reset-database') }}">
                        <strong>Reset Database</strong>
                    </a>
                </li>
                @endif
            </ul>
            @endif
        </div>
        <!-- END Menu -->
    </div>
    <!-- END Collapsible Navigation -->
</div>
