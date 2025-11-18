@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-sales::app.sales.point.sales._breadcrumb')
            <li><a href="{{ url('sales/point/indirect/payment-collection') }}">Payment Collection</a></li>
            <li>Create step 1</li>
        </ul>
        <h2 class="sub-header">Payment Collection</h2>
        @include('point-sales::app.sales.point.sales.payment-collection._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                <div class="table-responsive">
                    {!! $list_invoice->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th width="100px" class="text-center"></th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Form Number</th>
                            <th>Remaining</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="6">From Invoice</td>
                        </tr>
                        @foreach($list_invoice as $invoice)
                            <?php
                            $list_invoice_by_person = \Point\PointSales\Models\Sales\Invoice::joinFormulir()
                                ->joinPerson()
                                ->notArchived()
                                ->availableToPaymentCollection()
                                ->where('person_id', '=', $invoice->person_id)
                                ->selectOriginal()
                                ->orderByStandard()
                                ->get();
                            dd($list_invoice_by_person);
                            ?>
                            @foreach($list_invoice_by_person as $invoice_by_person)
                            <tr id="list-{{$invoice->formulir_id}}">
                                <td class="text-center">
                                    <a href="{{ url('sales/point/indirect/payment-collection/create-step-2/'.$invoice->person_id) }}"
                                       class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-external-link"></i>
                                        Payment Collection</a>
                                </td>
                                <td>{!! get_url_person($invoice->person_id) !!}</td>
                                <td>{{date_format_view($invoice_by_person->formulir->form_date)}}</td>
                                <td><a href="{{url('sales/point/indirect/invoice/'.$invoice_by_person->id)}}">{{$invoice_by_person->formulir->form_number}}</a></td>
                                <td style="text-align: right">{{ number_format_price(\Point\Framework\Helpers\ReferHelper::remaining(get_class($invoice_by_person), $invoice_by_person->id, $invoice_by_person->total), 0) }}</td>
                            </tr>
                            @endforeach
                        @endforeach

                        <tr>
                            <td colspan="3">From Cutoff</td>
                        </tr>
                        @foreach($list_cut_off_receivable as $cut_off_receivable)
                        <?php
                        $cut_off_receivable_detail = $cut_off_receivable->reference_type::find($cut_off_receivable->reference_id);
                        $reference_receivable = Point\PointAccounting\Models\CutOffReceivable::find($cut_off_receivable_detail->cut_off_receivable_id);
                        ?>
                        <tr>
                            <td class="text-center">
                                <a href="{{ url('sales/point/indirect/payment-collection/create-step-2/'.$cut_off_receivable->person_id) }}"
                                   class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-external-link"></i>
                                    Payment Collection</a>
                            </td>
                            <td>{!! get_url_person($cut_off_receivable->person_id) !!}</td>
                            <td>{{date_format_view($reference_receivable->formulir->form_date)}}</td>
                            <td><a href="{{url('accounting/point/cut-off/receivable/'.$reference_receivable->id)}}"> {{$reference_receivable->formulir->form_number}}</a></td>
                            <td></td>
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
