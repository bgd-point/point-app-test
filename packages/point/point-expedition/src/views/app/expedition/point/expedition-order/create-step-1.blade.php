@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-expedition::app/expedition/point/expedition-order/_breadcrumb')
            <li>Create Step 1</li>
        </ul>
        <h2 class="sub-header">Expedition Order</h2>
        @include('point-expedition::app.expedition.point.expedition-order._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                <div class="table-responsive">
                    {!! $expedition_collection->render() !!}
                    <table class="table">
                        <thead>
                        <tr>
                            <th width="100px" class="text-center"></th>
                            <th>Formulir Number</th>
                            <th>Notes</th>
                            <th>Supplier</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $i = 1;?>
                        @foreach($expedition_collection as $order)
                            <?php
                                $formulir = Point\Framework\Models\Formulir::find($order->expedition_reference_id);
                            ?>
                            <tr>
                                <td class="text-center" rowspan="2">
                                    @if($order->checkingDownpaymentReference() > 0)
                                    <a href="{{ url('expedition/point/expedition-order/create-step-2/'.$order->expedition_reference_id) }}"
                                       class="btn btn-effect-ripple btn-xs btn-info">
                                       <i class="fa fa-external-link"></i>Create Expedition Order
                                    </a>
                                    @else
                                    <a href="{{ url('sales/point/indirect/downpayment/insert/'. $order->formulir->formulirable_id) }}" class="btn btn-effect-ripple btn-xs btn-info" style="overflow: hidden; position: relative;" target="_blank"><i class="fa fa-external-link"></i> Create Downpayment</a>
                                    @endif
                                </td>
                                <td>
                                    {{ date_Format_view($formulir->form_date)}} <br>
                                    {{ $formulir->form_number}}
                                    <br>
                                    <i class="fa fa-caret-down"></i>
                                    <a data-toggle="collapse" href="#collapse{{$i}}"><small>Detail</small></a>
                                </td>
                                <td>{{ $formulir->notes}}</td>
                                <td> {!! get_url_person($order->person->id) !!}</td>
                            <tr>
                            <tr>
                                <td colspan="4" style="border-top: none;">
                                    <div id="collapse{{$i}}" class="panel-collapse collapse">
                                        <b>Description</b>
                                        <ul class="list-group">
                                            @foreach($order->items as $list_order_item)
                                                <li class="list-group-item">
                                                    <small>
                                                        {{ $list_order_item->item->codeName }} 
                                                        <span class="pull-right">{{ number_format_quantity($list_order_item->quantity) }} {{ $list_order_item->unit }}</span>
                                                    </small>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php $i++;?>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $expedition_collection->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop
