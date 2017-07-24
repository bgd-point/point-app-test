@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('core::app/master/_breadcrumb')
        <li>Fixed Assets Item</li>
    </ul>

    <h2 class="sub-header">Fixed Assets Item</h2>
    @include('framework::app.master.fixed-assets._menu')

    <div class="panel panel-default">
        <div class="panel-body">
            <form action="{{ url('master/fixed-assets-item/') }}" method="get" class="form-horizontal">
                <div class="form-group">
                    <div class="col-sm-3">
                        <input type="text" name="search" class="form-control" placeholder="Search Name..." value="{{\Input::get('search')}}" autofocus>
                    </div>
                    <div class="col-sm-9">
                        <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search</button> 
                    </div>
                </div>
            </form>

            <br/>

            <div class="table-responsive">
                {!! $list_fixed_assets_item->appends(['search'=>app('request')->get('search')])->render() !!}
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th width="75px" class="text-center"></th>
                            <th>NAME</th>
                            <th>USEFUL LIFE</th>
                            <th>SALVAGE VALUE</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($list_fixed_assets_item as $item_fixed_asset)
                            <tr id="list-{{$item_fixed_asset->id}}">
                                <td class="text-center">
                                    <a href="javascript:void(0)" data-toggle="tooltip" title="Delete" class="btn btn-effect-ripple btn-xs btn-danger" onclick="secureDelete({{$item_fixed_asset->id}}, '{{url('master/fixed-assets-item/delete')}}')"><i class="fa fa-times"></i></a>
                                    <a id="link-state-{{$item_fixed_asset->id}}" href="javascript:void(0)" data-toggle="tooltip" title="{{$item_fixed_asset->disabled == 0 ? 'click to disable' : 'click to enable' }}" class="btn btn-effect-ripple btn-xs btn-default" onclick="state({{$item_fixed_asset->id}})">
                                        <i id="icon-state-{{$item_fixed_asset->id}}" class="{{$item_fixed_asset->disabled == 0 ? 'fa fa-toggle-on' : 'fa fa-toggle-off' }}"></i>
                                    </a>
                                </td>
                                <td><a href="{{url('master/fixed-assets-item/'.$item_fixed_asset->id)}}">{{ $item_fixed_asset->codeName }}</a></td>
                                <td>{{ $item_fixed_asset->useful_life }} year</td>
                                <td>{{ number_format_quantity($item_fixed_asset->salvage_value) }}</td>
                            </tr>
                        @endforeach  
                    </tbody> 
                </table>
                {!! $list_fixed_assets_item->appends(['search'=>app('request')->get('search')])->render() !!}
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
        url: "{{URL::to('master/fixed-assets-item/state')}}",
        data: {
            index: index
        },
        success: function(result){
            console.log(result);
            if(result.status === "failed"){
                swal(result.status, result.message);
                return false;
            }
            
            var status = result.data_value == 0 ? 'enable' : 'disable'; 
            $("#link-state-"+index).attr('title', status);
            if(result.data_value == 0 ){
                $("#icon-state-"+index).removeClass("fa fa-toggle-off").addClass("fa fa-toggle-on");
            } else {
                $("#icon-state-"+index).removeClass("fa fa-toggle-on").addClass("fa fa-toggle-off");
            } 
        }, error: function(e){
            swal('Failed', 'Something went wrong','error');
        }
    });
} 

</script>
@stop
