@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-manufacture::app.manufacture.point.process._breadcrumb')
            <li>show</li>
        </ul>
        <h2 class="sub-header">Manufacture | Process"</h2>
        @include('point-manufacture::app.manufacture.point.process._menu')

        <div class="block full">
            <!-- Block Tabs Title -->
            <div class="block-title">
                <ul class="nav nav-tabs" data-toggle="tabs">
                    <li class="active"><a href="#block-tabs-home">Form</a></li>
                    <li><a href="#block-tabs-profile">History</a></li>
                    <li><a href="#block-tabs-settings"><i class="gi gi-settings"></i></a></li>
                </ul>
            </div>
            <!-- END Block Tabs Title -->

            <!-- Tabs Content -->
            <div class="tab-content">
                <div class="tab-pane active" id="block-tabs-home">

                    <div class="form-horizontal form-bordered">
                        <div class="form-group">
                            <label class="col-md-3 control-label">name</label>

                            <div class="col-md-6 content-show">{{$process->name}}</div>
                        </div>
                    </div>
                    <div class="form-horizontal form-bordered">
                        <div class="form-group">
                            <label class="col-md-3 control-label">notes</label>

                            <div class="col-md-6 content-show">{{$process->notes}}</div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="block-tabs-profile">
                    <div class="table-responsive">
                        <table id="list-table" class="table table-striped table-bordered">
                            <thead>
                            <tr>
                                <th>Date</th>
                                <th>User</th>
                                <th>Key</th>
                                <th>Old Value</th>
                                <th>New Value</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($histories as $history)
                                <tr id="{{$history->id}}">
                                    <td>{{ \DateHelper::formatView($history->created_at, true) }}</td>
                                    <td>{{ $history->user->name }}</td>
                                    <td>{{ $history->key }}</td>
                                    <td>{{ $history->old_value }}</td>
                                    <td>{{ $history->new_value }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane" id="block-tabs-settings">
                    <a href="{{url('manufacture/point/process/'.$process->id.'/edit')}}"
                       class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> edit</a>
                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                       onclick="secureDelete({{$process->id}}, '{{url('manufacture/point/process/delete')}}', '{{url('manufacture/point/process')}}') "><i
                                class="fa fa-times"></i> delete</a>
                </div>
            </div>
            <!-- END Tabs Content -->
        </div>
    </div>
@stop
