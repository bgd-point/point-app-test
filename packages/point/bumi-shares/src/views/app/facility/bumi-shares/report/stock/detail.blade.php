@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('bumi-shares::app/facility/bumi-shares/_breadcrumb')
            <li><a href="{{url('facility/bumi-shares/report/stock')}}">Stock Report</a></li>
            <li>{{$buy->formulir->form_number}}</li>
        </ul>

        <h2 class="sub-header">Stock Report Detail "{{$buy->formulir->form_number}}"</h2>

        <div class="panel panel-default">
            <div class="panel-body">
                <h3> Shares "{{$shares->name}}"</h3>
                <a class="btn btn-info" href="{{url('facility/bumi-shares/report/stock/detail/export/'.$buy->formulir_id.'/'.$shares->id)}}">Export to excel</a>
                <br>
                <br>
                <div class="table-responsive">
                    @include('bumi-shares::app/facility/bumi-shares/report/stock/_data-detail')
                </div>
            </div>
        </div>
    </div>
@stop