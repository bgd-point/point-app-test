@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li><a href="{{ url('purchasing/point/goods-received') }}">Goods Received</a></li>
            <li>Create step 2</li>
        </ul>
        <h2 class="sub-header">Goods Received</h2>
        @include('point-purchasing::app.purchasing.point.inventory.goods-received._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                @if(! $list_purchase_include_expedition->count() && ! $purchase_order_exclude_expedition->count())
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
                                <th>Form Number</th>
                                <th>Supplier</th>
                                <th>Item</th>
                                <th>Notes</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list_purchase_include_expedition as $purchase_order)
                            <tr>
                                <td class="text-center">
                                    @if($purchase_order->is_cash == 1)
                                        @if($purchase_order->getTotalRemainingDownpayment($purchase_order->id) > 0)
                                            <a href="{{ url('purchasing/point/goods-received/create-step-4/'.$purchase_order->id) }}" class="btn btn-effect-ripple btn-xs btn-info">
                                                <i class="fa fa-external-link"></i> CREATE GOODS RECEIVED
                                            </a>
                                        @else
                                            {{ $purchase_order->checkDownpayment() }}
                                        @endif
                                    @else
                                    <a href="{{ url('purchasing/point/goods-received/create-step-4/'.$purchase_order->id) }}" class="btn btn-effect-ripple btn-xs btn-info">
                                        <i class="fa fa-external-link"></i> CREATE GOODS RECEIVED
                                    </a>
                                    @endif
                                </td>
                                <td>
                                    {{ date_format_view($purchase_order->formulir->form_date) }} <br>
                                    <a href="{{ url('purchasing/point/purchase-order/'.$purchase_order->id) }}">{{ $purchase_order->formulir->form_number}}</a>
                                </td>
                                <td>{!! get_url_person($purchase_order->supplier_id) !!}</td>
                                <td>
                                    <ul style="list-style:none; padding:0">
                                        @foreach($purchase_order->items as $purchase_order_item)
                                            <li> - {{$purchase_order_item->item->codeName}} {{$purchase_order_item->quantity}} {{$purchase_order_item->unit}} </li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td>{{$purchase_order->formulir->notes}}</td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {!! $list_purchase_include_expedition->render() !!}
                    </div>
                @endif

                @if($purchase_order_exclude_expedition->count())
                    {!! $list_purchase_exclude_expedition['expedition_order']->render() !!}
                    <div class="table-responsive">
                        <h3>Goods Received picked by Expedition</h3>
                        <table class="table table-striped table-bordered">
                            <thead>
                            <tr>
                                <th width="100px" class="text-center"></th>
                                <th>Form Number</th>
                                <th>Supplier</th>
                                <th>Item</th>
                                <th>Notes</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list_purchase_exclude_expedition['expedition_order'] as $expedition_order)
                            <?php
                            if (! $expedition_order->reference()->checkExpeditionReference($expedition_order->reference()->formulir_id)) {
                                continue;
                            }
                            ?>
                                <tr>
                                    <td class="text-center">
                                        @if($expedition_order->reference()->is_cash == 1)
                                            @if($expedition_order->reference()->getTotalRemainingDownpayment($expedition_order->reference()->id) > 0)
                                                <a class="btn btn-effect-ripple btn-xs btn-info" href="{{url('purchasing/point/goods-received/create-step-3/'.$expedition_order->reference()->id)}}"> CREATE GOODS RECEIVED </a>
                                            @else
                                                {{ $expedition_order->reference()->checkDownpayment() }}
                                            @endif
                                        @else
                                            <a class="btn btn-effect-ripple btn-xs btn-info" href="{{url('purchasing/point/goods-received/create-step-3/'.$expedition_order->reference()->id)}}"> CREATE GOODS RECEIVED </a>
                                        @endif
                                    </td>
                                    <td>
                                        {{ date_format_view($expedition_order->reference()->formulir->form_date) }} <br>
                                        <a href="{{get_class($expedition_order->reference())::showUrl($expedition_order->reference()->id)}}"> {{ $expedition_order->reference()->formulir->form_number }}</a>
                                    </td>
                                    <td>{!! get_url_person($expedition_order->reference()->supplier_id) !!}</td>
                                    <td>
                                        <ul style="list-style:none; padding:0">
                                        @foreach($expedition_order->reference()->items as $purchase_order_item)
                                            <li> - {{$purchase_order_item->item->codeName}} {{$purchase_order_item->quantity}} {{$purchase_order_item->unit}} </li>
                                        @endforeach
                                        </ul>
                                    </td>
                                    <td>{{$expedition_order->reference()->formulir->notes}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    {!! $list_purchase_exclude_expedition['expedition_order']->render() !!}
                @endif
            </div>
        </div>
    </div>
@stop
