@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-manufacture::app.manufacture.point.process._breadcrumb')
            <li>Edit</li>
        </ul>
        <h2 class="sub-header"> Manufacture | Process</h2>
        @include('point-manufacture::app.manufacture.point.process._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('manufacture/point/process/'.$process->id)}}" method="post"
                      class="form-horizontal form-bordered">
                    {!! csrf_field() !!}
                    <input name="_method" type="hidden" value="PUT">

                    <div class="form-group">
                        <label class="col-md-3 control-label">name *</label>

                        <div class="col-md-6">
                            <input type="text" name="name" value="{{$process->name}}" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">notes</label>

                        <div class="col-md-6">
                            <input type="text" name="notes" class="form-control" value="{{$process->notes}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-3">
                            <button type="submit" class="btn btn-effect-ripple btn-primary">submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
