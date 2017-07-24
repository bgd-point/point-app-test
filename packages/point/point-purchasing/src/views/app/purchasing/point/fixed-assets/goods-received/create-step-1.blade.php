@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.fixed-assets._breadcrumb')
            <li><a href="{{ url('purchasing/point/fixed-assets/goods-received') }}">Goods Received</a></li>
            <li>Create step 1</li>
        </ul>
        <h2 class="sub-header">Goods Received | Fixed Assets</h2>
        @include('point-purchasing::app.purchasing.point.fixed-assets.goods-received._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                @if(! $list_purchase_include_expedition->count() && ! $list_purchase_exclude_expedition->count())
                    Please make an order first
                @endif

                @if($list_purchase_include_expedition->count())
                    <div class="table-responsive">
                        <h3>Goods Received picked by Supplier / Direct Deliver</h3>
                        {!! $list_purchase_include_expedition->render() !!}
                        <table class="table table-striped table-bordered">
                            <thead>
                            <tr>
                                <th width="100px" class="text-center"></th>
                                <th>Form Date</th>
                                <th>Form Number</th>
                                <th>Notes</th>
                                <th>Supplier</th>
                                <th>Order</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list_purchase_include_expedition as $purchase_order)
                            <tr>
                                <td class="text-center">
                                    @if($purchase_order->is_cash == 1)
                                        @if($purchase_order->getTotalRemainingDownpayment($purchase_order->id) > 0)
                                            <a href="{{ url('purchasing/point/fixed-assets/goods-received/create-step-2/'.$purchase_order->id) }}" class="btn btn-effect-ripple btn-xs btn-info">
                                                <i class="fa fa-external-link"></i> Create
                                            </a>
                                        @else
                                            {{ $purchase_order->checkDownpayment() }}
                                        @endif
                                    @else
                                    <a href="{{ url('purchasing/point/fixed-assets/goods-received/create-step-2/'.$purchase_order->id) }}" class="btn btn-effect-ripple btn-xs btn-info">
                                        <i class="fa fa-external-link"></i> Create
                                    </a>
                                    @endif
                                </td>
                                <td>{{ date_format_view($purchase_order->formulir->form_date) }}</td>
                                <td>
                                    <a href="{{ url('purchasing/point/fixed-assets/purchase-order/'.$purchase_order->id) }}">{{ $purchase_order->formulir->form_number}}</a>
                                </td>
                                <td>{{$purchase_order->formulir->notes}}</td>
                                <td>
                                    <a href="{{ url('master/contact/supplier/'.$purchase_order->supplier_id) }}">{{ $purchase_order->supplier->codeName}}</a>
                                </td>
                                <td>
                                    @foreach($purchase_order->details as $detail)
                                        {{ $detail->coa->name }}
                                        {{ $detail->name }}
                                        = {{ number_format_quantity($detail->quantity) }} {{$detail->unit}}
                                        <br/>
                                    @endforeach
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {!! $list_purchase_include_expedition->render() !!}
                    </div>
                @endif

                @if($list_purchase_exclude_expedition->count())
                    {!! $list_purchase_exclude_expedition->render() !!}
                    <div class="table-responsive">
                        <h3>Goods Received picked by Expedition</h3>
                        <table class="table table-striped table-bordered">
                            <thead>
                            <tr>
                                <th width="100px" class="text-center"></th>
                                <th>Form Date</th>
                                <th>Form Number</th>
                                <th>Notes</th>
                                <th>Supplier</th>
                                <th>Order</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list_purchase_exclude_expedition as $purchase_expedition_order)
                                <tr>
                                    <td class="text-center">
                                        <?php $purchase_expedition_order->createDeliveryFromExpedition(); ?>
                                    </td>
                                    <td>{{ date_format_view($purchase_expedition_order->formulir->form_date) }}</td>
                                    <td>
                                        <a href="{{ url('purchasing/point/fixed-assets/purchase-order/'.$purchase_expedition_order->id) }}">{{ $purchase_expedition_order->formulir->form_number}}</a>
                                    </td>
                                    <td>{{$purchase_expedition_order->formulir->notes}}</td>
                                    <td>
                                        <a href="{{ url('master/contact/supplier/'.$purchase_expedition_order->supplier_id) }}">{{ $purchase_expedition_order->supplier->codeName}}</a>
                                    </td>
                                    <td>
                                        @foreach($purchase_expedition_order->items as $purchase_expedition_order_item)
                                            {{ $purchase_expedition_order_item->item->codeName }}
                                            = {{ number_format_quantity($purchase_expedition_order_item->quantity) }} {{ $purchase_expedition_order_item->unit }}
                                            <br/>
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    {!! $list_purchase_exclude_expedition->render() !!}
                @endif
            </div>
        </div>
    </div>
@stop
