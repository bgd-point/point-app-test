@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('core::app/master/_breadcrumb')
        <li><a href="{{ url('master/item') }}">Item</a></li>
        <li><a href="{{ url('master/item/category') }}">Category</a></li>
        <li><a href="{{ url('master/item/category/'.$item_category->id) }}">{{$item_category->name}}</a></li>
        <li>Edit</li>
    </ul>

    <h2 class="sub-header">Item</h2>
    @include('framework::app.master.item._menu')
    @include('core::app.error._alert')

    <div class="col-sm-4">
        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('master/item/category/'.$item_category->id)}}" method="post" class="form-horizontal form-bordered">
                    {!! csrf_field() !!}
                    <input name="_method" type="hidden" value="PUT">

                    <fieldset>
                        <legend><i class="fa fa-angle-right"></i> Edit Category</legend>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Code*</label>
                            <div class="col-md-12">
                                <input type="text" id="code" name="code" class="form-control" value="{{$item_category->code}}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Name*</label>
                            <div class="col-md-12">
                                <input type="text" name="name" class="form-control" value="{{$item_category->name}}">
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
                    {!! $list_item_category->render() !!}
                    <table id="list-datatable" class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th width="50px" class="text-center"></th>
                            <th>NAME</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($list_item_category as $item_category)
                            <tr id="list-{{$item_category->id}}">
                                <td>
                                    <a href="{{url('master/item/category/'.$item_category->id.'/edit')}}"
                                       class="btn btn-effect-ripple btn-xs btn-warning"><i class="fa fa-pencil"></i></a>
                                    <a href="javascript:void(0)" data-toggle="tooltip" title="Delete"
                                       class="btn btn-effect-ripple btn-xs btn-danger"
                                       onclick="secureDelete({{$item_category->id}}, '{{url('master/item/category/delete')}}')"><i
                                                class="fa fa-times"></i></a>
                                </td>
                                <td>{{ $item_category->codeName }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $list_item_category->render() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@stop
