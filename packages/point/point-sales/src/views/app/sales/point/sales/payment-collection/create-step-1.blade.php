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
                            <th>Details</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="3">From Invoice</td>
                        </tr>
                        @foreach($list_invoice as $invoice)
                            <tr id="list-{{$invoice->formulir_id}}">
                                <td class="text-center">
                                    <a href="{{ url('sales/point/indirect/payment-collection/create-step-2/'.$invoice->person_id) }}"
                                       class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-external-link"></i>
                                        Payment Collection</a>
                                </td>
                                <td>
                                    {!! get_url_person($invoice->person_id) !!}
                                </td>
                                <td>
                                    <?php
                                    $list_invoice_by_person = \Point\PointSales\Models\Sales\Invoice::joinFormulir()
                                            ->joinPerson()
                                            ->notArchived()
                                            ->availableToPaymentCollection()
                                            ->where('person_id', '=', $invoice->person_id)
                                            ->selectOriginal()
                                            ->orderByStandard()
                                            ->get();
                                    ?>
                                    @foreach($list_invoice_by_person as $invoice_by_person)
                                        {{date_format_view($invoice_by_person->formulir->form_date)}} <a
                                                href="{{url('sales/point/indirect/invoice/'.$invoice_by_person->id)}}">{{$invoice_by_person->formulir->form_number}}</a>
                                        <br/>
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach

                        <tr>
                            <td colspan="3">From Cutoff</td>
                        </tr>
                        @foreach($list_cut_off_receivable as $cut_off_receivable)
                        <?php
                        $cut_off_receivable_remaining = \Point\Framework\Helpers\ReferHelper::remaining(get_class($cut_off_receivable), $cut_off_receivable->id, $cut_off_receivable->amount);
                        if (! $cut_off_receivable_remaining > 0) {
                            continue;
                        }
                        ?>
                        <tr id="list-{{$cut_off_receivable->formulir_id}}">
                            <td class="text-center">
                                <a href="{{ url('sales/point/indirect/payment-collection/create-step-2/'.$cut_off_receivable->subledger_id) }}"
                                   class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-external-link"></i>
                                    Payment Collection</a>
                            </td>
                            <td>
                                <a href="{{ url('master/contact/person/'.$cut_off_receivable->subledger_id) }}">{{ $cut_off_receivable->person->codeName}}</a>
                            </td>
                            <td>
                                {{date_format_view($cut_off_receivable->cutoffReceivable->formulir->form_date)}}
                                <a href="{{url('accounting/point/cut-off/receivable/'.$cut_off_receivable->cutoffReceivable->id)}}"> {{$cut_off_receivable->cutoffReceivable->formulir->form_number}}</a>
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
