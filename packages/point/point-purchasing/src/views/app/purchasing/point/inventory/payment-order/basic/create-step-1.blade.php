@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li><a href="{{ url('purchasing/point/payment-order/basic') }}">Payment Order</a></li>
            <li>Create step 1</li>
        </ul>
        <h2 class="sub-header">PAYMENT ORDER</h2>
        @include('point-purchasing::app.purchasing.point.inventory.payment-order.basic._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                <div class="table-responsive">
                    {!! $list_invoice->render() !!}
                    <table class="table table-striped table-bordered table-vcenter">
                        <thead>
                        <tr>
                            <th width="100px" class="text-center"></th>
                            <th>Supplier</th>
                            <th>From Invoice</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($list_invoice as $invoice)
                            <tr id="list-{{$invoice->formulir_id}}">
                                <td class="text-center">
                                    <a href="{{ url('purchasing/point/payment-order/basic/create-step-2/'.$invoice->supplier_id) }}"
                                       class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-external-link"></i>
                                        PAYMENT ORDER</a>
                                </td>
                                <td>
                                    {!! get_url_person($invoice->supplier_id) !!}
                                </td>
                                <td> {{ $invoice->getListSupplier() }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $list_invoice->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop
