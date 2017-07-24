@extends('core::app.layout')

@section('content')
<div id="page-content">
    <div class="row">
        @if(auth()->user()->may('read.user'))
        <div class="col-md-4 col-lg-3">
            <a href="{{url('master/user')}}" class="widget widget-button">
                <div class="widget-content text-right clearfix">
                    <i class="fa fa-4x fa-user push-bit pull-left"></i>
                    <h4 class="widget-heading"><strong>User</strong></h4>
                    <span class="text-muted">Manage user, role, and permission</span>
                </div>
            </a>
        </div>
        @endif
        <?php

        $files = \File::files(base_path().'/resources/views/menu/master');
        foreach ($files as $file) {
            $array = explode('/', $file);
            $view_name = end($array);
            $array = explode('.', $view_name);
            $view_name = $array[0]; ?>
        @include('menu/master/'.$view_name)
        <?php

        }

        ?>
    </div>
</div>
@stop

