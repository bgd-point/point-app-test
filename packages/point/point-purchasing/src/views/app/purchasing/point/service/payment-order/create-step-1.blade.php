@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.service._breadcrumb')
            <li><a href="{{ url('purchasing/point/service/payment-order') }}">Payment Order</a></li>
            <li>Create step 1</li>
        </ul>
        <h2 class="sub-header">Payment Order</h2>
        @include('point-purchasing::app.purchasing.point.service.payment-order._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                <div class="table-responsive">
                    {!! $list_invoice->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th width="100px" class="text-center"></th>
                            <th>Supplier</th>
                            <th>Invoice Date</th>
                            <th>Invoice Number</th>
                            <th>Description</th>
                            <th>Amount</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($list_invoice as $invoice)
                        <?php
                        $list_invoice_by_person = \Point\PointPurchasing\Models\Service\Invoice::joinFormulir()
                                ->joinPerson()
                                ->notArchived()
                                ->availableToPaymentOrder()
                                ->where('person_id', '=', $invoice->person_id)
                                ->selectOriginal()
                                ->orderByStandard()
                                ->get();
                        ?>
                        @foreach($list_invoice_by_person as $invoice_by_person)
                            <?php
                            $amount = number_format_price(\Point\Framework\Helpers\ReferHelper::remaining(get_class($invoice_by_person), $invoice_by_person->id, $invoice_by_person->total), 0);
                            ?>
                            @if ($amount > 0)
                            <tr id="list-{{$invoice->formulir_id}}">
                                <td class="text-center">
                                    <a href="{{ url('purchasing/point/service/payment-order/create-step-2/'.$invoice->person_id) }}"
                                       class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-external-link"></i>
                                        Payment Order</a>
                                </td>
                                <td>
                                    {!! get_url_person($invoice->person_id) !!}
                                </td>
                                <td>{{date_Format_view($invoice_by_person->formulir->form_date)}} </td>
                                <td>
                                    <a href="{{url('purchasing/point/service/invoice/'.$invoice_by_person->id)}}">{{$invoice_by_person->formulir->form_number}}</a>
                                </td>
                                <td>
                                @foreach($invoice_by_person->services as $service)
                                {{ $service->service_notes }}
                                @if ($invoice_by_person->services->count() > 1)
                                ,
                                @endif
                                @endforeach
                                </td>
                                <td>{{ $amount }}</td>
                            </tr>
                            @endif
                            @endforeach
                        @endforeach
                        </tbody>
                    </table>
                    {!! $list_invoice->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop
