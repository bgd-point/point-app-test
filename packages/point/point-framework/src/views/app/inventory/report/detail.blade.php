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
                                ->where(function($query) use ($warehouse) {
                                    if ($warehouse) {
                                        $query->where('warehouse_id', '=', $warehouse->id);
                                    }
                                })
                                ->orderBy('form_date', '=', 'desc')
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
                                <td>{{$inventory->formulir->form_number}}</td>
                                <td>{{date_format_view($inventory->form_date)}}</td>
                                <td>{{number_format_quantity($inventory->quantity)}}</td>
                                <td>{{number_format_quantity($inventory->total_quantity)}}</td>
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
