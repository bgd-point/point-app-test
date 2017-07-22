@extends('core::app.layout')

@section('content')
    <div id="page-content">

        <h2 class="sub-header">Master</h2>

        <div class="row">
            @if(client_has_addon('basic') && auth()->user()->may('read.point.manufacture.machine'))
                <div class="col-md-4 col-lg-3">
                    <a href="{{url('manufacture/point/machine')}}" class="widget widget-button">
                        <div class="widget-content text-right clearfix">
                            <i class="fa fa-4x fa-tachometer push-bit pull-left"></i>
                            <h4 class="widget-heading"><strong>Machine</strong></h4>
                            <span class="text-muted">machine used on manufacture process</span>
                        </div>
                    </a>
                </div>
            @endif

            @if(client_has_addon('basic') && auth()->user()->may('read.point.manufacture.process'))
                <div class="col-md-4 col-lg-3">
                    <a href="{{url('manufacture/point/process')}}" class="widget widget-button">
                        <div class="widget-content text-right clearfix">
                            <i class="fa fa-4x fa-sort-amount-asc push-bit pull-left"></i>
                            <h4 class="widget-heading"><strong>Process</strong></h4>
                            <span class="text-muted">steps on manufacture</span>
                        </div>
                    </a>
                </div>
            @endif

            @if(client_has_addon('pro') && auth()->user()->may('read.point.manufacture.formula'))
                <div class="col-md-4 col-lg-3">
                    <a href="{{url('manufacture/point/formula')}}" class="widget widget-button">
                        <div class="widget-content text-right clearfix">
                            <i class="fa fa-4x fa-sort-alpha-asc push-bit pull-left"></i>
                            <h4 class="widget-heading"><strong>Formula</strong></h4>
                            <span class="text-muted">ingredient used to create a product</span>
                        </div>
                    </a>
                </div>
            @endif

        </div>

        @if(client_has_addon("basic") && auth()->user()->may("create.point.manufacture.input"))

            <?php $process_list = Point\PointManufacture\Models\Process::active()->get(); ?>

            @if(count($process_list) > 0)
                <h2 class="sub-header">Process</h2>
                <div class="row">
                    @foreach($process_list as $process)
                        <div class="col-md-4 col-lg-3">
                            <a href="{{url('manufacture/point/process-io/'. $process->id)}}"
                               class="widget widget-button">
                                <div class="widget-content text-right clearfix">
                                    <i class="fa fa-4x fa-cogs push-bit pull-left"></i>
                                    <h4 class="widget-heading"><strong>Process {{$process->name}}</strong></h4>
                                    <span class="text-muted">{{$process->notes}}</span>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif

        @endif

    </div>
@stop




