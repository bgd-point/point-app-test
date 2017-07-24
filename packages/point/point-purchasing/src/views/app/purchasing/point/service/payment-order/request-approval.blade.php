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
            @include('point-purchasing::app.purchasing.point.service._breadcrumb')
            <li><a href="{{ url('purchasing/point/service/payment-order') }}">Payment Order</a></li>
            <li>Request approval</li>
        </ul>
        <h2 class="sub-header">Payment Order</h2>
        @include('point-purchasing::app.purchasing.point.service.payment-order._menu')

        <form action="{{url('purchasing/point/service/payment-order/send-request-approval')}}" method="post">
            {!! csrf_field() !!}

            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="table-responsive">
                        {!! $list_payment_order->render() !!}
                        <table class="table table-striped table-bordered">
                            <thead>
                            <tr>
                                <th width="100px" class="text-center">
                                    <input type="checkbox" id="check-all">
                                </th>
                                <th>Form Date</th>
                                <th>Form Number</th>
                                <th>Customer</th>
                                <th>Total Payment</th>
                                <th>Approval To</th>
                                <th>Last Request</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list_payment_order as $payment_order)
                                <tr id="list-{{$payment_order->formulir_id}}">
                                    <td class="text-center">
                                        <input type="checkbox" name="formulir_id[]"
                                               value="{{$payment_order->formulir_id}}">
                                    </td>
                                    <td>{{ date_format_view($payment_order->formulir->form_date) }}</td>
                                    <td>
                                        <a href="{{ url('purchasing/point/service/payment-order/'.$payment_order->id) }}">{{ $payment_order->formulir->form_number}}</a>
                                    </td>
                                    <td>
                                        <a href="{{ url('master/contact/person/'.$payment_order->person_id) }}">{{ $payment_order->person->codeName}}</a>
                                    </td>
                                    <td>
                                        {{ number_format_price($payment_order->total_payment) }}
                                    </td>
                                    <td>{{  $payment_order->formulir->approvalTo->name}}</td>
                                    <td>
                                        @if($payment_order->formulir->request_approval_at != null)
                                            {{ date_format_view($payment_order->formulir->request_approval_at, true) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {!! $list_payment_order->render() !!}
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-effect-ripple btn-primary">Send Request</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@stop
