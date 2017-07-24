@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.fixed-assets._breadcrumb')
            <li><a href="{{ url('purchasing/point/fixed-assets/invoice') }}">Invoice</a></li>
            <li>Create step 1</li>
        </ul>
        <h2 class="sub-header">Invoice | Fixed Assets</h2>
        @include('point-purchasing::app.purchasing.point.fixed-assets.invoice._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                @if(client_has_addon('basic'))
                <a href="{{ url('purchasing/point/fixed-assets/invoice/basic/create') }}" class="btn btn-info">
                    Create New Invoice
                </a>
                @endif
                @if($list_goods_received->count())
                <br><br>
                <h3>Create From Delivery Order</h3>
                <div class="table-responsive">
                    {!! $list_goods_received->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th width="100px" class="text-center"></th>
                            <th>SUPPLIER</th>
                            <th>INFO RECEIVED</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($list_goods_received as $goods_received)

                            <tr id="list-{{$goods_received->formulir_id}}">
                                <td class="text-center">
                                    <a href="{{ url('purchasing/point/fixed-assets/invoice/create-step-2/'.$goods_received->supplier_id) }}"
                                       class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-external-link"></i>
                                        Create Invoice</a>
                                </td>
                                <td>
                                    <a href="{{ url('master/contact/supplier/'.$goods_received->supplier_id) }}">{{ $goods_received->supplier->codeName }}</a>
                                </td>
                                <td>
                                    <?php
                                    $list_goods_received_single = Point\PointPurchasing\Models\FixedAssets\FixedAssetsGoodsReceived::joinFormulir()
                                            ->availableToInvoice($goods_received->supplier_id)
                                            ->selectOriginal()
                                            ->get();
                                    ?>

                                    <ol>
                                        @foreach($list_goods_received_single as $goods_received_single)
                                            <li>
                                                <a href="{{ url('purchasing/point/fixed-assets/goods-received/'.$goods_received_single->id) }}">{{ $goods_received_single->formulir->form_number }}</a>
                                            </li>
                                        @endforeach
                                    </ol>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $list_goods_received->render() !!}
                </div>
                @endif
            </div>
        </div>
    </div>
@stop
