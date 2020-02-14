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
                <select id="itemSearch" name="item" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectItem()">
                    <option value=""></option>
                    @foreach(\Point\Framework\Models\Master\Item::all() as $item)
                        <option value="{{ $item->id }}" @if(request()->get('item_id') == $item->id) selected @endif>[{{ $item->code }}] {{ $item->name }}</option>
                    @endforeach
                </select>
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
                                <th>#</th>
                                <th>Date</th>
                                <th>Supplier</th>
                                <th>Item</th>
                                <th>Qty. Order</th>
                                <th>Qty. Pending</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $totalQtyPending = 0;?>
                            <?php $totalQtyOrder = 0;?>
                            @foreach($list_purchase_include_expedition as $purchase_order)
                            @foreach($purchase_order->items as $purchase_order_item)
                                <?php $deliver_qty = Point\Framework\Helpers\ReferHelper::remaining(get_class($purchase_order_item), $purchase_order_item->id, $purchase_order_item->quantity);?>
                            @if($deliver_qty > 0 && $purchase_order_item->item_id == request()->get('item_id'))
                                <?php
                                $totalQtyPending += $deliver_qty;
                                $totalQtyOrder += $purchase_order_item->quantity;
                                ?>
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
                                <td><a href="{{ url('purchasing/point/purchase-order/'.$purchase_order->id) }}">{{ $purchase_order->formulir->form_number}}</a></td>
                                <td>{{ date_format_view($purchase_order->formulir->form_date) }} <br></td>
                                <td>{!! get_url_person($purchase_order->supplier_id) !!}</td>
                                <td>{{$purchase_order_item->item->codeName}}</td>
                                <td class="text-right">{{number_format_accounting($purchase_order_item->quantity)}}</td>
                                <td class="text-right">{{number_format_accounting($deliver_qty)}}</td>
                            </tr>
                            @endif
                            @endforeach
                            @endforeach
                            <tr>
                                <td colspan="5"></td>
                                <td class="text-right">{{ number_format_accounting($totalQtyOrder) }}</td>
                                <td class="text-right">{{ number_format_accounting($totalQtyPending) }}</td>
                            </tr>
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
                                @foreach($expedition_order->reference()->items as $purchase_order_item)
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
                                        {{$purchase_order_item->item->codeName}} {{ number_format_accounting($purchase_order_item->quantity) }} {{$purchase_order_item->unit}}
                                    </td>
                                    <td>{{$expedition_order->reference()->formulir->notes}}</td>
                                </tr>
                            @endforeach
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

@section('scripts')
    <script>
        function selectItem() {
            window.location.replace("/purchasing/point/goods-received/create-step-2/{{ $supplier_id }}/?item_id="+document.getElementById('itemSearch').value)
        }
    </script>
@stop

