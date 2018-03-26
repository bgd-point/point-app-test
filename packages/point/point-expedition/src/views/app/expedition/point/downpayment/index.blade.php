@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-expedition::app/expedition/point/downpayment/_breadcrumb')
            <li>List</li>
        </ul>
        <h2 class="sub-header">Downpayment</h2>
        @include('point-expedition::app.expedition.point.downpayment._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{ url('expedition/point/downpayment') }}" method="get" class="form-horizontal">
                    <div class="form-group">
                    	<div class="col-sm-4">
                            <select class="selectize" name="status" id="status" onchange="selectData('form_date', 'desc')">
                                <option value="0" @if(\Input::get('status') == 0) selected @endif>open</option>                            
                                <option value="1" @if(\Input::get('status') == 1) selected @endif>closed</option>                            
                                <option value="-1" @if(\Input::get('status') == -1) selected @endif>canceled</option>                            
                                <option value="all" @if(\Input::get('status') == 'all') selected @endif>all</option>                            
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <div class="input-group input-daterange" data-date-format="{{date_format_masking()}}">
                                <input type="text" name="date_from" id="date-from" class="form-control date input-datepicker"
                                       placeholder="From" value="{{\Input::get('date_from') ? \Input::get('date_from') : ''}}">
                                <span class="input-group-addon"><i class="fa fa-chevron-right"></i></span>
                                <input type="text" name="date_to" id="date-to" class="form-control date input-datepicker"
                                       placeholder="To" value="{{\Input::get('date_to') ? \Input::get('date_to') : ''}}">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <input type="text" name="search" id="search" class="form-control" placeholder="Search..."
                                   value="{{\Input::get('search')}}"
                                   value="{{\Input::get('search') ? \Input::get('search') : ''}}">
                        </div>
                        <div class="col-sm-12">
                            <input type="hidden" name="order_by" id="order-by" value="{{\Input::get('order_by') ? \Input::get('order_by') : 'form_date'}}">
                            <input type="hidden" name="order_type" id="order-type" value="{{\Input::get('order_type') ? \Input::get('order_type') : 'desc'}}">
                            <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary">
                                <i class="fa fa-search"></i> Search
                            </button>
                            @if(auth()->user()->may('read.point.expedition.downpayment'))
                                <a class="btn btn-effect-ripple btn-effect-ripple btn-info button-export" id="btn-pdf" href="{{url('expedition/point/downpayment/pdf?date_from='.\Input::get('date_from').'&date_to='.\Input::get('date_to').'&search='.\Input::get('search').'&order_by='.\Input::get('order_by').'&order_type='.\Input::get('order_type').'&status='.\Input::get('status'))}}"> export to PDF</a>
                            @endif
                        </div>
                    </div>
                </form>

                <br/>

                <div class="table-responsive">
                    <?php 
                        $order_by = \Input::get('order_by') ? : 0;
                        $order_type = \Input::get('order_type') ? : 0;
                    ?>
                    {!! $list_downpayment->appends(['order_by'=>app('request')->get('order_by'), 'order_type'=>app('request')->get('order_type'), 'status'=>app('request')->get('status'), 'search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th style="cursor:pointer" onclick="selectData('form_date', @if($order_by == 'form_date' && $order_type == 'asc') 'desc' @elseif($order_by == 'form_date' && $order_type == 'desc') 'asc' @else 'desc' @endif)">Date <span class="pull-right"><i class="fa @if($order_by == 'form_date' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'form_date' && $order_type == 'desc') fa-sort-desc @else fa-sort-asc @endif fa-fw"></i></span></th>
                            <th style="cursor:pointer" onclick="selectData('form_number', @if($order_by == 'form_number' && $order_type == 'asc') 'desc' @elseif($order_by == 'form_number' && $order_type == 'desc') 'asc' @else 'desc' @endif)">Form Number <span class="pull-right"><i class="fa @if($order_by == 'form_number' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'form_number' && $order_type == 'desc') fa-sort-desc @else fa-sort-asc @endif fa-fw"></i></span></th>
                            <th>Form Reference</th>
                            <th style="cursor:pointer" onclick="selectData('person.name', @if($order_by == 'person.name' && $order_type == 'asc') 'desc' @elseif($order_by == 'person.name' && $order_type == 'desc') 'asc' @else 'desc' @endif)">Expedition <span class="pull-right"><i class="fa @if($order_by == 'person.name' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'person.name' && $order_type == 'desc') fa-sort-desc @else fa-sort-asc @endif fa-fw"></i></span></th>
                            <th>Amount</th>
                            <th>Remaining <br> Amount</th>
                            <th>Notes</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($list_downpayment as $downpayment)
                        <?php
                        $downpayment_remaining = \Point\Framework\Helpers\ReferHelper::remaining(get_class($downpayment),
                            $downpayment->id, $downpayment->amount);
                        $expedition_order = '';
                        if ($downpayment->expedition_order_id) {
                            $expedition_order = Point\PointExpedition\Models\ExpeditionOrder::find($downpayment->expedition_order_id);
                        }
                        ?>
                            <tr>
                                <td>{{ date_format_view($downpayment->formulir->form_date) }}</td>
                                <td>
                                    <a href="{{ url('expedition/point/downpayment/'.$downpayment->id) }}">{{ $downpayment->formulir->form_number}}</a>
                                </td>
                                <td>
                                    @if($downpayment->expedition_order_id)
                                    <a href="{{ url('expedition/point/expedition-order/'.$downpayment->expedition_order_id) }}">{{ $expedition_order->formulir->form_number}}</a>
                                    @else
                                    -
                                    @endif
                                </td>
                                <td>
                                    {!! get_url_person($downpayment->expedition->id) !!}
                                </td>
                                <td>{{ number_format_quantity($downpayment->amount) }}</td>
                                <td>{{ number_format_quantity($downpayment_remaining) }}</td>
                                <td>{{ $downpayment->formulir->notes ? $downpayment->formulir->notes : '-' }}</td>
                                <td>
                                    @include('framework::app.include._approval_status_label', ['approval_status' => $downpayment->formulir->approval_status])
                                    @include('framework::app.include._form_status_label', ['form_status' => $downpayment->formulir->form_status])
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $list_downpayment->appends(['order_by'=>app('request')->get('order_by'), 'order_type'=>app('request')->get('order_type'), 'status'=>app('request')->get('status'), 'search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
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
    var url = '{{url()}}/expedition/point/downpayment/?order_by='+order_by+'&order_type='+order_type+'&status='+status+'&date_from='+date_from+'&date_to='+date_to+'&search='+search;
    location.href = url;
}
</script>
@stop
