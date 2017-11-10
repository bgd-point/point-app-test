@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-finance::app.finance.point.payment-order._breadcrumb')
        <li>{{ $payment_order->formulir->form_number }}</li>
        <li>Edit review</li>
    </ul>
    <h2 class="sub-header">Payment order</h2>
    @include('point-finance::app.finance.point.payment-order._menu')

    @include('core::app.error._alert')

    <div class="panel panel-default"> 
        <div class="panel-body">
            <form action="{{url('finance/point/payment-order/update/'.$payment_order->id)}}" method="POST" class="form-horizontal form-bordered">
                {!! csrf_field() !!}
                
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Payment Order</legend>
                        </div>
                    </div> 
                </fieldset>                
                <div class="form-group">
                    <label class="col-md-3 control-label">Form Number</label>
                    <div class="col-md-6 content-show">
                        {{ $payment_order->formulir->form_number }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Payment Date</label>
                    <div class="col-md-6 content-show">
                        <input type="hidden" name="payment_date" class="form-control" value="{{ $payment_date }}">
                        {{date_format_view($payment_date, true)}}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Payment To</label>
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
                        {{$notes}}
                    </div>
                </div>
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="table-responsive"> 
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th style="min-width: 115px">Account</th>
                                            <th style="min-width: 115px">Notes</th>
                                            <th style="min-width: 115px" class="text-right">Amount</th>
                                            <th style="min-width: 150px" class="text-center">Allocation</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $total=0;?>
                                        @for($i=0;$i < count($coa_id); $i++)
                                        @if($coa_id[$i] && $allocation_id[$i] 
                                        && $detail_notes[$i] && $amount[$i])
                                        <?php $total += $amount[$i];?>
                                        <tr>
                                            <td>
                                                {{Point\Framework\Models\Master\Coa::find($coa_id[$i])->account}} <br/>
                                                <input type="hidden" name="coa_id[]" value="{{$coa_id[$i]}}" />
                                                <input type="hidden" name="coa_allocation_id[]" value="{{$allocation_id[$i]}}" />
                                                <input type="hidden" name="other_notes[]" value="{{$detail_notes[$i]}}" />
                                                <input  type="hidden" name="coa_value[]" value="{{$amount[$i]}}" />
                                            </td>
                                            <td>{{$detail_notes[$i]}}</td>
                                            <td class="text-right">{{ number_format_quantity($amount[$i])}}</td>
                                            <td>{{Point\Framework\Models\Master\Allocation::find($allocation_id[$i])->name}}</td>
                                        </tr>
                                        @endif
                                        @endfor
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="2" class="text-right"><h4><b>Total payment</b></h4></td>
                                            <td class="text-right">
                                                <h4><strong>{{ number_format_quantity($total)}}</strong></h4>
                                                <input type="hidden" name="total" value="{{$total}}" />
                                            </td>
                                            <td></td>
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
                            <legend><i class="fa fa-angle-right"></i> Authorized User</legend>
                        </div>
                    </div>  
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Creator</label>
                        <div class="col-md-6 content-show">
                            {{auth()->user()->name}}
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
                        <a href="{{url('finance/point/payment-order/'.$payment_order->formulir_id.'/edit')}}" class="btn btn-danger">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>  
</div>
@stop
