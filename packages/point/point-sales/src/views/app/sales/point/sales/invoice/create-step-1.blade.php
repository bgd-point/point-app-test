@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-sales::app.sales.point.sales._breadcrumb')
            <li><a href="{{ url('sales/point/indirect/invoice') }}">Invoice</a></li>
            <li>Create step 1</li>
        </ul>
        <h2 class="sub-header">Invoice</h2>
        @include('point-sales::app.sales.point.sales.invoice._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                <a href="{{ url('sales/point/indirect/invoice/basic/create') }}" class="btn btn-info">
                    Create New Invoice
                </a>
                @if($list_delivery_order->count())
                <br><br>
                <h3>Create From Delivery Order</h3>
                <div class="table-responsive">
                    {!! $list_delivery_order->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th width="100px" class="text-center"></th>
                            <th>Customer</th>
                            <th>Delivered Goods Reference</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($list_delivery_order as $delivery_order)

                            <tr id="list-{{$delivery_order->formulir_id}}">
                                <td class="text-center">
                                    <a href="{{ url('sales/point/indirect/invoice/create-step-2/'.$delivery_order->person_id) }}"
                                       class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-external-link"></i>
                                        Create Invoice</a>
                                </td>
                                <td>
                                    {!! get_url_person($delivery_order->person_id) !!}
                                </td>
                                <td>
                                    <?php
                                    $list_delivery_order_single = Point\PointSales\Models\Sales\DeliveryOrder::joinFormulir()
                                            ->availableToInvoice($delivery_order->person_id)
                                            ->selectOriginal()
                                            ->get();
                                    ?>

                                    <ol>
                                        @foreach($list_delivery_order_single as $delivery_order_single)
                                            <li>
                                                <a href="{{ url('sales/point/indirect/delivery-order/'.$delivery_order_single->id) }}">{{ $delivery_order_single->formulir->form_number }}</a>
                                            </li>
                                        @endforeach
                                    </ol>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $list_delivery_order->render() !!}
                </div>
                @endif
            </div>
        </div>
    </div>
@stop
