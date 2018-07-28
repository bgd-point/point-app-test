@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('core::app/master/_breadcrumb')
        <li>Item</li>
    </ul>

    <h2 class="sub-header">Item</h2>
    @include('framework::app.master.item._menu')

    <div class="panel panel-default">
        <div class="panel-body">
            <form action="{{ url('master/item/') }}" method="get" class="form-horizontal">
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
                        <a class="btn btn-effect-ripple btn-effect-ripple btn-info button-export" onclick="exportExcel()"> Export to excel</a>
                        <div id="preloader" style="display:none; margin-top:5px; float: left;position: relative;margin-top: -29px;margin-left: 250px;">
                            <i class="fa fa-spinner fa-spin" style="font-size:24px;"></i>
                        </div>
                    </div>
                </div>
            </form>

            <br/>

            <div class="table-responsive">
                {!! $list_item->appends(['status'=>app('request')->get('status'), 'search'=>app('request')->get('search')])->render() !!}
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th width="75px" class="text-center"></th>
                            <th>NAME</th>
                            <th>ACCOUNT</th>
                            <th>NOTES</th>
                            @if(auth()->user()->may('read.point.sales.pos.pricing'))
                                <th style="text-align: right;">COST OF GOOD SALES</th>
                            @endif
                            <th style="text-align: right;">PRICE</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($list_item as $key=>$item)
                            <tr id="list-{{$item->id}}">
                                <td class="text-center">
                                    <a href="javascript:void(0)" data-toggle="tooltip" title="Delete" class="btn btn-effect-ripple btn-xs btn-danger" onclick="secureDelete({{$item->id}}, '{{url('master/item/delete')}}')"><i class="fa fa-times"></i></a>
                                    <a id="link-state-{{$item->id}}" href="javascript:void(0)" data-toggle="tooltip" title="{{$item->disabled == 0 ? 'click to disable' : 'click to enable' }}" class="btn btn-effect-ripple btn-xs btn-default" onclick="state({{$item->id}})">
                                        <i id="icon-state-{{$item->id}}" class="{{$item->disabled == 0 ? 'fa fa-pause' : 'fa fa-play' }}"></i>
                                    </a>
                                </td>
                                <td><a href="{{url('master/item/'.$item->id)}}">{{ $item->codeName }}</a></td>
                                <td>{{ $item->accountAsset->name }}</td>
                                <td>{{ $item->notes }}</td>
                                @if(auth()->user()->may('read.point.sales.pos.pricing'))
                                    <td style="min-width: 225px; text-align: right;">{{ number_format_price($item->averageCostOfSales(\Carbon::now())) }}</td>
                                @endif
                                <td style="min-width: 225px; text-align: right;">
                                    @forelse($item->pricing AS $key=>$pricing)
                                        <span style="display: block;">
                                            @if($key === 0)
                                                {{$pricing->person_group_name}} {{ number_format_price($pricing->price ? $pricing->price * (100 - $pricing->discount) / 100 : 0) }}
                                            @elseif($item->pricing[$key-1]->person_group_id !== $pricing->person_group_id)
                                                {{$pricing->person_group_name}} {{ number_format_price($pricing->price ? $pricing->price * (100 - $pricing->discount) / 100 : 0) }}
                                            @endif
                                        </span>
                                    @empty
                                    NOT SET
                                    @endforelse
                                </td>
                            </tr>
                        @endforeach  
                    </tbody> 
                </table>
                {!! $list_item->appends(['status'=>app('request')->get('status'), 'search'=>app('request')->get('search')])->render() !!}
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
            url: "{{URL::to('master/item/state')}}",
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
                    $("#icon-state-"+index).removeClass("fa fa-play").addClass("fa fa-pause");
                } else {
                    $("#icon-state-"+index).removeClass("fa fa-pause").addClass("fa fa-play");
                } 
            }, error: function(e){
                swal('Failed', 'Something went wrong','error');
            }
        });
    } 

    function selectData() {
        var status = $("#status option:selected").val();
        var search = $("#search").val();
        var url = '{{url()}}/master/item/?status='+status+'&search='+search;
        location.href = url;
    }

    function exportExcel() {
        var status = $("#status option:selected").val();
        var search = $("#search").val();
        $("#preloader").fadeIn();
        $(".button-export").addClass('disabled');
        $.ajax({
            url: '{{url("master/item/export/")}}',
            data: {
                status: status,
                search: search
            },
            success: function (data) {
                console.log(data);
                if (data.status == 'success') {
                    $("#preloader").fadeOut();
                    $(".button-export").removeClass('disabled');
                    notification('export item data success, please check your email in a few moments');
                }
            }, error:  function (data) {
                $("#preloader").fadeOut();
                $(".button-export").removeClass('disabled');
                notification('export item data failed, please try again');
            }

        });
    }

    
</script>
@stop
