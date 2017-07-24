@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.fixed-assets._breadcrumb')
            <li>Purchase order</li>
        </ul>
        <h2 class="sub-header">Purchase Order | Fixed Assets</h2>
        @include('point-purchasing::app.purchasing.point.fixed-assets.purchase-order._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{ url('purchasing/point/fixed-assets/purchase-order') }}" method="get" class="form-horizontal">
                    <div class="form-group">
                        <div class="col-sm-3">
                            <select class="selectize" name="status" id="status" onchange="selectData()">
                                <option value="0" @if(\Input::get('status') == 0) selected @endif>open</option>
                                <option value="1" @if(\Input::get('status') == 1) selected @endif>closed</option>
                                <option value="-1" @if(\Input::get('status') == -1) selected @endif>canceled</option>
                            </select>
                        </div>
                        
                        <div class="col-sm-3">
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
                        <div class="col-sm-3">
                            <input type="text" name="search" id="search" class="form-control" placeholder="Search..."
                                   value="{{\Input::get('search')}}"
                                   value="{{\Input::get('search') ? \Input::get('search') : ''}}">
                        </div>
                        <div class="col-sm-3">
                            <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i
                                        class="fa fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </form>

                <br/>

                <div class="table-responsive">
                    {!! $list_purchase_order->appends(['status'=>app('request')->get('status'), 'search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th style="width: 180px"></th>
                            <th style="width: 120px">Form</th>
                            <th>Customer</th>
                            <th>Grand Total</th>
                            <th>Total Remaining Downpayment</th>
                            <th>Total Downpayment</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($list_purchase_order as $purchase_order)
                            <td>
                                @if($purchase_order->formulir->approval_status == '1' && $purchase_order->formulir->form_status == 0 && auth()->user()->may('create.point.sales.downpayment') && $purchase_order->is_cash == 1)
                                    {{ $purchase_order->checkDownpayment() }}
                                @endif
                            </td>
                            <td>
                                {{ date_format_view($purchase_order->formulir->form_date) }} <br>
                                <a href="{{ url('purchasing/point/fixed-assets/purchase-order/'.$purchase_order->id) }}">{{ $purchase_order->formulir->form_number}}</a>
                            </td>
                            <td>
                                <a href="{{ url('master/contact/supplier/'.$purchase_order->person_id) }}">{{ $purchase_order->supplier->codeName}}</a>
                            </td>
                            <td>{{number_format_price($purchase_order->total)}}
                                <i class='text-info' style='font-size:12px'> [{{ $purchase_order->is_cash == 1 ? 'Cash' : 'Credit' }}]</i> <br>
                            </td>
                            <td>{{ number_format_price($purchase_order->getTotalRemainingDownpayment(($purchase_order->id))) }}</td>
                            <td>{{ number_format_price($purchase_order->getTotalDownpayment(($purchase_order->id))) }}</td>
                            <td>
                                @include('framework::app.include._approval_status_label', ['approval_status' => $purchase_order->formulir->approval_status])
                                @include('framework::app.include._form_status_label', ['form_status' => $purchase_order->formulir->form_status])
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $list_purchase_order->appends(['status'=>app('request')->get('status'), 'search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
<script>
function selectData() {
    var status = $("#status option:selected").val();
    var date_from = $("#date-from").val();
    var date_to = $("#date-to").val();
    var search = $("#search").val();
    var url = '{{url()}}/purchasing/point/fixed-assets/purchase-order/?status='+status+'&date_from='+date_from+'&date_to='+date_to+'&search='+search;
    location.href = url;
}
</script>
@stop
