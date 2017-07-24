@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-expedition::app/expedition/point/payment-order/_breadcrumb')
            <li>Create Step 1</li>
        </ul>
        <h2 class="sub-header">PAYMENT ORDER</h2>
        @include('point-expedition::app.expedition.point.payment-order._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                <div class="table-responsive">
                    {!! $list_invoice->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th width="100px" class="text-center"></th>
                            <th>Expedition</th>
                            <th>Info Invoices</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="3">From Invoice</td>
                        </tr>
                        
                        @foreach($list_invoice as $invoice)
                            <tr id="list-{{$invoice->formulir_id}}">
                                <td class="text-center">
                                    <a href="{{ url('expedition/point/payment-order/create-step-2/'.$invoice->expedition_id) }}"
                                       class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-external-link"></i>
                                        PAYMENT ORDER</a>
                                </td>
                                <td><a href="{{ url('master/contact/expedition/'.$invoice->expedition_id) }}">{{ $invoice->expedition->codeName}}</a></td>
                                <td><?php $invoice->getListExpedition();?></td>
                            </tr>
                        @endforeach
                        @if(count($list_cut_off_payable) > 0)
                        <tr>
                            <td colspan="3">From Cutoff</td>
                        </tr>
                        @foreach($list_cut_off_payable as $cut_off_payable)
                        <?php
                        $cut_off_payable_remaining = \Point\Framework\Helpers\ReferHelper::remaining(get_class($cut_off_payable), $cut_off_payable->id, $cut_off_payable->amount);
                        if (! $cut_off_payable_remaining > 0) {
                            continue;
                        }
                        ?>
                        <tr id="list-{{$cut_off_payable->formulir_id}}">
                            <td class="text-center">
                                <a href="{{ url('expedition/point/payment-order/create-step-2/'.$cut_off_payable->subledger_id) }}"
                                   class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-external-link"></i>
                                    Payment Order</a>
                            </td>
                            <td>
                                {!! get_url_person($cut_off_payable->person->id) !!}
                            </td>
                            <td>
                                {{date_format_view($cut_off_payable->cutoffPayable->formulir->form_date)}}
                                <a href="{{url('accounting/point/cut-off/payable/'.$cut_off_payable->cutoffPayable->id)}}"> {{$cut_off_payable->cutoffPayable->formulir->form_number}}</a>
                            </td>
                        </tr>
                        @endforeach
                        @endif
                        </tbody>
                    </table>
                    {!! $list_invoice->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop
