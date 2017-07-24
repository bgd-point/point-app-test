@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-expedition::app/expedition/point/expedition-order/_breadcrumb')
            <li>Create Step 1</li>
        </ul>
        <h2 class="sub-header">Downpayment</h2>
        @include('point-expedition::app.expedition.point.expedition-order._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                <div class="table-responsive">
                    {!! $list_expedition_order->render() !!}
                    <table class="table">
                        <thead>
                        <tr>
                            <th width="20px" class="text-center"></th>
                            <th>Formulir Number</th>
                            <th>Notes</th>
                            <th>Expedition</th>
                            <th>Amount</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($list_expedition_order as $expedition_order)
                            <tr>
                                <td class="text-center">
                                    <a href="{{ url('expedition/point/downpayment/create-step-2/'.$expedition_order->id) }}"class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-external-link"></i>
                                        Create Downpayment
                                    </a>
                                </td>
                                <td>
                                    {{ date_format_view($expedition_order->formulir->form_date)}} <br>
                                    {{ $expedition_order->formulir->form_number}}
                                    <br>
                                    <i class="fa fa-caret-down"></i> 
                                    <a data-toggle="collapse" href="#collapse{{$expedition_order->id}}"><small>Detail</small></a>
                                </td>
                                <td>{{ $expedition_order->formulir->notes}}</td>
                                <td>
                                    <a href="{{ url('master/contact/expedition/'.$expedition_order->expedition->id)}}">
                                        {{ $expedition_order->expedition->name}}
                                    </a>
                                </td>
                                <td>{{ number_format_quantity($expedition_order->total) }}</td>
                            <tr>
                            <tr>
                                <td colspan="5" style="border-top: none;">
                                    <div id="collapse{{$expedition_order->id}}" class="panel-collapse collapse">
                                        <b>Description</b>
                                        <ul class="list-group">
                                            @foreach($expedition_order->items as $list_order_item)
                                                <li class="list-group-item">
                                                    <small>
                                                        {{ $list_order_item->item->codeName }} 
                                                        <spanclass="pull-right">{{ number_format_quantity($list_order_item->quantity) }} {{ $list_order_item->unit }}</span>
                                                    </small>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </td>

                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $list_expedition_order->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop
