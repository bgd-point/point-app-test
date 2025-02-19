@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
             <li><a href="{{url('finance')}}">Finance</a></li>
             <li>Payment Order</li>
        </ul>
        <h2 class="sub-header"> Payment Order</h2>
        @include('point-finance::app.finance.point.payment-order._menu')
        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{ url('finance/point/payment-order') }}" method="get" class="form-horizontal">
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
                            <div class="input-group input-daterange" data-date-format="{{ date_format_masking()}}">
                                <input type="text" id="date-from" name="date_from" class="form-control date input-datepicker"  placeholder="date from" value="{{\Input::get('date_from') ? \Input::get('date_from') : ''}}">
                                <span class="input-group-addon"><i class="fa fa-chevron-right"></i></span>
                                <input type="text" id="date-to" name="date_to" class="form-control date input-datepicker" placeholder="date to" value="{{\Input::get('date_to') ? \Input::get('date_to') : ''}}">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <input type="text" id="search" name="search" class="form-control" placeholder="Search..." value="{{\Input::get('search')}}">
                        </div>
                        <div class="col-sm-3">
                            <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search</button>
                            <a class="btn btn-success" id="full_view" onclick="showAll();">Show All</a>
                        </div>
                    </div>
                </form>

                <br/>

                <div class="table-responsive">
                    {!! $list_payment_order->appends(['search'=>app('request')->get('search'),'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to'),'order_by'=>app('request')->get('order_by'), 'order_type'=>app('request')->get('order_type'), 'status'=>app('request')->get('status')])->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 80px"></th>
                                <th>Date</th>
                                <th>Form Number</th>
                                <th>Pay To</th>
                                <th colspan="2" class="th-detail">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($list_payment_order as $payment_order)
                                <tr>
                                    <td>
                                        @include('framework::app.include._approval_status_label', [
                                            'approval_status' => $payment_order->formulir->approval_status,
                                            'approval_message' => $payment_order->formulir->approval_message,
                                            'approval_at' => $payment_order->formulir->approval_at,
                                            'approval_to' => $payment_order->formulir->approvalTo->name,
                                        ])
                                        @include('framework::app.include._form_status_label', ['form_status' => $payment_order->formulir->form_status])
                                    </td>
                                    <td>{{ date_format_view($payment_order->formulir->form_date) }}</td>
                                    <td>
                                        <a href="{{ url('finance/point/payment-order/'.$payment_order->id) }}">{{ $payment_order->formulir->form_number}}</a>
                                    </td>
                                    <td>{!! get_url_person($payment_order->person->id) !!}</td>
                                    <td class="td-detail">
                                        <strong>ITEM</strong>
                                    </td>
                                    <td class="td-detail">
                                        <strong>AMOUNT</strong>
                                    </td>
                                </tr>
                                @foreach($payment_order->detail as $payment_order_detail)
                                    <tr class="tr-detail">
                                        <td colspan="4"></td>
                                        <td>[ {{ $payment_order_detail->coa->account }} ] {{ $payment_order_detail->notes_detail }}</td>
                                        <td class="text-right">{{ number_format_price($payment_order_detail->amount) }}</td>
                                    </tr>
                                @endforeach
                                <tr class="tr-detail">
                                    <td></td>
                                    <td colspan="4">
                                    <td class="text-right"><b>{{ number_format_price($payment_order->total) }}</b></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {!! $list_payment_order->appends(['search'=>app('request')->get('search'),'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to'),'order_by'=>app('request')->get('order_by'), 'order_type'=>app('request')->get('order_type'), 'status'=>app('request')->get('status')])->render() !!}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function showAll(){
        $('.th-detail').show();
        $('.td-detail').show();
        $('.tr-detail').show();
        $('#full_view').attr('onclick','compact()');
        $('#full_view').text('Compact');
    }
    compact();
    function compact(){
        $('.th-detail').hide();
        $('.td-detail').hide();
        $('.tr-detail').hide();
        $('#full_view').attr('onclick','showAll()');
        $('#full_view').text('Show All');
    }
    function selectData(order_by, order_type) {
      var status = $("#status").val();
      var date_from = $("#date-from").val();
      var date_to = $("#date-to").val();
      var search = $("#search").val();
      var url = '{{url()}}/finance/point/payment-order/?order_by='+order_by+'&order_type='+order_type+'&status='+status+'&date_from='+date_from+'&date_to='+date_to+'&search='+search;
      location.href = url;
    }
</script>
@stop
