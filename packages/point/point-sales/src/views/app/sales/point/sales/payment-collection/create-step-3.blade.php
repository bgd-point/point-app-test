@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-sales::app.sales.point.sales._breadcrumb')
            <li><a href="{{ url('sales/point/indirect/payment-collection') }}">Payment Collection</a></li>
            <li>Create step 3</li>
        </ul>
        <h2 class="sub-header">Payment Collection</h2>
        @include('point-sales::app.sales.point.sales.payment-collection._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('sales/point/indirect/payment-collection')}}" method="post"
                      class="form-horizontal form-bordered">
                    {!! csrf_field() !!}

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Payment Collection</legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <label class="col-md-3 control-label">PAYMENT DATE</label>

                        <div class="col-md-6 content-show">
                            <input type="hidden" name="payment_date" class="form-control" value="{{ $payment_date }}">
                            {{date_Format_view($payment_date, true)}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Customer</label>

                        <div class="col-md-6 content-show">
                            <input type="hidden" name="person_id" value="{{$person->id}}">
                            {!! get_url_person($person->id) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Payment Type</label>

                        <div class="col-md-6 content-show">
                            <input type="hidden" name="payment_type" value="{{$payment_type}}">
                            {{$payment_type}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>

                        <div class="col-md-6 content-show">
                            <input type="hidden" name="notes" value="{{$notes}}">
                            {{ $notes }}
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th>DATE</th>
                                        <th>FORM NUMBER</th>
                                        <th>NOTES</th>
                                        <th class="text-right">TOTAL</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $total_payment = 0;?>

                                    <!-- INVOICE -->
                                    @foreach($list_invoice as $invoice)
                                        <?php
                                        $i = array_search($invoice->formulir_id, $invoice_rid);
                                        $total_payment += $amount_invoice[$i];
                                        ?>
                                        <tr>
                                            <td>
                                                {{ date_Format_view($invoice->formulir->form_date) }}
                                                <input type="hidden" name="invoice_id[]" value="{{$invoice->id}}"/>
                                                <input type="hidden" name="invoice_notes[]"
                                                       value="{{$invoice->formulir->notes}}"/>
                                                <input type="hidden" name="invoice_amount[]"
                                                       value="{{$amount_invoice[$i]}}"/>
                                                <input type="hidden" name="invoice_amount_original[]"
                                                       value="{{$invoice->total}}"/>
                                                <input type="hidden" name="invoice_available[]"
                                                       value="{{$available_invoice[$i]}}"/>
                                                <input type="hidden" name="invoice_reference_id[]" value="{{$invoice->id}}">
                                                <input type="hidden" name="invoice_reference_type[]" value="{{get_class($invoice)}}">
                                            </td>
                                            <td>
                                                <a href="{{ url('sales/point/indirect/invoice/'.$invoice->id) }}">{{ $invoice->formulir->form_number}}</a>
                                            </td>
                                            <td>{{ $invoice->formulir->notes }}</td>
                                            <td class="text-right">{{ number_format_quantity($amount_invoice[$i]) }}</td>
                                        </tr>
                                    @endforeach

                                    <!-- CUTOFF -->
                                    @foreach($list_cut_off_receivable as $cut_off)
                                        <?php
                                        $i = array_search($cut_off->id, $cutoff_rid);
                                        $total_payment += $amount_cutoff[$i];
                                        ?>
                                        <tr>
                                            <td>
                                                {{ date_Format_view($cut_off->cutoffReceivable->formulir->form_date) }}
                                                <input type="hidden" name="cutoff_id[]" value="{{$cut_off->id}}"/>
                                                <input type="hidden" name="cutoff_notes[]"
                                                       value="{{$cut_off->notes}}"/>
                                                <input type="hidden" name="cutoff_amount[]"
                                                       value="{{$amount_cutoff[$i]}}"/>
                                                <input type="hidden" name="cutoff_amount_original[]"
                                                       value="{{$cut_off->amount}}"/>
                                                <input type="hidden" name="cutoff_available[]"
                                                       value="{{$available_cutoff[$i]}}"/>
                                                <input type="hidden" name="cutoff_reference_id[]" value="{{$cut_off->id}}">
                                                <input type="hidden" name="cutoff_reference_type[]" value="{{get_class($cut_off)}}">
                                            </td>
                                            <td>
                                                <a href="{{ url('accounting/point/cut-off/receivable/'.$cut_off->id)  }}">{{ $cut_off->cutoffReceivable->formulir->form_number}}</a>
                                            </td>
                                            <td>{{ $cut_off->notes }}</td>
                                            <td class="text-right">{{ number_format_quantity($amount_cutoff[$i]) }}</td>
                                        </tr>
                                    @endforeach

                                    <!-- DOWNPAYMENT -->
                                    @foreach($list_downpayment as $downpayment)
                                        <?php
                                        $i = array_search($downpayment->formulir_id, $downpayment_rid);
                                        $total_payment -= $amount_downpayment[$i];
                                        ?>
                                        <tr>
                                            <td>
                                                {{ date_Format_view($downpayment->formulir->form_date) }}
                                                <input type="hidden" name="downpayment_id[]"
                                                       value="{{$downpayment->id}}"/>
                                                <input type="hidden" name="downpayment_notes[]"
                                                       value="{{$downpayment->formulir->notes}}"/>
                                                <input type="hidden" name="downpayment_amount[]"
                                                       value="{{$amount_downpayment[$i] * -1}}"/>
                                                <input type="hidden" name="downpayment_amount_original[]"
                                                       value="{{$downpayment->amount}}"/>
                                                <input type="hidden" name="downpayment_available[]"
                                                       value="{{$available_downpayment[$i]}}"/>
                                                <input type="hidden" name="downpayment_reference_id[]" value="{{$downpayment->id}}">
                                                <input type="hidden" name="downpayment_reference_type[]" value="{{get_class($downpayment)}}">
                                            </td>
                                            <td>
                                                <a href="{{ url('sales/point/indirect/downpayment/'.$downpayment->id) }}">{{ $downpayment->formulir->form_number}}</a>
                                            </td>
                                            <td>{{ $downpayment->formulir->notes }}</td>
                                            <td class="text-right">{{ number_format_quantity($amount_downpayment[$i]*-1) }}</td>
                                        </tr>
                                        <?php $i++;?>
                                    @endforeach

                                    <!-- RETUR -->
                                    @foreach($list_retur as $retur)
                                        <?php
                                        $i = array_search($retur->formulir_id, $retur_rid);
                                        $total_payment -= $amount_retur[$i];
                                        ?>
                                        <tr>
                                            <td>
                                                {{ date_Format_view($retur->formulir->form_date) }}
                                                <input type="hidden" name="retur_id[]" value="{{$retur->id}}"/>
                                                <input type="hidden" name="retur_notes[]"
                                                       value="{{$retur->formulir->notes}}"/>
                                                <input type="hidden" name="retur_amount[]"
                                                       value="{{$amount_retur[$i] * -1}}"/>
                                                <input type="hidden" name="retur_amount_original[]"
                                                       value="{{$retur->total}}"/>
                                                <input type="hidden" name="retur_available[]"
                                                       value="{{$available_retur[$i]}}"/>
                                                <input type="hidden" name="retur_reference_id[]" value="{{$retur->id}}">
                                                <input type="hidden" name="retur_reference_type[]" value="{{get_class($retur)}}">
                                            </td>
                                            <td>
                                                <a href="{{ url('sales/point/indirect/retur/'.$retur->id) }}">{{ $retur->formulir->form_number}}</a>
                                            </td>
                                            <td>{{ $retur->formulir->notes }}</td>
                                            <td class="text-right">{{ number_format_quantity($amount_retur[$i]*-1) }}</td>
                                        </tr>
                                    @endforeach

                                    <tr>
                                        <td colspan="4"><h4><b>Others</b></h4></td>
                                    </tr>
                                    @for($i=0;$i < count($coa_id); $i++)
                                        <?php $total_payment += $total[$i];?>
                                        <tr>
                                            <td colspan="2">
                                                {{\Point\Framework\Models\Master\Coa::find($coa_id[$i])->account}}
                                                <input type="hidden" name="coa_id[]" value="{{$coa_id[$i]}}"/>
                                                <input type="hidden" name="allocation_id[]" value="{{$allocation_id[$i]}}"/>
                                                <input type="hidden" name="other_notes[]" value="{{$other_notes[$i]}}"/>
                                                <input type="hidden" name="coa_amount[]" value="{{$total[$i]}}"/>
                                            </td>

                                            <td>{{$other_notes[$i]}}</td>
                                            <td class="text-right">{{number_format_quantity($total[$i])}}</td>
                                        </tr>
                                    @endfor
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-right"><h4><b>TOTAL</b></h4></td>
                                        <td class="text-right">{{number_format_quantity($total_payment)}}</td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Person in charge</legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Creator</label>

                            <div class="col-md-6 content-show">
                                {{\Auth::user()->name}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Approval to</label>

                            <div class="col-md-6 content-show">
                                <input type="hidden" name="approval_to" value="{{$approval_to->id}}">
                                {{$approval_to->name}}
                            </div>
                        </div>
                    </fieldset>

                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-3">
                            <button type="submit" class="btn btn-effect-ripple btn-primary">Submit</button>
                            <a href="{{url('sales/point/indirect/payment-collection/create-step-2/'.$person->id)}}"
                               class="btn btn-danger">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
