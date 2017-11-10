@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.fixed-assets._breadcrumb')
            <li><a href="{{ url('purchasing/point/fixed-assets/payment-order') }}">Payment Order</a></li>
            <li>Create step 3</li>
        </ul>
        <h2 class="sub-header">PAYMENT ORDER</h2>
        @include('point-purchasing::app.purchasing.point.fixed-assets.payment-order._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('purchasing/point/fixed-assets/payment-order')}}" method="post"
                      class="form-horizontal form-bordered">
                    {!! csrf_field() !!}

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> PAYMENT ORDER</legend>
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
                        <label class="col-md-3 control-label">Supplier</label>

                        <div class="col-md-6 content-show">
                            <input type="hidden" name="supplier_id" value="{{$supplier->id}}">
                            {{$supplier->codeName}}
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
                    <fieldset>
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
                                                </td>
                                                <td>
                                                    <a href="{{ url('purchasing/point/invoice/'.$invoice->id) }}">{{ $invoice->formulir->form_number}}</a>
                                                </td>
                                                <td>{{ $invoice->formulir->notes }}</td>
                                                <td class="text-right">{{ number_format_quantity($amount_invoice[$i]) }}</td>
                                            </tr>
                                        @endforeach
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
                                                </td>
                                                <td>
                                                    <a href="{{ url('purchasing/point/retur/'.$retur->id) }}">{{ $retur->formulir->form_number}}</a>
                                                </td>
                                                <td>{{ $retur->formulir->notes }}</td>
                                                <td class="text-right">{{ number_format_quantity($amount_retur[$i]*-1) }}</td>
                                            </tr>
                                        @endforeach

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
                                                </td>
                                                <td>
                                                    <a href="{{ url('purchasing/point/downpayment/'.$downpayment->id) }}">{{ $downpayment->formulir->form_number}}</a>
                                                </td>
                                                <td>{{ $downpayment->formulir->notes }}</td>
                                                <td class="text-right">{{ number_format_quantity($amount_downpayment[$i]*-1) }}</td>
                                            </tr>
                                            <?php $i++;?>
                                        @endforeach
                                        <tr>
                                            <td colspan="4"><h4><b>Others</b></h4></td>
                                        </tr>
                                        @for($i=0;$i < count($coa_id); $i++)
                                            <?php $total_payment += $total[$i];?>
                                            <tr>
                                                <td colspan="2">
                                                    {{\Point\Framework\Models\Master\Coa::find($coa_id[$i])->account}}
                                                    <br/>
                                                    <b>ALLOCATION
                                                        :</b> {{\Point\Framework\Models\Master\Allocation::find($allocation_id[$i])->name}}
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
                    </fieldset>

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Person in Charge</legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Creator</label>

                            <div class="col-md-6 content-show">
                                {{\Auth::user()->name}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Ask approval to</label>

                            <div class="col-md-6 content-show">
                                <input type="hidden" name="approval_to" value="{{$approval_to->id}}">
                                {{$approval_to->name}}
                            </div>
                        </div>
                    </fieldset>

                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-3">
                            <button type="submit" class="btn btn-effect-ripple btn-primary">Submit</button>
                            <a href="{{url('purchasing/point/fixed-assets/payment-order/create-step-2/'.$supplier->id)}}"
                               class="btn btn-danger">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
