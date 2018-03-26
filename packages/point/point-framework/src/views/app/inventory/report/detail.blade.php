@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        <li><a href="{{ url('inventory') }}">Inventory</a></li>
        <li><a href="{{ url('inventory/report') }}">Report</a></li>
        <li>Detail</li>
    </ul>
    <h2 class="sub-header">Inventory Report "{{ $warehouse ? $warehouse->name : 'All' }}"</h2>

    <div class="panel panel-default">
        <div class="panel-body">
            <h1>{{$item->codeName}}</h1>
            <a class="btn btn-effect-ripple btn-effect-ripple btn-info button-export pull-left" onclick="exportExcel()"> Export to excel</a>
            <div id="preloader" style="display:none; margin-top:5px;float: left;position: relative; margin-left:10px">
                <i class="fa fa-spinner fa-spin" style="font-size:24px;"></i>
            </div>
            <input type="hidden" id="date-from" value="{{$date_from}}">
            <input type="hidden" id="date-to" value="{{$date_to}}">
            <input type="hidden" id="item-id" value="{{$item->id}}">
            <input type="hidden" id="warehouse-id" value="{{$warehouse ? $warehouse->id : 0}}">

            <br><br>
            <div class="table-responsive">
                {!! $list_inventory->render() !!}
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            @if(!$warehouse) <th>Warehouse</th> @endif
                            <th>Reference</th>
                            <th>Date</th>
                            <th>Stock<br/> <span style="font-size:12px"> ({{date_format_view($date_from)}}) - ({{date_format_view($date_to)}})</th>
                            <th>Accumulation Stock</th>
                        </tr>
                    </thead>
                    <tbody> 
                        <?php
                        $opening_inventory = \Point\Framework\Models\Inventory::where('item_id', '=', $item->id)
                                ->where('form_date', '<', $date_from)
                                ->where(function ($query) use ($warehouse) {
                                    if ($warehouse) {
                                        $query->where('warehouse_id', '=', $warehouse->id);
                                    }
                                })
                                ->orderBy('form_date', '=', 'desc')
                                ->orderBy('formulir_id', '=', 'asc')
                                ->first();
                        $total_quantity = $opening_inventory ? $opening_inventory->total_quantity : 0;

                        ?>
                        <tr>
                            @if(!$warehouse) <td>{{$opening_inventory ? $opening_inventory->warehouse->name : '-' }}</td> @endif
                            <td><b>Opening Stock</b></td>
                            <td>{{date_format_view($date_from)}}</td>
                            <td>-</td>
                            <td>{{number_format_quantity($total_quantity)}}</td>
                        </tr>
                        @foreach($list_inventory as $inventory)
                        <?php $total_quantity += $inventory->quantity ?>
                            <tr>
                                @if(!$warehouse) <td>{{$inventory->warehouse->name}}</td> @endif
                                <td>{!! formulir_url($inventory->formulir) !!}</td>
                                <td>{{date_format_view($inventory->form_date)}}</td>
                                <td>{{number_format_quantity($inventory->quantity)}}</td>
                                <td>{{number_format_quantity($total_quantity)}}</td>
                            </tr>
                        @endforeach
                        @if($list_inventory->count())
                        <tr>
                            <td @if(!$warehouse) colspan="2"> @endif<b>End Stock</b></td>
                            <td>{{date_format_view($date_to)}}</td>
                            <td>-</td>
                            <td>{{number_format_quantity($total_quantity)}}</td>
                        </tr>
                        @endif
                    </tbody> 
                </table>
                {!! $list_inventory->render() !!}
            </div>
        </div>
    </div>  
</div>
@stop

@section('scripts')
<script type="text/javascript">
    function exportExcel() {
        var date_from = $("#date-from").val();
        var date_to = $("#date-to").val();
        var warehouse_id = $("#warehouse-id").val();
        var item_id = $("#item-id").val();
        $("#preloader").fadeIn();
        $(".button-export").addClass('disabled');
        $.ajax({
            url: '{{url("inventory/report/export/detail")}}',
            data: {
                date_from: date_from,
                date_to: date_to,
                warehouse_id: warehouse_id,
                item_id: item_id
            },
            success: function(result) {
                $("#preloader").fadeOut();
                $(".button-export").removeClass('disabled');
                notification('export item data success, please check your email in a few moments');
            }, error:  function (result) {
                $("#preloader").fadeOut();
                $(".button-export").removeClass('disabled');
                notification('export item data failed, please try again');
            }

        });
    }
</script>
@stop

