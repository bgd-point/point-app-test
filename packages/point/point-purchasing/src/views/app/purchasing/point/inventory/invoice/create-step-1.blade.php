@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li><a href="{{ url('purchasing/point/invoice') }}">Invoice</a></li>
            <li>Create step 1/li>
        </ul>
        <h2 class="sub-header">Invoice</h2>
        @include('point-purchasing::app.purchasing.point.inventory.invoice._menu')
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="table-responsive">
                    {!! $list_goods_received->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th width="100px" class="text-center"></th>
                            <th>DATE</th>
                            <th>FORM NUMBER</th>
                            <th>SUPPLIER</th>
                            <th>WAREHOUSE</th>
                            <th>ITEMS</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $i=0; ?>
                        @foreach($list_goods_received as $goods_received)
                            <tr id="list-{{$goods_received->formulir_id}}">
                                <td class="text-center">
                                    <a href="{{ url('purchasing/point/invoice/create-step-2/'.$goods_received->id) }}"class="btn btn-effect-ripple btn-xs btn-info">
                                       <i class="fa fa-external-link"></i>
                                       Create Invoice
                                    </a>
                                </td>
                                <td>{{ date_format_view($goods_received->formulir->form_date) }}</td>
                                <td>
                                    <a href="{{ url('purchasing/point/goods-received/'.$goods_received->id) }}">{{ $goods_received->formulir->form_number}}</a>
                                </td>
                                <td>
                                    {!! get_url_person($goods_received->supplier_id) !!}
                                </td>
                                <td>
                                    <a href="{{ url('master/warehouse/'.$goods_received->warehouse_id) }}">{{ $goods_received->warehouse->codeName}}</a>
                                </td>
                                <td>
                                    @foreach($goods_received->items as $goods_received_item)
                                        {{ $goods_received_item->item->codeName }}
                                        = {{ number_format_quantity($goods_received_item->quantity) }} {{ $goods_received_item->unit }}
                                        <br/>
                                    @endforeach
                                </td>
                            </tr>
                        <?php $i++; ?>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $list_goods_received->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop