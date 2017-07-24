@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        <li><a href="{{ url('inventory') }}">Inventory</a></li>
        <li>Inventory Usage</li>
    </ul>
    <h2 class="sub-header">Inventory Usage</h2>
    @include('point-inventory::app.inventory.point.inventory-usage._menu')

    <div class="panel panel-default">
        <div class="panel-body">
            <form action="{{ url('inventory/point/inventory-usage') }}" method="get" class="form-horizontal">
                <div class="form-group">
                    <div class="col-sm-3">
                        <select class="selectize" name="status" id="status" onchange="selectData('form_date', 'desc')">
                            <option value="0" @if(\Input::get('status') == 0) selected @endif>open</option>
                            <option value="1" @if(\Input::get('status') == 1) selected @endif>closed</option>
                            <option value="-1" @if(\Input::get('status') == -1) selected @endif>canceled</option>
                            <option value="all" @if(\Input::get('status') == 'all') selected @endif>all</option>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <div class="input-group input-daterange" data-date-format="{{date_format_masking()}}">
                            <input type="text" name="date_from" id="date-from" class="form-control date input-datepicker" placeholder="From"  value="{{\Input::get('date_from') ? \Input::get('date_from') : ''}}">
                            <span class="input-group-addon"><i class="fa fa-chevron-right"></i></span>
                            <input type="text" name="date_to" id="date-to" class="form-control date input-datepicker" placeholder="To" value="{{\Input::get('date_to') ? \Input::get('date_to') : ''}}">
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <input type="text" name="search" id="search" class="form-control" placeholder="Search..." value="{{\Input::get('search')}}" value="{{\Input::get('search') ? \Input::get('search') : ''}}">
                    </div>
                    <div class="col-sm-1">
                        <input type="hidden" name="order_by" value="{{\Input::get('order_by') ? \Input::get('order_by') : 'form_date'}}">
                        <input type="hidden" name="order_type" value="{{\Input::get('order_type') ? \Input::get('order_type') : 'desc'}}">
                        <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search</button>
                    </div>
                </div>
            </form>

            <br/>

            <div class="table-responsive">
                <?php 
                    $order_by = \Input::get('order_by') ? : 0;
                    $order_type = \Input::get('order_type') ? : 0;
                ?>
                {!! $list_inventory_usage->appends(['order_by'=>app('request')->get('order_by'), 'order_type'=>app('request')->get('order_type'), 'status'=>app('request')->get('status'), 'search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th style="cursor:pointer" onclick="selectData('form_date', @if($order_by == 'form_date' && $order_type == 'asc') 'desc' @elseif($order_by == 'form_date' && $order_type == 'desc') 'asc' @else 'desc' @endif)">Date <span class="pull-right"><i class="fa @if($order_by == 'form_date' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'form_date' && $order_type == 'desc') fa-sort-desc @else fa-sort-asc @endif fa-fw"></i></span></th>
                            <th style="cursor:pointer" onclick="selectData('form_number', @if($order_by == 'form_number' && $order_type == 'asc') 'desc' @elseif($order_by == 'form_number' && $order_type == 'desc') 'asc' @else 'desc' @endif)">Form Number <span class="pull-right"><i class="fa @if($order_by == 'form_number' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'form_number' && $order_type == 'desc') fa-sort-desc @else fa-sort-asc @endif fa-fw"></i></span></th>
                            <th style="cursor:pointer" onclick="selectData('warehouse.name', @if($order_by == 'warehouse.name' && $order_type == 'asc') 'desc' @elseif($order_by == 'warehouse.name' && $order_type == 'desc') 'asc' @else 'desc' @endif)">Warehouse <span class="pull-right"><i class="fa @if($order_by == 'warehouse.name' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'warehouse.name' && $order_type == 'desc') fa-sort-desc @else fa-sort-asc @endif fa-fw"></i></span></th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($list_inventory_usage as $inventoryusage)
                        <tr>
                            <td>{{ date_format_view($inventoryusage->formulir->form_date) }}</td>
                            <td><a href="{{ url('inventory/point/inventory-usage/'.$inventoryusage->id) }}">{{ $inventoryusage->formulir->form_number}}</a></td>
                            <td>{{ $inventoryusage->warehouse->codeName}}</td>
                            <td>
                                @include('framework::app.include._approval_status_label', ['approval_status' => $inventoryusage->formulir->approval_status])
                                @include('framework::app.include._form_status_label', ['form_status' => $inventoryusage->formulir->form_status])
                            </td>
                        </tr>

                        <tr>
                            <th></th>
                            <th>ITEM</th>
                            <th>STOCK BEFORE USAGE</th>
                            <th>QUANTITY USAGE</th>
                        </tr>

                        @foreach($inventoryusage->listInventoryUsage as $usage_item)
                            <tr>
                                <td></td>
                                <td>{{$usage_item->item->name}}</td>
                                <td>{{number_format_quantity($usage_item->stock_in_database)}} {{$usage_item->unit}}</td>
                                <td>{{number_format_quantity($usage_item->quantity_usage)}} {{$usage_item->unit}}</td>
                            </tr>
                        @endforeach
                        
                        @endforeach
                    </tbody>
                </table>
                {!! $list_inventory_usage->appends(['order_by'=>app('request')->get('order_by'), 'order_type'=>app('request')->get('order_type'), 'status'=>app('request')->get('status'), 'search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
            </div>
        </div>
    </div>
</div>
@stop

@section('scripts')
<script>
function selectData(order_by, order_type) {
    var status = $("#status option:selected").val();
    var date_from = $("#date-from").val();
    var date_to = $("#date-to").val();
    var search = $("#search").val();
    var url = '{{url()}}/inventory/point/inventory-usage/?order_by='+order_by+'&order_type='+order_type+'&status='+status+'&date_from='+date_from+'&date_to='+date_to+'&search='+search;
    location.href = url;
}
</script>
@stop
