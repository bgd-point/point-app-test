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
            @include('point-sales::app.sales.point.sales._breadcrumb')
            <li><a href="{{ url('sales/point/indirect/payment-collection') }}">Payment Collection</a></li>
            <li>Request approval</li>
        </ul>
        <h2 class="sub-header">Payment Collection</h2>
        @include('point-sales::app.sales.point.sales.payment-collection._menu')

        <form action="{{url('sales/point/indirect/payment-collection/send-request-approval')}}" method="post">
            {!! csrf_field() !!}

            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="table-responsive">
                        {!! $list_payment_collection->render() !!}
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
                            @foreach($list_payment_collection as $payment_collection)
                                <tr id="list-{{$payment_collection->formulir_id}}">
                                    <td class="text-center">
                                        <input type="checkbox" name="formulir_id[]"
                                               value="{{$payment_collection->formulir_id}}">
                                    </td>
                                    <td>{{ date_format_view($payment_collection->formulir->form_date) }}</td>
                                    <td>
                                        <a href="{{ url('sales/point/indirect/payment-collection/'.$payment_collection->id) }}">{{ $payment_collection->formulir->form_number}}</a>
                                    </td>
                                    <td>
                                        <a href="{{ url('master/contact/person/'.$payment_collection->person_id) }}">{{ $payment_collection->person->codeName}}</a>
                                    </td>
                                    <td>
                                        {{ number_format_price($payment_collection->total_payment) }}
                                    </td>
                                    <td>{{  $payment_collection->formulir->approvalTo->name}}</td>
                                    <td>
                                        @if($payment_collection->formulir->request_approval_at != null)
                                            {{ date_format_view($payment_collection->formulir->request_approval_at, true) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {!! $list_payment_collection->render() !!}
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
