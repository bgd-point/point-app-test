@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-sales::app.sales.point.sales._breadcrumb')
            <li><a href="{{ url('sales/point/indirect/delivery-order') }}">Delivery Order</a></li>
            <li>Create step 1</li>
        </ul>
        <h2 class="sub-header">Delivery Order</h2>
        @include('point-sales::app.sales.point.sales.delivery-order._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                @if(! $list_sales_include_expedition->count() && ! $list_sales_exclude_expedition->count())
                    Please make an order first
                @endif

                @if($list_sales_include_expedition->count())
                    <div class="table-responsive">
                        <h3>Delivery Order picked by Customer / Direct Deliver</h3>
                        {!! $list_sales_include_expedition->render() !!}
                        <table class="table table-striped table-bordered">
                            <thead>
                            <tr>
                                <th width="100px" class="text-center"></th>
                                <th>Form Date</th>
                                <th>Form Number</th>
                                <th>Notes</th>
                                <th>Customer</th>
                                <th>Order</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list_sales_include_expedition as $sales_order)
                                <?php $dp_paid = Point\PointSales\Models\Sales\Downpayment::joinFormulir()->PaymentFinished($sales_order->id)->first(); ?>

                                    <tr>
                                        <td class="text-center">
                                            @if($sales_order->is_cash == 1)
                                                @if($sales_order->getTotalRemainingDownpayment($sales_order->id) > 0)
                                                    <a href="{{ url('sales/point/indirect/delivery-order/create-step-2/'.$sales_order->id) }}" class="btn btn-effect-ripple btn-xs btn-info">
                                                        <i class="fa fa-external-link"></i> Create
                                                    </a>
                                                @else
                                                    {{ $sales_order->checkDownpayment() }}
                                                @endif
                                            @else
                                            <a href="{{ url('sales/point/indirect/delivery-order/create-step-2/'.$sales_order->id) }}" class="btn btn-effect-ripple btn-xs btn-info">
                                                <i class="fa fa-external-link"></i> Create
                                            </a>
                                            @endif
                                        </td>
                                        <td>{{ date_format_view($sales_order->formulir->form_date) }}</td>
                                        <td>
                                            <a href="{{ url('sales/point/indirect/sales-order/'.$sales_order->id) }}">{{ $sales_order->formulir->form_number}}</a>
                                        </td>
                                        <td>{{$sales_order->formulir->notes}}</td>
                                        <td>
                                            <a href="{{ url('master/contact/person/'.$sales_order->person_id) }}">{{ $sales_order->person->codeName}}</a>
                                        </td>
                                        <td>
                                            @foreach($sales_order->items as $sales_order_item)
                                                {{ $sales_order_item->item->codeName }}
                                                = {{ number_format_quantity($sales_order_item->quantity) }} {{ $sales_order_item->unit }}
                                                <br/>
                                            @endforeach
                                        </td>
                                    </tr>

                            @endforeach
                            </tbody>
                        </table>
                        {!! $list_sales_include_expedition->render() !!}
                    </div>
                @endif

                @if($list_sales_exclude_expedition->count())
                    {!! $list_sales_exclude_expedition->render() !!}
                    <div class="table-responsive">
                        <h3>Delivery Order picked by Expedition</h3>
                        <table class="table table-striped table-bordered">
                            <thead>
                            <tr>
                                <th width="100px" class="text-center"></th>
                                <th>Form Date</th>
                                <th>Form Number</th>
                                <th>Notes</th>
                                <th>Customer</th>
                                <th>Order</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list_sales_exclude_expedition as $sales_expedition_order)
                                <tr>
                                    <td class="text-center">
                                        <?php $sales_expedition_order->createDeliveryFromExpedition(); ?>
                                    </td>
                                    <td>{{ date_format_view($sales_expedition_order->formulir->form_date) }}</td>
                                    <td>
                                        <a href="{{ url('sales/point/indirect/sales-order/'.$sales_expedition_order->id) }}">{{ $sales_expedition_order->formulir->form_number}}</a>
                                    </td>
                                    <td>{{$sales_expedition_order->formulir->notes}}</td>
                                    <td>
                                        <a href="{{ url('master/contact/person/'.$sales_expedition_order->person_id) }}">{{ $sales_expedition_order->person->codeName}}</a>
                                    </td>
                                    <td>
                                        @foreach($sales_expedition_order->items as $sales_expedition_order_item)
                                            {{ $sales_expedition_order_item->item->codeName }}
                                            = {{ number_format_quantity($sales_expedition_order_item->quantity) }} {{ $sales_expedition_order_item->unit }}
                                            <br/>
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    {!! $list_sales_exclude_expedition->render() !!}
                @endif
            </div>
        </div>
    </div>
@stop
