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
                    <div class="col-sm-6">
                        <div class="input-group input-daterange" data-date-format="{{ date_format_masking()}}">
                            <input type="text" name="date_from" class="form-control date input-datepicker"  placeholder="date from" value="{{\Input::get('date_from') ? \Input::get('date_from') : ''}}">
                            <span class="input-group-addon"><i class="fa fa-chevron-right"></i></span>
                            <input type="text" name="date_to" class="form-control date input-datepicker" placeholder="date to" value="{{\Input::get('date_to') ? \Input::get('date_to') : ''}}">
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <input type="text" name="search" class="form-control" placeholder="Search..." value="{{\Input::get('search')}}">
                    </div>
                    <div class="col-sm-3">
                        <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search</button> 
                    </div>
                </div>
            </form>

            <br/>

            <div class="table-responsive">
                {!! $list_payment_order->appends(['search'=>app('request')->get('search')])->render() !!}
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th style="width: 80px"></th>
                            <th>Date</th>
                            <th>Form Number</th>
                            <th>Pay To</th>
                            <th></th>
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
                                <td>

                                </td>
                            </tr>
                            <tr>
                                <th></th>
                                <th colspan="3">Description</th>
                                <th class="text-right">Amount</th>
                            </tr>
                            @foreach($payment_order->detail as $payment_order_detail)
                                <tr>
                                    <td></td>
                                    <td colspan="3">[ {{ $payment_order_detail->coa->account }} ] {{ $payment_order_detail->notes_detail }}</td>
                                    <td class="text-right">{{ number_format_price($payment_order_detail->amount) }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td></td>
                                <td colspan="3">
                                <td class="text-right"><b>{{ number_format_price($payment_order->total) }}</b></td>
                            </tr>
                        @endforeach  
                    </tbody> 
                </table>
                {!! $list_payment_order->appends(['search'=>app('request')->get('search')])->render() !!}
            </div>
        </div>
    </div>  
</div>
@stop
