@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-sales::app/sales/point/pos/pricing/_breadcrumb')
        <li>Point of Sales</li>
    </ul>
    <h2 class="sub-header">Point Of Sales | Pricing</h2>
    @include('point-sales::app.sales.point.pos.pricing._menu')

    <div class="panel panel-default">
        <div class="panel-body">
            <form action="{{ url('sales/point/pos/pricing') }}" method="get" class="form-horizontal">
                <div class="form-group">
                    <div class="col-sm-3">
                        <select class="selectize" name="status" id="status" onchange="selectData('form_date', 'desc')">
                            <option value="0" @if(\Input::get('status') == 0) selected @endif>open</option>
                            <option value="1" @if(\Input::get('status') == 1) selected @endif>closed</option>
                            <option value="-1" @if(\Input::get('status') == -1) selected @endif>canceled</option>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <div class="input-group input-daterange" data-date-format="{{ date_format_masking()}}">
                            <input type="text" name="date_from" id="date-from" class="form-control date input-datepicker"  placeholder="Date From" value="{{\Input::get('date_from') ? \Input::get('date_from') : ''}}">
                            <span class="input-group-addon"><i class="fa fa-chevron-right"></i></span>
                            <input type="text" name="date_to" id="date-to" class="form-control date input-datepicker" placeholder="Date To" value="{{\Input::get('date_to') ? \Input::get('date_to') : ''}}">
                        </div>
                    </div>
                <div class="col-sm-3">
                    <input type="hidden" name="order_by" value="{{\Input::get('order_by') ? \Input::get('order_by') : 'form_date'}}">
                    <input type="hidden" name="order_type" value="{{\Input::get('order_type') ? \Input::get('order_type') : 'desc'}}">
                    <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search </button> 
                    @if(auth()->user()->may('read.point.sales.pos.pricing'))
                        <a class="btn btn-effect-ripple btn-effect-ripple btn-info button-export" id="btn-pdf" href="{{url('sales/point/pos/pricing/pdf?date_from='.\Input::get('date_from').'&date_to='.\Input::get('date_to').'&search='.\Input::get('search').'&order_by='.\Input::get('order_by').'&order_type='.\Input::get('order_type').'&status='.\Input::get('status'))}}"> export to PDF</a>
                    @endif
                </div>
            </div>
        </form>
        <div class="table-responsive">
            <?php 
                $order_by = \Input::get('order_by') ? : 0;
                $order_type = \Input::get('order_type') ? : 0;
            ?>
            {!! $list_pricing->appends(['order_by'=>app('request')->get('order_by'), 'status'=>app('request')->get('status'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
            <table class="table tabble-striped table-bordered" cellpadding="0" cellspacing="0" border="0" >
                <thead>
                    <tr>
                        <th style="cursor:pointer" onclick="selectData('form_date', @if($order_by == 'form_date' && $order_type == 'asc') 'desc' @elseif($order_by == 'form_date' && $order_type == 'desc') 'asc' @else 'desc' @endif)">Date <span class="pull-right"><i class="fa @if($order_by == 'form_date' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'form_date' && $order_type == 'desc') fa-sort-desc @else fa-sort-asc @endif fa-fw"></i></span></th>
                        <th style="cursor:pointer" onclick="selectData('form_number', @if($order_by == 'form_number' && $order_type == 'asc') 'desc' @elseif($order_by == 'form_number' && $order_type == 'desc') 'asc' @else 'desc' @endif)">Form Number <span class="pull-right"><i class="fa @if($order_by == 'form_number' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'form_number' && $order_type == 'desc') fa-sort-desc @else fa-sort-asc @endif fa-fw"></i></span></th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($list_pricing as $pricing)
                    <tr>
                        <td>{{ date_format_view($pricing->formulir->form_date) }}</td>
                        <td><a href="{{url('sales/point/pos/pricing/'.$pricing->id)}}">{{ $pricing->formulir->form_number }}</a></td>
                        <td>{{ $pricing->formulir->notes }}</td>
                    </tr>
                    @endforeach  
                </tbody> 
            </table>
            {!! $list_pricing->appends(['order_by'=>app('request')->get('order_by'), 'status'=>app('request')->get('status'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
        </div>
    </div>
</div>  
</div>
@stop

@section('scripts')
<script>
function selectData(order_by, order_type) {
    var date_from = $("#date-from").val();
    var date_to = $("#date-to").val();
    var search = $("#search").val();
    var status = $("#status option:selected").val();
    var url = '{{url()}}/sales/point/pos/pricing/?order_by='+order_by+'&order_type='+order_type+'&status='+status+'&date_from='+date_from+'&date_to='+date_to+'&search='+search;
    location.href = url;
}
</script>
@stop
