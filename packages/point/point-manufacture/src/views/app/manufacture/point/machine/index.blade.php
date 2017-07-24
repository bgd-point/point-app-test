@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            <li><a href="{{ url('manufacture') }}">Manufacture</a></li>
            <li>Machine</li>
        </ul>
        <h2 class="sub-header">Manufacture | Machine</h2>
        @include('point-manufacture::app.manufacture.point.machine._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{ url('manufacture/point/machine') }}" method="get" class="form-horizontal">
                    <div class="form-group">
                        <div class="col-sm-3">
                            <input type="text" name="search" class="form-control" placeholder="Search Name..."
                                   value="{{\Input::get('search')}}" autofocus>
                        </div>
                        <div class="col-sm-9">
                            <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i
                                        class="fa fa-search"></i> search
                            </button>
                        </div>
                    </div>
                </form>

                <br/>

                <div class="table-responsive">
                    {!! $machine_list->appends(['search' => app('request')->get('search')])->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th width="100px" class="text-center"></th>
                            <th>NAME</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($machine_list as $machine)
                            <tr id="list-{{$machine->id}}">
                                <td class="text-center">
                                    <a href="{{ url('manufacture/point/machine/'.$machine->id) }}" data-toggle="tooltip"
                                       title="show" class="btn btn-effect-ripple btn-xs btn-info"><i
                                                class="fa fa-file"></i></a>
                                    <a href="javascript:void(0)" data-toggle="tooltip" title="delete"
                                       class="btn btn-effect-ripple btn-xs btn-danger"
                                       onclick="secureDelete({{$machine->id}}, '{{url('manufacture/point/machine/delete')}}', '{{url('manufacture/point/machine')}}' )"><i
                                                class="fa fa-times"></i></a>
                                </td>
                                <td>
                                    <a href="{{ url('manufacture/point/machine/'.$machine->id) }}">{{ $machine->name }}</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $machine_list->appends(['search' => app('request')->get('search')])->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop
