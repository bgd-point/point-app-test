@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.service._breadcrumb')
            <li><a href="{{ url('purchasing/point/service/invoice') }}">Invoice</a></li>
            <li>Create</li>
        </ul>
        <h2 class="sub-header">Invoice</h2>
        @include('point-purchasing::app.purchasing.point.service.invoice._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                <div class="table-responsive">
                    {!! $list_purchase_order->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th width="100px"></th>
                            <th>FORM NUMBER</th>
                            <th>SUPPLIER</th>
                            <th>DATE</th>
                            <th class="text-right">TOTAL</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($list_purchase_order as $purchase_order)

                            <tr>
                                <td class="text-center">
                                    <a
                                        href="{{ url('purchasing/point/service/invoice/create-step-2/'.$purchase_order->id) }}"
                                        class="btn btn-effect-ripple btn-xs btn-info">
                                        <i class="fa fa-external-link"></i> Create Invoice
                                    </a>
                                </td>
                                <td>
                                    {!! formulir_url($purchase_order->formulir) !!}
                                </td>
                                <td>
                                    {!! get_url_person($purchase_order->person->id) !!}
                                </td>
                                <td>
                                    {{ date_format_view($purchase_order->formulir->form_date) }}
                                </td>
                                <td class="text-right">
                                    {{ number_format_price($purchase_order->total) }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $list_purchase_order->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop
