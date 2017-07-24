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
            @include('point-purchasing::app.purchasing.point.fixed-assets._breadcrumb')
            <li><a href="{{ url('purchasing/point/fixed-assets/purchase-order') }}">Purchase Order</a></li>
            <li>Request approval</li>
        </ul>
        <h2 class="sub-header">Purchase Order</h2>
        @include('point-purchasing::app.purchasing.point.fixed-assets.purchase-order._menu')

        <form action="{{url('purchasing/point/fixed-assets/purchase-order/send-request-approval')}}" method="post">
            {!! csrf_field() !!}

            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="table-responsive">
                        {!! $list_purchase_order->render() !!}
                        <table class="table table-striped table-bordered">
                            <thead>
                            <tr>
                                <th width="100px" class="text-center">
                                    <input type="checkbox" id="check-all">
                                </th>
                                <th>Form Date</th>
                                <th>Form Number</th>
                                <th>Supplier</th>
                                <th>Order</th>
                                <th>Last Request</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list_purchase_order as $purchase_order)
                                <tr id="list-{{$purchase_order->formulir_id}}">
                                    <td class="text-center">
                                        <input type="checkbox" name="formulir_id[]"
                                               value="{{$purchase_order->formulir_id}}">
                                    </td>
                                    <td>{{ date_format_view($purchase_order->formulir->form_date) }}</td>
                                    <td>
                                        <a href="{{ url('purchasing/point/fixed-assets/purchase-order/'.$purchase_order->id) }}">{{ $purchase_order->formulir->form_number}}</a>
                                    </td>
                                    <td>
                                        <a href="{{ url('master/contact/supplier/'.$purchase_order->supplier_id) }}">{{ $purchase_order->supplier->codeName}}</a>
                                    </td>
                                    <td>
                                        @foreach($purchase_order->details as $detail)
                                            - {{ $detail->coa->name }} {{ $detail->name }}
                                            = {{ number_format_quantity($detail->quantity) }} {{ $detail->unit }}
                                            <br/>
                                        @endforeach
                                    </td>
                                    <td>
                                        @if($purchase_order->formulir->request_approval_at != '0000-00-00 00:00:00')
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
