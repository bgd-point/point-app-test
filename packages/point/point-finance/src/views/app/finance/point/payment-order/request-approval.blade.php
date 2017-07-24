@extends('core::app.layout')

@section('scripts')
<script>
    $("#check-all").change(function () {
        $("input:checkbox").prop('checked', $(this).prop("checked"));
    });
</script>
@stop

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
         @include('point-finance::app.finance.point.payment-order._breadcrumb')
         <li>Request approval</li>
    </ul>
    <h2 class="sub-header">Payment Order</h2>
    @include('point-finance::app.finance.point.payment-order._menu')
    
    <form action="{{url('finance/point/payment-order/send-request-approval')}}" method="post">
        {!! csrf_field() !!}

        <div class="panel panel-default">
            <div class="panel-body">            
                <div class="table-responsive">
                    {!! $list_payment_order->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center">
                                    <input type="checkbox" id="check-all">
                                </th>
                                <th>payment date</th>
                                <th>form number</th>
                                <th>supplier</th>
                                <th>approval to</th>
                                <th>last request</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($list_payment_order as $payment_order)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" name="formulir_id[]" value="{{$payment_order->formulir_id}}">
                                </td>
                                <td>{{ date_format_view($payment_order->formulir->form_date) }}</td>
                                <td><a href="{{ url('finance/point/payment-order/'.$payment_order->id) }}">{{ $payment_order->formulir->form_number}}</a></td>
                                <td>{!! get_url_person($payment_order->person->id) !!}</td>
                                <td>{{ $payment_order->formulir->approvalTo->name }}</td>
                                <td>
                                    @if($payment_order->formulir->request_approval_at != null)
                                        {{ date_format_view($payment_order->formulir->request_approval_at, true) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @foreach($payment_order->detail as $payment_order_detail)
                                <tr>
                                    <td></td>
                                    <td colspan="4">{{ $payment_order_detail->notes_detail }}</td>
                                    <td class="text-right">{{ number_format_price($payment_order_detail->amount) }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td></td>
                                <td colspan="4"></td>
                                <td class="text-right"><b>{{ number_format_price($payment_order->total) }}</b></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $list_payment_order->render() !!}
                </div>
                @if($list_payment_order->count())
                <div class="form-group">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-effect-ripple btn-primary">
                            send request
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </div>          
    </form>
</div>
@stop
