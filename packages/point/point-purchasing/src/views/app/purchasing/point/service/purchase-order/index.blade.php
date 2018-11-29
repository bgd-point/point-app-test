@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.service._breadcrumb')
            <li>Purchase Order</li>
        </ul>
        <h2 class="sub-header">Purchase Order</h2>
        @include('point-purchasing::app.purchasing.point.service.purchase-order._menu')
        {{ $list_purchase_order }}
        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{ url('purchasing/point/service/purchase-order') }}" method="get" class="form-horizontal">
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
                                        placeholder="From"
                                        value="{{\Input::get('date_from') ? \Input::get('date_from') : ''}}">
                                <span class="input-group-addon"><i class="fa fa-chevron-right"></i></span>
                                <input type="text" name="date_to" id="date-to" class="form-control date input-datepicker"
                                        placeholder="To"
                                        value="{{\Input::get('date_to') ? \Input::get('date_to') : ''}}">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <input type="text" name="search" id="date-search" class="form-control"
                                    placeholder="Search Form Number / supplier..." value="{{\Input::get('search')}}"
                                    value="{{\Input::get('search') ? \Input::get('search') : ''}}">
                        </div>
                        <div class="col-sm-12">
                            <input type="hidden" name="order_by" value="{{\Input::get('order_by') ? \Input::get('order_by') : 'form_date'}}">
                            <input type="hidden" name="order_type" value="{{\Input::get('order_type') ? \Input::get('order_type') : 'desc'}}">
                            <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search
                            </button>
                            @if(auth()->user()->may('read.point.purchasing.service.purchase-order'))
                                <a class="btn btn-effect-ripple btn-effect-ripple btn-info button-export" id="btn-pdf" href="{{url('purchasing/point/service/purchase-order/pdf?date_from='.\Input::get('date_from').'&date_to='.\Input::get('date_to').'&search='.\Input::get('search').'&order_by='.\Input::get('order_by').'&order_type='.\Input::get('order_type').'&status='.\Input::get('status'))}}"> export to PDF</a>
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
                    {!! $list_purchase_order->appends(['order_by'=>app('request')->get('order_by'), 'order_type'=>app('request')->get('order_type'), 'status'=>app('request')->get('status'), 'search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr class="th-head">
                            <th style="cursor:pointer" onclick="selectData('form_date', `{{($order_by == 'form_date' && $order_type == 'desc') ? 'asc' : 'desc'}}`)">
                                Form Date
                                <span class="pull-right">
                                    <i class="fa fa-fw
                                        @if($order_by === 'form_date')
                                            @if($order_type === 'desc')
                                                fa-sort-desc
                                            @else
                                                fa-sort-asc
                                            @endif
                                        @else
                                            fa-sort
                                        @endif
                                    "></i>
                                </span>
                            </th>
                            <th style="cursor:pointer" onclick="selectData('form_number', `{{($order_by == 'form_date' && $order_type == 'desc') ? 'asc' : 'desc'}}`)">Form Number
                                <span class="pull-right">
                                    <i class="fa fa-fw
                                        @if($order_by === 'form_number')
                                            @if($order_type === 'desc')
                                                fa-sort-desc
                                            @else
                                                fa-sort-asc
                                            @endif
                                        @else
                                            fa-sort
                                        @endif
                                    "></i>
                                </span>
                            </th>
                            <th style="cursor:pointer" onclick="selectData('person.name', `{{($order_by == 'form_date' && $order_type == 'desc') ? 'asc' : 'desc'}}`)">Supplier
                                <span class="pull-right">
                                    <i class="fa fa-fw
                                        @if($order_by === 'person.name')
                                            @if($order_type === 'desc')
                                                fa-sort-desc
                                            @else
                                                fa-sort-asc
                                            @endif
                                        @else
                                            fa-sort
                                        @endif
                                    "></i>
                                </span>
                            </th>
                            <th class="text-right" style="cursor:pointer" onclick="selectData('total', `{{($order_by == 'total' && $order_type == 'desc') ? 'asc' : 'desc'}}`)">
                                Total
                                <span class="pull-right">
                                    <i class="fa fa-fw
                                        @if($order_by === 'total')
                                            @if($order_type === 'desc')
                                                fa-sort-desc
                                            @else
                                                fa-sort-asc
                                            @endif
                                        @else
                                            fa-sort
                                        @endif
                                    "></i>
                                </span>
                            </th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($list_purchase_order as $purchase_order)
                            <?php array_push($array_purchase_order_id,$purchase_order->id); ?>
                            <tr  class="row-detail" id="row_detail_{{$purchase_order->id}}">
                                <td>{{ date_format_view($purchase_order->formulir->form_date) }}</td>
                                <td>
                                    <a href="{{ url('purchasing/point/service/purchase-order/'.$purchase_order->id) }}">{{ $purchase_order->formulir->form_number}}</a>
                                </td>
                                <td>
                                    {!! get_url_person($purchase_order->person->id) !!}
                                </td>
                                <td class="text-right">
                                    {{ number_format_price($purchase_order->total) }}
                                </td>
                                <td>
                                    @include('framework::app.include._approval_status_label', ['approval_status' => $purchase_order->formulir->approval_status])
                                    @include('framework::app.include._form_status_label', ['form_status' => $purchase_order->formulir->form_status])
                                </td>
                            </tr>
                        @endforeach
                        <input type="hidden" id="array_purchase_order_id" value="{{ implode('#',$array_purchase_order_id) }}">
                        </tbody>
                    </table>
                    {!! $list_purchase_order->appends(['order_by'=>app('request')->get('order_by'), 'order_type'=>app('request')->get('order_type'), 'status'=>app('request')->get('status'), 'search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
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
    var url = '{{url()}}/purchasing/point/service/purchase-order/?order_by='+order_by+'&order_type='+order_type+'&status='+status;
    location.href = url;
}
</script>
@stop
