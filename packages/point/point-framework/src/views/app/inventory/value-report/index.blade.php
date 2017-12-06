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
                        <div class="col-sm-6">
                            <div class="input-group input-daterange" data-date-format="{{date_format_masking()}}">
                                <input type="text" name="date_from" class="form-control date input-datepicker" placeholder="From"  value="{{\Input::get('date_from') ? \Input::get('date_from') : date(date_format_get(), strtotime($date_from))}}">
                                <span class="input-group-addon"><i class="fa fa-chevron-right"></i></span>
                                <input type="text" name="date_to" class="form-control date input-datepicker" placeholder="To" value="{{\Input::get('date_to') ? \Input::get('date_to') : date(date_format_get(), strtotime($date_to))}}">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <input type="text" name="search" class="form-control" placeholder="Search Item..." value="{{\Input::get('search')}}" value="{{\Input::get('search') ? \Input::get('search') : ''}}">
                        </div>
                        <div class="col-sm-3">
                            <select name="warehouse_id" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                <option value="0" @if($search_warehouse) selected @endif>All</option>
                                @foreach($list_warehouse as $warehouse)
                                    <option value="{{$warehouse->id}}" @if( $search_warehouse && $search_warehouse->id == $warehouse->id) selected @endif>{{$warehouse->codeName}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-3">
                            <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search</button>
                        </div>
                    </div>
                </form>

                <br/>

                <div class="table-responsive">
                    {!! $inventory->appends(['search'=>app('request')->get('search')])->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th>Item</th>
                            <th>Opening Stock <br/> <span style="font-size:12px">({{date_format_view($date_from)}})</span></th>
                            <th>Stock In <br/> <span style="font-size:12px"> ({{date_format_view($date_from)}}) - ({{date_format_view($date_to)}})</th>
                            <th>Stock Out <br/> <span style="font-size:12px"> ({{date_format_view($date_from)}}) - ({{date_format_view($date_to)}})</th>
                            <th>Closing Stock <br/> <span style="font-size:12px"> ({{date_format_view($date_to)}})</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($inventory as $item)
                            <?php
                            if ($search_warehouse) {
                                $opening_stock = inventory_get_opening_stock($date_from, $item->item_id, $search_warehouse->id);
                                $stock_in = inventory_get_stock_in($date_from, $date_to, $item->item_id, $search_warehouse->id);
                                $stock_out = inventory_get_stock_out($date_from, $date_to, $item->item_id, $search_warehouse->id);
                                $closing_stock = inventory_get_closing_stock($date_from, $date_to, $item->item_id, $search_warehouse->id);
                                $warehouse_id = $search_warehouse->id;
                            } else {
                                $opening_stock = inventory_get_opening_stock_all($date_from, $item->item_id);
                                $stock_in = inventory_get_stock_in_all($date_from, $date_to, $item->item_id);
                                $stock_out = inventory_get_stock_out_all($date_from, $date_to, $item->item_id);
                                $closing_stock = inventory_get_closing_stock_all($date_from, $date_to, $item->item_id);
                                $warehouse_id = 0;
                            }
                            $recalculate_stock = \Point\Framework\Models\Inventory::where('item_id', '=', $item->item_id)->where('recalculate', '=', 1)->orderBy('form_date', 'asc')->count() > 0;
                            ?>
                            <tr>
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
                                <td>{{number_format_quantity($opening_stock)}}</td>
                                <td>{{number_format_quantity($stock_in)}}</td>
                                <td>{{number_format_quantity($stock_out)}}</td>
                                <td>{{number_format_quantity($closing_stock)}}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $inventory->appends(['search'=>app('request')->get('search')])->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop
