@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-sales::app.sales.point.service._breadcrumb')
            <li><a href="{{ url('sales/point/service/payment-collection') }}">Payment Collection</a></li>
            <li>Create step 1</li>
        </ul>
        <h2 class="sub-header">Payment Collection</h2>
        @include('point-sales::app.sales.point.service.payment-collection._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                <div class="table-responsive">
                    {!! $list_invoice->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th width="100px" class="text-center"></th>
                            <th>Customer</th>
                            <th>Info Invoices</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($list_invoice as $invoice)
                            <tr id="list-{{$invoice->formulir_id}}">
                                <td class="text-center">
                                    <a href="{{ url('sales/point/service/payment-collection/create-step-2/'.$invoice->person_id) }}"
                                       class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-external-link"></i>
                                        Payment Collection</a>
                                </td>
                                <td>
                                    {!! get_url_person($invoice->person->id) !!}
                                </td>
                                <td>
                                    <?php
                                    $list_invoice_by_person = \Point\PointSales\Models\Service\Invoice::joinFormulir()
                                            ->joinPerson()
                                            ->notArchived()
                                            ->availableToPaymentCollection()
                                            ->where('person_id', '=', $invoice->person_id)
                                            ->selectOriginal()
                                            ->orderByStandard()
                                            ->get();
                                    ?>
                                    @foreach($list_invoice_by_person as $invoice_by_person)
                                        {{date_Format_view($invoice_by_person->formulir->form_date)}} <a
                                                href="{{url('sales/point/service/invoice/'.$invoice_by_person->id)}}">{{$invoice_by_person->formulir->form_number}}</a>
                                        <br/>
                                    @endforeach
                                </td>
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
