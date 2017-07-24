@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('core::app/master/_breadcrumb')
        <li>Service</li>
    </ul>

    <h2 class="sub-header">Service</h2>
    @include('framework::app.master.service._menu')

    <div class="panel panel-default">
        <div class="panel-body">
            <form action="{{ url('master/service') }}" method="get" class="form-horizontal">
                <div class="form-group">
                    <div class="col-sm-3">
                        <select class="selectize" name="status" id="status" onchange="selectData()">
                            <option value="0" @if(\Input::get('status')== 0) selected @endif>Enable</option>                            
                            <option value="1" @if(\Input::get('status')== 1) selected @endif>Disabled</option>                            
                        </select>
                    </div>
                    <div class="col-sm-3">
                        <input type="text" name="search" id="search" class="form-control" placeholder="Search Name..." value="{{\Input::get('search')}}" autofocus>
                    </div>
                    <div class="col-sm-3">
                        <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search</button> 
                    </div>
                </div>
            </form>

            <br/>

            <div class="table-responsive">
            {!! $list_service->appends(['status'=>app('request')->get('status'), 'search'=>app('request')->get('search')])->render() !!}
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($list_service as $service)
                        <tr id="list-{{$service->id}}">
                            <td>
                                <a href="{{url('master/service/'.$service->id.'/edit')}}" class="btn btn-effect-ripple btn-xs btn-warning"><i class="fa fa-pencil"></i></a>
                                <a href="javascript:void(0)" data-toggle="tooltip" title="Delete" class="btn btn-effect-ripple btn-xs btn-danger" onclick="secureDelete({{$service->id}}, '{{url('master/service/delete')}}')"><i class="fa fa-times"></i></a>
                                <a id="link-state-{{$service->id}}" href="javascript:void(0)" data-toggle="tooltip" 
                                title="{{$service->disabled == 0 ? 'disable' : 'enable' }}" 
                                class="btn btn-effect-ripple btn-xs {{$service->disabled == 0 ? 'btn-success' : 'btn-default' }}" 
                                onclick="state({{$service->id}})">
                                <i id="icon-state-{{$service->id}}" class="{{$service->disabled == 0 ? 'fa fa-pause' : 'fa fa-play' }}"></i></a>
                            </td>
                            <td><a href="{{url('master/service/'.$service->id)}}">{{ $service->name }}</a></td>
                            <td>{{ number_format_price($service->price) }}</td>
                            <td>{{ $service->notes }}</td>
                        </tr>
                        @endforeach  
                    </tbody> 
                </table>
               {!! $list_service->appends(['status'=>app('request')->get('status'), 'search'=>app('request')->get('search')])->render() !!}
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
        url: "{{URL::to('master/service/state')}}",
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


function selectData() {
    var status = $("#status option:selected").val();
    var search = $("#search").val();
    var url = '{{url()}}/master/service/?status='+status+'&search='+search;
    location.href = url;
}
</script>
@stop
