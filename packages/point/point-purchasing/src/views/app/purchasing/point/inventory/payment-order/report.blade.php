@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li><a href="{{ url('purchasing/point/payment-order') }}">Payment Order</a></li>
            <li>Report</li>
        </ul>
        <h2 class="sub-header">PAYMENT ORDER</h2>
        @include('point-purchasing::app.purchasing.point.inventory.payment-order._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <td>date</td>
                            <td>from invoice</td>
                            <td>supplier</td>
                            <td>status</td>
                            <td style="text-align: right;">remaining</td>
                        </thead>
                        <tbody>
                            @foreach($list_invoice as $invoice)
                            <?php
                                $invoice_remaining = \Point\Framework\Helpers\ReferHelper::remaining(get_class($invoice),
                                    $invoice->id, $invoice->total);
                            ?>
                                <tr>
                                    <td>
                                        {{ date_Format_view($invoice->formulir->form_date) }}
                                    </td>
                                    <td>
                                        {{ $invoice->formulir->form_number }}
                                    </td>
                                    <td>
                                        {!! get_url_person($invoice->supplier_id) !!}
                                    </td>
                                    <td>
                                        @include('framework::app.include._approval_status_label', ['approval_status' => $invoice->formulir->approval_status])
                                        @include('framework::app.include._form_status_label', ['form_status' => $invoice->formulir->form_status])
                                    </td>
                                    <td style="text-align: right;">
                                        {{ number_format_price($invoice_remaining) }}
                                    </td>
                                </tr>
                            @endforeach                            
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop


@section('scripts')
@stop
