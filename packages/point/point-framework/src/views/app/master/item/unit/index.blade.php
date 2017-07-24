@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('core::app/master/_breadcrumb')
            <li><a href="{{ url('master/item') }}">Item</a></li>
            <li>Unit</li>
        </ul>

        <h2 class="sub-header">Item</h2>
        @include('framework::app.master.item._menu')

        @include('core::app.error._alert')

        <div class="col-sm-4">
            <div class="panel panel-default">
                <div class="panel-body">
                    <form action="{{url('master/item/unit_master')}}" method="post" class="form-horizontal form-bordered">
                        {!! csrf_field() !!}

                        <fieldset>
                            <legend><i class="fa fa-angle-right"></i> New Unit</legend>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Name*</label>
                                <div class="col-md-12">
                                    <input type="text" name="name" class="form-control" value="{{old('name')}}">
                                </div>
                            </div>
                        </fieldset>

                        <div class="form-group">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-effect-ripple btn-primary">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-sm-8">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="table-responsive">
                        {!! $list_item_unit->render() !!}
                        <table id="list-datatable" class="table table-striped table-bordered">
                            <thead>
                            <tr>
                                <th width="50px" class="text-center"></th>
                                <th>NAME</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list_item_unit as $item_unit)
                                <tr id="list-{{$item_unit->id}}">
                                    <td>
                                        <a href="{{url('master/item/unit_master/'.$item_unit->id.'/edit')}}"
                                           class="btn btn-effect-ripple btn-xs btn-warning"><i class="fa fa-pencil"></i></a>
                                        <a href="javascript:void(0)" data-toggle="tooltip" title="Delete"
                                           class="btn btn-effect-ripple btn-xs btn-danger"
                                           onclick="secureDelete({{$item_unit->id}}, '{{url('master/item/unit_master/delete')}}')">
                                           <i class="fa fa-times"></i></a>
                                    </td>
                                    <td>{{ $item_unit->name }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {!! $list_item_unit->render() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
