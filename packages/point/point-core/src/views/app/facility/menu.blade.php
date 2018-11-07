@extends('core::app.layout')

@section('content')
<div id="page-content">
    <div class="row">
        <div class="col-md-4 col-lg-3">
            <a href="{{url('finance/point/debt-cash')}}" class="widget widget-button">
                <div class="widget-content text-right clearfix">
                    <i class="fa fa-4x fa-desktop push-bit pull-left"></i>
                    <h4 class="widget-heading"><strong>Cash</strong></h4>
                    <span class="text-muted">Using Debt Account</span>
                </div>
            </a>
        </div>
        <div class="col-md-4 col-lg-3">
            <a href="{{url('finance/point/debt-report/cash')}}" class="widget widget-button">
                <div class="widget-content text-right clearfix">
                    <i class="fa fa-4x fa-newspaper-o push-bit pull-left"></i>
                    <h4 class="widget-heading"><strong>Cash Report</strong></h4>
                    <span class="text-muted">Approval Cash Transaction</span>
                </div>
            </a>
        </div>
        @if(auth()->user()->may('manage.monitoring'))
            <div class="col-md-4 col-lg-3">
                <a href="{{url('facility/monitoring')}}" class="widget widget-button">
                    <div class="widget-content text-right clearfix">
                        <i class="fa fa-4x fa-desktop push-bit pull-left"></i>
                        <h4 class="widget-heading"><strong>Monitoring</strong></h4>
                        <span class="text-muted">Facility</span>
                    </div>
                </a>
            </div>
        @endif

        <?php

        $files = \File::files(base_path().'/resources/views/menu/facility');
        foreach ($files as $file) {
            $array = explode('/', $file);
            $view_name = end($array);
            $array = explode('.', $view_name);
            $view_name = $array[0]; ?>
        @include('menu/facility/'.$view_name)
        <?php

        }

        ?>
    </div>
</div>
@stop
