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
            <li><a href="{{ url('purchasing/point/service/invoice') }}">Purchase Order</a></li>
            <li>Request approval</li>
        </ul>
        <h2 class="sub-header">Purchase Order</h2>
        @include('point-purchasing::app.purchasing.point.service.purchase-order._menu')

        <form action="{{url('purchasing/point/service/purchase-order/send-request-approval')}}" method="post">
            {!! csrf_field() !!}

            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="table-responsive">
                        {!! $list_purchase_order->render() !!}
                        <table class="table table-striped table-bordered">
                            <thead>
                            <tr>
                                <th class="text-center">
                                    <input type="checkbox" id="check-all">
                                </th>
                                <th>Form Date</th>
                                <th>Form Number</th>
                                <th>Supplier</th>
                                <th>Amount</th>
                                <th>Last Request</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list_purchase_order as $purchase_order)
                                <tr id="list-{{ $purchase_order->formulir_id }}">
                                    <td class="text-center">
                                        <input type="checkbox" name="formulir_id[]"
                                               value="{{ $purchase_order->formulir_id }}">
                                    </td>
                                    <td>{{ date_format_view($purchase_order->formulir->form_date) }}</td>
                                    <td>
                                        <a href="{{ url('purchasing/point/service/invoice/'.$purchase_order->id) }}">
                                            {{ $purchase_order->formulir->form_number}}
                                        </a>
                                    </td>
                                    <td>
                                        {!! get_url_person($purchase_order->person->id) !!}
                                    </td>
                                    <td>{{ number_format_quantity($purchase_order->total) }}</td>
                                    <td>
                                        @if($purchase_order->formulir->request_approval_at != null)
                                            {{ date_format_view($purchase_order->formulir->request_approval_at, true) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {!! $list_purchase_order->render() !!}
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
