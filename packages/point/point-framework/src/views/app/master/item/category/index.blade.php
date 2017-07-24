@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('core::app/master/_breadcrumb')
            <li><a href="{{ url('master/item') }}">Item</a></li>
            <li>Category</li>
        </ul>

        <h2 class="sub-header">Item</h2>
        @include('framework::app.master.item._menu')

        @include('core::app.error._alert')

        <div class="col-sm-4">
            <div class="panel panel-default">
                <div class="panel-body">
                    <form action="{{url('master/item/category')}}" method="post" class="form-horizontal form-bordered">
                        {!! csrf_field() !!}

                        <fieldset>
                            <legend><i class="fa fa-angle-right"></i> New Category</legend>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Code*</label>
                                <div class="col-md-12">
                                    <input type="text" id="code" name="code" class="form-control"
                                           value="{{old('code')}}">
                                </div>
                            </div>
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
                        {!! $list_item_category->render() !!}
                        <table id="list-datatable" class="table table-striped table-bordered">
                            <thead>
                            <tr>
                                <th width="80px" class="text-center"></th>
                                <th>NAME</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list_item_category as $item_category)
                                <tr id="list-{{$item_category->id}}">
                                    <td>
                                        <a href="{{url('master/item/category/'.$item_category->id.'/edit')}}" class="btn btn-effect-ripple btn-xs btn-warning">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <a href="javascript:void(0)" data-toggle="tooltip" title="Delete" class="btn btn-effect-ripple btn-xs btn-danger" onclick="secureDelete({{$item_category->id}}, '{{url('master/item/category/delete')}}')">
                                           <i class="fa fa-times"></i>
                                        </a>
                                        <a id="link-state-{{$item_category->id}}" href="javascript:void(0)" data-toggle="tooltip" title="{{$item_category->disabled == 0 ? 'disable' : 'enable' }}"     class="btn btn-effect-ripple btn-xs {{$item_category->disabled == 0 ? 'btn-success' : 'btn-default' }}" onclick="state({{$item_category->id}})">
                                            <i id="icon-state-{{$item_category->id}}" class="{{$item_category->disabled == 0 ? 'fa fa-pause' : 'fa fa-play' }}"></i>
                                        </a>
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

@section('scripts')
<script>

function state(index) {
    $.ajax({
        type:'post',
        url: "{{URL::to('master/allocation/state')}}",
        data: {
            index: index
        },
        success: function(result){
            if(result.status === "failed"){
                swal(result.status, result.message);
                return false;
            }
            
            var status = result.data_value == 0 ? 'enable' : 'disable'; 
            $("#link-state-"+index).attr('title', status);
            if(result.data_value == 0 ){
                $("#link-state-"+index).removeClass("btn-default").addClass("btn-success");
                $("#icon-state-"+index).removeClass("fa fa-play").addClass("fa fa-pause");
            } else {
                $("#link-state-"+index).removeClass("btn-success").addClass("btn-default");
                $("#icon-state-"+index).removeClass("fa fa-pause").addClass("fa fa-play");
            } 
                                            
            swal(result.status, result.message,"success");
        }, error: function(e){
            swal('Failed', 'Something went wrong', 'error');
        }
    });
} 

</script>
@stop
