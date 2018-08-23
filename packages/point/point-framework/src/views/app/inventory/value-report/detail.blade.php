@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            <li><a href="{{ url('inventory') }}">Inventory</a></li>
            <li><a href="{{ url('inventory/value-report') }}">Value Report</a></li>
            <li>Detail</li>
        </ul>
        <h2 class="sub-header">Inventory Value Report "{{ $warehouse ? $warehouse->name : 'All' }}"</h2>

        <div class="panel panel-default">
            <div class="panel-body">
                <h1>{{$item->codeName}}</h1>

                <div class="table-responsive">
                    {!! $list_inventory->appends([
                        'search'=>app('request')->get('search'),
                        'date_from'=>app('request')->get('date_from'),
                        'date_to'=>app('request')->get('date_to'),
                        'account'=>app('request')->get('account'),
                        'warehouse_id'=>app('request')->get('warehouse_id')
                        ])->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            @if(!$warehouse) <th>Warehouse</th> @endif
                            <th>Reference</th>
                            <th>Vendor</th>
                            <th>Date</th>
                            <th>I/O</th>
                            <th>Value</th>
                            <th>Total Value</th>
                            <th>Cost of Sales</th>
                            <th>Remaining Stock</th>
                            <th>Remaining Value</th>
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
                            ->orderBy('form_date', '=', 'asc')
                            ->orderBy('formulir_id', '=', 'asc')
                            ->first();
                        $total_quantity = $opening_inventory ? $opening_inventory->total_quantity : 0;
                        $total_value = $opening_inventory ? $opening_inventory->total_value : 0;
                        $cogs = $opening_inventory ? $opening_inventory->cogs : 0;
                        ?>
                        <tr>
                            @if(!$warehouse) <td>{{$opening_inventory ? $opening_inventory->warehouse->name : '-' }}</td> @endif
                            <td>
                                -
                            </td>
                            <td>-</td>
                            <td>{{date_format_view($date_from)}}</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>{{number_format_quantity($cogs)}}</td>
                            <td>{{number_format_quantity($total_quantity)}}</td>
                            <td>{{number_format_quantity($total_value)}}</td>
                        </tr>
                        @foreach($list_inventory as $inventory)
                            <?php $total_quantity += $inventory->quantity ?>
                            <?php $total_value += $inventory->quantity * $inventory->cogs ?>

                            <tr @if($inventory->recalculate == 1) style="background: red;color:white;" @endif>
                                @if(!$warehouse) <td>{{$inventory->warehouse->name}}</td> @endif
                                <?php
                                    $model = $inventory->formulir->formulirable_type;
                                ?>
                                <td>{!! formulir_url($inventory->formulir) !!}</td>
                                <td>
                                    <?php
                                    $model = $inventory->formulir->formulirable_type;
                                    $class = $model::find($inventory->formulir->formulirable_id);
                                    ?>
                                    @if ($class->person)
                                    {{ $class->person->name }}
                                    @endif
                                </td>
                                <td>{{date_format_view($inventory->form_date)}}</td>
                                <td>{{number_format_quantity($inventory->quantity)}}</td>
                                <td>@if($inventory->quantity > 0) {{number_format_quantity($inventory->price)}} @else {{number_format_quantity($inventory->cogs)}} @endif</td>
                                <td>{{number_format_quantity($inventory->quantity * $inventory->price)}}</td>
                                <td>{{number_format_quantity($inventory->cogs)}}</td>
                                <td>{{number_format_quantity($inventory->total_quantity)}}</td>
                                <td>{{number_format_quantity($inventory->total_value)}}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $list_inventory->appends([
                        'search'=>app('request')->get('search'),
                        'date_from'=>app('request')->get('date_from'),
                        'date_to'=>app('request')->get('date_to'),
                        'account'=>app('request')->get('account'),
                        'warehouse_id'=>app('request')->get('warehouse_id')
                        ])->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop
