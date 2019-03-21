@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            <li><a href="{{ url('inventory') }}">Inventory</a></li>
            <li>Value Report</li>
        </ul>
        <h2 class="sub-header">Inventory Value Report "{{ $search_warehouse ? $search_warehouse->name : 'All'}}"</h2>

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{ url('inventory/value-report') }}" method="get" class="form-horizontal">
                    <div class="form-group">
                        <div class="col-sm-4">
                            <div class="input-group input-daterange" data-date-format="{{date_format_masking()}}">
                                <input type="text" name="date_from" id="date_from" class="form-control date input-datepicker" placeholder="From"  value="{{\Input::get('date_from') ? \Input::get('date_from') : date(date_format_get(), strtotime($date_from))}}">
                                <span class="input-group-addon"><i class="fa fa-chevron-right"></i></span>
                                <input type="text" name="date_to" id="date_to" class="form-control date input-datepicker" placeholder="To" value="{{\Input::get('date_to') ? \Input::get('date_to') : date(date_format_get(), strtotime($date_to))}}">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <select name="warehouse_id" id="warehouse_id" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                <option value="0" @if($search_warehouse) selected @endif>All Warehouses</option>
                                @foreach($list_warehouse as $warehouse)
                                    <option
                                        value="{{$warehouse->id}}"
                                        @if( $search_warehouse && $search_warehouse->id == $warehouse->id) selected @endif>
                                        {{$warehouse->codeName}}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <select name="account" class="selectize" style="width: 100%;" data-placeholder="Choose account inventory..">
                                <option value="0" selected>All Accounts</option>
                                @foreach($list_coa as $c)
                                    <option
                                        value="{{$c->id}}"
                                        @if($coa == $c->id) selected @endif>
                                        {{ $c->coa_number }} {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-sm-4">
                            <input type="text" name="search" id="search" class="form-control" placeholder="Search Item..." value="{{\Input::get('search')}}" value="{{\Input::get('search') ? \Input::get('search') : ''}}">
                        </div>
                        <div class="col-sm-8">
                            <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search</button>
                            @if(access_is_allowed_to_view('export.inventory.value.report'))
                                <a class="btn btn-effect-ripple btn-effect-ripple btn-info button-export" id="btn-excel">Export to excel</a>
                            @endif
                        </div>
                    </div>
                </form>

                <br/>

                <div class="table-responsive">
                    {!! $inventory->appends([
                        'search'=>app('request')->get('search'),
                        'date_from'=>app('request')->get('date_from'),
                        'date_to'=>app('request')->get('date_to'),
                        'account'=>app('request')->get('account'),
                        'warehouse_id'=>app('request')->get('warehouse_id')
                        ])->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th>Nomer Akun</th>
                            <th>Keterangan</th>
                            <th>Item</th>
                            <th>Unit</th>
                            <th colspan="2">Opening Stock <br/> <span style="font-size:12px">({{date_format_view($date_from)}})</span></th>
                            <th colspan="2">Stock In <br/> <span style="font-size:12px"> ({{date_format_view($date_from)}}) - ({{date_format_view($date_to)}})</th>
                            <th colspan="2">Stock Out <br/> <span style="font-size:12px"> ({{date_format_view($date_from)}}) - ({{date_format_view($date_to)}})</th>
                            <th colspan="3">Closing Stock <br/> <span style="font-size:12px"> ({{date_format_view($date_to)}})</th>
                        </tr>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th style="text-align: center">QTY</th>
                            <th style="text-align: center">VALUE</th>
                            <th style="text-align: center">QTY</th>
                            <th style="text-align: center">VALUE</th>
                            <th style="text-align: center">QTY</th>
                            <th style="text-align: center">VALUE</th>
                            <th style="text-align: center">QTY</th>
                            <th style="text-align: center">LAST BUY PRICE</th>
                            <th style="text-align: center">VALUE</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $total_closing_value = 0; ?>
                        @foreach($inventory as $item)
                            <?php
                            if ($search_warehouse) {
                                $opening_stock = inventory_get_opening_stock($date_from, $item->item_id, $search_warehouse->id);
                                $stock_in = inventory_get_stock_in($date_from, $date_to, $item->item_id, $search_warehouse->id);
                                $stock_out = inventory_get_stock_out($date_from, $date_to, $item->item_id, $search_warehouse->id);
                                $closing_stock = inventory_get_closing_stock($date_from, $date_to, $item->item_id, $search_warehouse->id);
                                $opening_value = inventory_get_opening_value($date_from, $item->item_id, $search_warehouse->id);
                                $value_in = inventory_get_value_in($date_from, $date_to, $item->item_id, $search_warehouse->id);
                                $value_out = inventory_get_value_out($date_from, $date_to, $item->item_id, $search_warehouse->id);
                                $closing_value = inventory_get_closing_value($date_from, $date_to, $item->item_id, $search_warehouse->id);
                                $warehouse_id = $search_warehouse->id;
                            } else {
                                $opening_stock = inventory_get_opening_stock_all($date_from, $item->item_id);
                                $stock_in = inventory_get_stock_in_all($date_from, $date_to, $item->item_id);
                                $stock_out = inventory_get_stock_out_all($date_from, $date_to, $item->item_id);
                                $closing_stock = inventory_get_closing_stock_all($date_from, $date_to, $item->item_id);
                                $opening_value = inventory_get_opening_value_all($date_from, $item->item_id);
                                $value_in = inventory_get_value_in_all($date_from, $date_to, $item->item_id);
                                $value_out = inventory_get_value_out_all($date_from, $date_to, $item->item_id);
                                $closing_value = inventory_get_closing_value_all($date_from, $date_to, $item->item_id);
                                $warehouse_id = 0;
                            }

                            $recalculate_stock = \Point\Framework\Models\Inventory::where('item_id', '=', $item->item_id)->where('recalculate', '=', 1)->orderBy('form_date', 'asc')->count() > 0;
                            $lastBuy = \Point\PointPurchasing\Models\Inventory\InvoiceItem::join('point_purchasing_invoice', 'point_purchasing_invoice.id', '=', 'point_purchasing_invoice_item.point_purchasing_invoice_id')
                                ->join('formulir', 'point_purchasing_invoice.formulir_id', '=', 'formulir.id')
                                ->where('point_purchasing_invoice_item.item_id', '=', $item->item_id)
                                ->where('formulir.form_date', '<=', request()->get('date_to') ?? \Carbon\Carbon::now())
                                ->first();

                            $price = 0;

                            if ($lastBuy) {
                                $price = $lastBuy->price;
                            } else {
                                $ci = \Point\PointAccounting\Models\CutOffInventoryDetail::where('subledger_id', $item->item_id)->first();

                                if ($ci) {
                                    $price = $ci->amount / $ci->stock;
                                } else {
                                    $product = \Point\PointManufacture\Models\InputProduct::join('point_manufacture_input', 'point_manufacture_input.id', '=', 'point_manufacture_input_product.input_id')
                                        ->join('formulir', 'point_manufacture_input.formulir_id', '=', 'formulir.id')
                                        ->where('formulir.form_date', '<=', request()->get('date_to') ?? \Carbon\Carbon::now())
                                        ->whereNotNull('formulir.form_number')
                                        ->where('product_id', $item->item_id)->first();
                                    if ($product) {
                                        $materials = \Point\PointManufacture\Models\InputMaterial::where('input_id', $product->input_id)->get();
                                        $price = 0;
                                        info('======'.$product->item->name.'====='. $product->input_id);
                                        foreach ($materials as $material) {
                                            $lastBuyMaterial = \Point\PointPurchasing\Models\Inventory\InvoiceItem::join('point_purchasing_invoice', 'point_purchasing_invoice.id', '=', 'point_purchasing_invoice_item.point_purchasing_invoice_id')
                                                ->join('formulir', 'point_purchasing_invoice.formulir_id', '=', 'formulir.id')
                                                ->where('point_purchasing_invoice_item.item_id', '=', $material->material_id)
                                                ->where('formulir.form_date', '<=', request()->get('date_to') ?? \Carbon\Carbon::now())
                                                ->whereNotNull('formulir.form_number')
                                                ->first();

                                            info($material->item->name);

                                            if ($lastBuyMaterial) {
                                                $price += ($material->quantity * $lastBuyMaterial->price) / $product->quantity;
                                                info($product->item->name .'=  '.$material->quantity .'*'. $lastBuyMaterial->price .'/'. $product->quantity);
                                            }

                                            info ('price = ' . $price);
                                        }
                                        info('========');
                                    }
                                    else {
                                        $oi = \Point\Framework\Models\OpeningInventory::where('item_id', '=', $item->item_id)->first();
                                        if ($oi) {
                                            $price = $oi->price;
                                        }
                                    }
                                }

                                $total_closing_value += $price;
                            }
                            ?>
                            <tr>
                                <td>{{ $item->item->accountAsset->coa_number }}</td>
                                <td>{{ $item->item->accountAsset->name }}</td>
                                <td style="{{$recalculate_stock == true ? 'color:red;font-weight: bold' : ''}}">
                                    <a href="{{url('inventory/value-report/detail/'.$item->item_id.'?date_from='.$date_from.'&date_to='.$date_to.'&warehouse_id='.$warehouse_id)}}">
                                        @if($recalculate_stock == true)
                                            <span data-toggle="tooltip" data-placement="top" title="" style="overflow: hidden; position: relative;color:red !important" data-original-title="Stock value need to recalculate">
                                        <i class="fa fa-warning"></i>
                                                {{$item->item->codeName}}
                                    </span>
                                        @else
                                            {{$item->item->codeName}}
                                        @endif
                                    </a>
                                </td>
                                <td>{{ $item->item->defaultUnit($item->item->id)->name }}</td>
                                <td style="text-align: right">{{number_format_quantity($opening_stock)}}</td>
                                <td style="text-align: right">{{number_format_quantity($opening_value)}}</td>
                                <td style="text-align: right">{{number_format_quantity($stock_in)}}</td>
                                <td style="text-align: right">{{number_format_quantity($value_in)}}</td>
                                <td style="text-align: right">{{number_format_quantity($stock_out)}}</td>
                                <td style="text-align: right">{{number_format_quantity($value_out)}}</td>
                                <td style="text-align: right">{{number_format_quantity($closing_stock)}}</td>
                                <td style="text-align: right">{{number_format_quantity($price)}}</td>
                                <td style="text-align: right">{{number_format_quantity($closing_stock * $price)}}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="text-align: right;font-weight: bold">{{ number_format_accounting($total_closing_value) }}</td>
                        </tr>
                        </tbody>
                    </table>
                    {!! $inventory->appends([
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

@section('scripts')
<script type="text/javascript">
    $("#btn-excel").click(function(e) {
        var spinner = ' <i class="fa fa-spinner fa-spin" style="font-size:16px;"></i>';
        var date_from = $("#date_from").val();
        var date_to = $("#date_to").val();
        var search = $('#search').val();
        var warehouse = $('#warehouse_id').val();

        $(e.currentTarget).html(spinner).addClass('disabled');

        $.ajax({
            url: '{{url("inventory/value-report/export")}}',
            data: {
                date_from: date_from,
                date_to: date_to,
                search: search,
                warehouse: warehouse
            },
            success: function(result) {
                notification('export data success, please check your email in a few moments');
            },
            error:  function (result) {
                notification('export data failed, please try again');
            },
            complete: function(result) {
                $(e.currentTarget).removeClass('disabled').html('Export to excel');
            }
        });
    });
</script>
@stop
