@extends('core::app.layout')

@section('content')
    <div id="page-content">
        @if(count($process) > 0)
            <ul class="breadcrumb breadcrumb-top">
                <li><a href="{{ url('manufacture') }}">Manufacture</a></li>
                <li>Process</li>
            </ul>
            <h2 class="sub-header">Process {{ $process->name }}</h2>

            @if(client_has_addon("basic") && auth()->user()->may("create.point.manufacture.input"))
                <div class="col-md-4 col-lg-3">
                    <a href="{{url('manufacture/point/process-io/'. $process->id .'/input' )}}"
                       class="widget widget-button">
                        <div class="widget-content text-right clearfix">
                            <i class="fa fa-4x fa-toggle-on push-bit pull-left"></i>
                            <h4 class="widget-heading"><strong>Input</strong></h4>
                            <span class="text-muted"></span>
                        </div>
                    </a>
                </div>
            @endif
            @if(client_has_addon("basic") && auth()->user()->may("create.point.manufacture.output"))
                <div class="col-md-4 col-lg-3">
                    <a href="{{url('manufacture/point/process-io/'. $process->id .'/output' )}}"
                       class="widget widget-button">
                        <div class="widget-content text-right clearfix">
                            <i class="fa fa-4x fa-toggle-off push-bit pull-left"></i>
                            <h4 class="widget-heading"><strong>Output</strong></h4>
                            <span class="text-muted"></span>
                        </div>
                    </a>
                </div>
            @endif
        @endif
    </div>
@stop
