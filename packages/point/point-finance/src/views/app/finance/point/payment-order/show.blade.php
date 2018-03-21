@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
         @include('point-finance::app.finance.point.payment-order._breadcrumb')
         <li>Show</li>
    </ul>
    <h2 class="sub-header">Payment Order </h2>
    @include('point-finance::app.finance.point.payment-order._menu')

    @include('core::app.error._alert')

    <div class="block full">
        <div class="block-title">
            <ul class="nav nav-tabs" data-toggle="tabs">
                <li class="active"><a href="#block-tabs-home">Form</a></li>
                <li><a href="#block-tabs-settings"><i class="gi gi-settings"></i></a></li>
            </ul>
        </div>
        <div class="tab-content">
            <div class="tab-pane active" id="block-tabs-home">
                <div class="form-horizontal form-bordered">
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="pull-right">
                                    @include('framework::app.include._approval_status_label', [
                                    'approval_status' => $payment_order->formulir->approval_status,
                                    'approval_message' => $payment_order->formulir->approval_message,
                                    'approval_at' => $payment_order->formulir->approval_at,
                                    'approval_to' => $payment_order->formulir->approvalTo->name])
                                    @include('framework::app.include._form_status_label', ['form_status' => $payment_order->formulir->form_status])
                                </div>
                                <legend><i class="fa fa-angle-right"></i> Form</legend>
                            </div>
                        </div> 
                    </fieldset>                
                    <div class="form-group">
                        <label class="col-md-3 control-label">Payment date</label>
                        <div class="col-md-6 content-show">
                            {{date_format_view($payment_order->formulir->form_date, false)}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form number</label>
                        <div class="col-md-6 content-show">
                            {{ $payment_order->formulir->form_number }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Payment Type</label>
                        <div class="col-md-6 content-show">
                            {{ $payment_order->payment_type }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Payment to</label>
                        <div class="col-md-6 content-show">
                            {!! get_url_person($payment_order->person->id) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>
                        <div class="col-md-6 content-show">
                            {{$payment_order->formulir->notes}}
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
                                            @foreach($payment_order->detail as $payment_order_detail)
                                            <?php $total += $payment_order_detail->amount;?>
                                            <tr>
                                                <td>
                                                    {{$payment_order_detail->coa->account}} <br/>
                                                </td>
                                                <td>{{$payment_order_detail->notes_detail}}</td>
                                                <td class="text-right">{{number_format_quantity($payment_order_detail->amount)}}</td>
                                                <td class="text-center">{{$payment_order_detail->allocation->name}}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                        @if($cash_advance)
                                            <tr>
                                                <td colspan="2" class="text-right">Total Expense</td>
                                                <td class="text-right">{{ \NumberHelper::formatAccounting($total)}}</td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" class="text-right">Cash Advance</td>
                                                <td class="text-right">{{ \NumberHelper::formatAccounting($cash_advance->remaining_amount)}}</td>
                                                <td></td>
                                            </tr>

                                            <tr>
                                                <td colspan="2" class="text-right"><h4><b>Total payment</b></h4></td>
                                                <td class="text-right">
                                                    <h4><strong>{{ \NumberHelper::formatAccounting($total - $cash_advance->remaining_amount)}}</strong></h4>
                                                    <input type="hidden" name="total" value="{{$total - $cash_advance->remaining_amount}}" />
                                                </td>
                                                <td></td>
                                            </tr>
                                        @else
                                            <tr>
                                                <td colspan="2" class="text-right"><h4><b>Total Payment</b></h4></td>
                                                <td class="text-right"><h4><strong>{{ number_format_quantity($total)}}</strong></h4></td>
                                                <td></td>
                                            </tr>
                                            @endif
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
                                {{ $payment_order->formulir->createdBy->name }} ({{ date_format_view($payment_order->formulir->created_at) }})
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Ask Approval To</label>
                            <div class="col-md-6 content-show">
                                {{ $payment_order->formulir->approvalTo->name }}
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
            
            <div class="tab-pane" id="block-tabs-settings"> 
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Action</legend>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-12">
                            @if(formulir_view_edit($payment_order->formulir, 'update.point.finance.payment.order'))
                            <a href="{{url('finance/point/payment-order/'.$payment_order->id.'/edit')}}" class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i>Edit </a>
                            @endif
                            @if(formulir_view_cancel($payment_order->formulir, 'delete.point.finance.payment.order'))
                            <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger" onclick="secureCancelForm(
                                '{{url('finance/point/payment-order/cancel')}}',
                                {{$payment_order->formulir_id}}, 
                                'delete.point.finance.payment.order')">
                                <i class="fa fa-times"></i> 
                                cancel</a>
                            @endif
                        </div>
                    </div>
                </fieldset>

                @if(formulir_view_approval($payment_order->formulir, 'approval.point.finance.payment.order'))
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Approval</legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-6">
                            <form action="{{url('finance/point/payment-order/'.$payment_order->id.'/approve')}}" method="post">
                                {!! csrf_field() !!}
                                <div class="input-group">
                                <input type="text" name="approval_message" class="form-control" placeholder="message...">
                                <span class="input-group-btn">
                                <input type="submit" class="btn btn-primary" value="Approve"></span>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form action="{{url('finance/point/payment-order/'.$payment_order->id.'/reject')}}" method="post">
                                {!! csrf_field() !!}
                                <div class="input-group">
                                <input type="text" name="approval_message" class="form-control" placeholder="message...">
                                <span class="input-group-btn">
                                <input type="submit" class="btn btn-danger" value="Reject"></span>
                                </div>
                            </form>
                        </div>
                    </div>
                </fieldset>                
                @endif

                @if($list_payment_order_archived->count() > 0)
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i>Archived</legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12 content-show">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                        <tr>
                                            <th></th>
                                            <th>Date</th>
                                            <th>Form number</th>
                                            <th>Created by</th>
                                            <th>Updated by</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php $count=0;?>

                                        @foreach($list_payment_order_archived as $payment_order_archived)
                                            <tr>
                                                <td class="text-center">
                                                    <a href="{{ url('finance/point/payment-order/'.$payment_order_archived->id.'/archived') }}" data-toggle="tooltip" title="Show" class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-file"></i> {{ 'Revision ' . $count++ }}</a>
                                                </td>
                                                <td>{{ date_format_view($payment_order_archived->formulir->form_date) }}</td>
                                                <td>{{ $payment_order_archived->formulir->archived }}</td>
                                                <td>{{ $payment_order_archived->formulir->createdBy->name }}</td>
                                                <td>{{ $payment_order_archived->formulir->updatedBy->name }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                @endif
            </div>
        </div>
    </div>    
</div>
@stop 

@section('scripts')
<script>
    initDatatable('#item-datatable');
</script>
@stop
