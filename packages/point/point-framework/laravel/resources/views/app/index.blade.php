@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <h2 class="sub-header"><i class="fa fa-user"></i> Vesa <br/><span style="font-size:14px" class="label label-primary label-xs">Virtual Enterprise Smart Assistance</span></h2>
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th colspan="3" class="text-center" style="margin:0 ;padding: 0;background: black;color: white"><h4>Basic Task</h4></th>
                        </tr>
                        <tr>
                            <th></th>
                            <th style="width:150px">Deadline</th>
                            <th>Description</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($list_vesa as $vesa)
                        @if(auth()->user()->may($vesa->permission_slug))
                        <tr>
                            <td style="vertical-align: top"><a href="{{ $vesa->url }}"><i class="fa fa-share-square-o"></i></a></td>
                            <td style="vertical-align: top">{{ date_format_view($vesa->task_deadline) }}</td>
                            <td>{{ $vesa->description }}</td>
                        </tr>
                        @endif
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
