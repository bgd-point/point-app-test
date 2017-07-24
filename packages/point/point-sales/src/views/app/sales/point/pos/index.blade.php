@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-sales::app/sales/point/pos/_breadcrumb')
        <li>List</li>
    </ul>
    <h2 class="sub-header">Point of Sales</h2>
    @include('point-sales::app.sales.point.pos._menu')

    <div class="panel panel-default">
        <div class="panel-body">
            <form action="{{ url('sales/point/pos') }}" method="get" class="form-horizontal">
                <div class="form-group">
                    <div class="col-sm-3">
                        <select class="selectize" name="status" id="status" onchange="selectData('form_date', 'desc')">
                            <option value="0" @if(\Input::get('status') == 0) selected @endif>draft</option>
                            <option value="1" @if(\Input::get('status') == 1) selected @endif>closed</option>
                            <option value="-1" @if(\Input::get('status') == -1) selected @endif>canceled</option>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <div class="input-group input-daterange" data-date-format="{{ date_format_masking()}}">
                            <input type="text" name="date_from" id="date-from" class="form-control date input-datepicker"  placeholder="Date From" value="{{\Input::get('date_from') ? \Input::get('date_from') : ''}}">
                            <span class="input-group-addon"><i class="fa fa-chevron-right"></i></span>
                            <input type="text" name="date_to" id="date-to" class="form-control date input-datepicker" placeholder="date To" value="{{\Input::get('date_to') ? \Input::get('date_to') : ''}}">
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <input type="text" name="search" id="search" class="form-control" value="{{\Input::get('search') ? \Input::get('search') : ''}}"  placeholder="Form Number / Item / Customer / Code of item or customer ...">
                    </div>
                    <div class="col-sm-1">
                        <input type="hidden" name="order_by" value="{{\Input::get('order_by') ? \Input::get('order_by') : 'form_date'}}">
                        <input type="hidden" name="order_type" value="{{\Input::get('order_type') ? \Input::get('order_type') : 'desc'}}">
                        <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search</button>
                    </div>
                </div>
            </form>
            <div class="table-responsive">
                <?php 
                    $order_by = \Input::get('order_by') ? : 0;
                    $order_type = \Input::get('order_type') ? : 0;
                ?>
                {!! $list_sales->appends(['order_by'=>app('request')->get('order_by'), 'order_type'=>app('request')->get('order_type'), 'status'=>app('request')->get('status'), 'search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
                <table class="table tabble-striped table-bordered" cellpadding="0" cellspacing="0" border="0" >
                    <thead>
                        <tr>
                            <th></th>
                            <th style="cursor:pointer" onclick="selectData('form_date', @if($order_by == 'form_date' && $order_type == 'asc') 'desc' @elseif($order_by == 'form_date' && $order_type == 'desc') 'asc' @else 'desc' @endif)">Date <span class="pull-right"><i class="fa @if($order_by == 'form_date' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'form_date' && $order_type == 'desc') fa-sort-desc @else fa-sort-asc @endif fa-fw"></i></span></th>
                            <th style="cursor:pointer" onclick="selectData('form_number', @if($order_by == 'form_number' && $order_type == 'asc') 'desc' @elseif($order_by == 'form_number' && $order_type == 'desc') 'asc' @else 'desc' @endif)">Form Number <span class="pull-right"><i class="fa @if($order_by == 'form_number' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'form_number' && $order_type == 'desc') fa-sort-desc @else fa-sort-asc @endif fa-fw"></i></span></th>
                            <th style="cursor:pointer" onclick="selectData('person.name', @if($order_by == 'person.name' && $order_type == 'asc') 'desc' @elseif($order_by == 'person.name' && $order_type == 'desc') 'asc' @else 'desc' @endif)">Customer <span class="pull-right"><i class="fa @if($order_by == 'person.name' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'person.name' && $order_type == 'desc') fa-sort-desc @else fa-sort-asc @endif fa-fw"></i></span></th>
                            <th>Sales</th>
                            <th>Status</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $total_sales = 0;?>
                        @foreach($list_sales as $sales)
                        <tr id="list-{{$sales->id}}">
                            <td class="text-center">
                                @if($sales->formulir->form_status == 0)
                                <a href="{{ url('sales/point/pos/'.$sales->id.'/edit') }}" data-toggle="tooltip" title="Update" class="btn btn-effect-ripple btn-xs btn-info">Update</a>
                                @else
                                <a href="{{ url('sales/point/pos/'.$sales->id) }}" data-toggle="tooltip" title="Show" class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-file"></i></a>
                                <a href="#" onclick="pagePrint('/sales/point/pos/print/{{$sales->id}}');" data-toggle="tooltip" title="Print" class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-print"></i></a>
                                @endif
                            </td>
                            <td>{{ $sales->formulir->form_number }}</td>
                            <td>{{ date_format_view($sales->formulir->form_date, true) }}</td>
                            <td>{{ $sales->customer->codeName }}</td>
                            <td>{{ $sales->formulir->createdBy->name }}</td>
                            <td>@include('framework::app.include._form_status_label', ['form_status' => $sales->formulir->form_status])</td>
                            <td class="text-right">{{ number_format_accounting($sales->total) }}</td>
                        </tr>
                        @endforeach
                    </tbody> 
                </table>
                {!! $list_sales->appends(['order_by'=>app('request')->get('order_by'), 'order_type'=>app('request')->get('order_type'), 'status'=>app('request')->get('status'), 'search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
            </div>
        </div>
    </div>  
</div>
@stop

@section('scripts')
<script>
    function pagePrint(url){
        var printWindow = window.open( url, 'Print', 'left=200, top=200, width=950, height=500, toolbar=0, resizable=0');
        printWindow.addEventListener('load', function(){
            printWindow.print();
            
        }, true);
    }

    function selectData(order_by, order_type) {
        var status = $("#status option:selected").val();
        var date_from = $("#date-from").val();
        var date_to = $("#date-to").val();
        var search = $("#search").val();
        var url = '{{url()}}/sales/point/pos?order_by='+order_by+'&order_type='+order_type+'&status='+status+'&date_from='+date_from+'&date_to='+date_to+'&search='+search;
        location.href = url;
    }
</script>
@stop
