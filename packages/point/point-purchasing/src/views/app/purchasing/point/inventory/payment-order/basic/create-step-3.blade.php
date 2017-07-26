@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li><a href="{{ url('purchasing/point/payment-order/basic') }}">Payment Order</a></li>
            <li>Create step 3</li>
        </ul>
        <h2 class="sub-header">PAYMENT ORDER</h2>
        @include('point-purchasing::app.purchasing.point.inventory.payment-order.basic._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('purchasing/point/payment-order/basic')}}" method="post"
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
                            {!! get_url_person($supplier->id) !!}
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
                                    <table class="table table-striped table-vcenter">
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
                                                </td>
                                                <td>
                                                    <a href="{{ url('purchasing/point/invoice/'.$invoice->id) }}">{{ $invoice->formulir->form_number}}</a>
                                                </td>
                                                <td>{{ $invoice->formulir->notes }}</td>
                                                <td class="text-right">{{ number_format_quantity($amount_invoice[$i]) }}</td>
                                            </tr>
                                        @endforeach
                                        @if($coa_id)
                                        <tr>
                                            <td colspan="4"><h4><b>Others</b></h4></td>
                                        </tr>
                                        @endif
                                        @for($i=0;$i < count($coa_id); $i++)
                                            <?php $total_payment += $total[$i];?>
                                            <tr>
                                                <td colspan="2">
                                                    {{\Point\Framework\Models\Master\Coa::find($coa_id[$i])->account}}
                                                    <br/>
                                                    <b>ALLOCATION
                                                        :</b> {{\Point\Framework\Models\Master\Allocation::find($allocation_id[$i])->name}}
                                                    <input type="hidden" name="coa_id[]" value="{{$coa_id[$i]}}"/>
                                                    <input type="hidden" name="allocation_id[]"
                                                           value="{{$allocation_id[$i]}}"/>
                                                    <input type="hidden" name="coa_notes[]" value="{{$coa_notes[$i]}}"/>
                                                    <input type="hidden" name="coa_amount[]" value="{{$total[$i]}}"/>
                                                </td>

                                                <td>{{$coa_notes[$i]}}</td>
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
                            <a href="{{url('purchasing/point/payment-order/basic/create-step-2/'.$supplier->id)}}"
                               class="btn btn-danger">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
