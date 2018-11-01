@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li><a href="{{ url('purchasing/point/payment-order') }}">Payment Order</a></li>
            <li>Show</li>
        </ul>
        <h2 class="sub-header">PAYMENT ORDER </h2>
        @include('point-purchasing::app.purchasing.point.inventory.payment-order._menu')

        @include('core::app.error._alert')

        <div class="block full">
            <!-- Block Tabs Title -->
            <div class="block-title">
                <ul class="nav nav-tabs" data-toggle="tabs">
                    <li class="active"><a href="#block-tabs-home">Form</a></li>
                    <li><a href="#block-tabs-settings"><i class="gi gi-settings"></i></a></li>
                </ul>
            </div>
            <!-- END Block Tabs Title -->

            <!-- Tabs Content -->
            <div class="tab-content">
                <div class="tab-pane active" id="block-tabs-home">
                    <div class="form-horizontal form-bordered">
                        <fieldset>
                            <div class="form-group pull-right">
                                <div class="col-md-12">
                                    @include('framework::app.include._approval_status_label', [
                                        'approval_status' => $payment_order->formulir->approval_status,
                                        'approval_message' => $payment_order->formulir->approval_message,
                                        'approval_at' => $payment_order->formulir->approval_at,
                                        'approval_to' => $payment_order->formulir->approvalTo->name,
                                    ])
                                    @include('framework::app.include._form_status_label', ['form_status' => $payment_order->formulir->form_status])
                                </div>
                            </div>
                        </fieldset>
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
                                {{date_Format_view($payment_order->formulir->form_date, true)}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Number</label>
                            <div class="col-md-6 content-show">
                                {{ formulir_url($payment_order->formulir) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Supplier</label>
                            <div class="col-md-6 content-show">
                                {!! get_url_person($payment_order->supplier_id) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Notes</label>
                            <div class="col-md-6 content-show">
                                {{ $payment_order->formulir->notes }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Referenced By</label>
                            <div class="col-md-6 content-show">
                                @foreach($list_referenced as $referenced)
                                    <?php
                                    $model = $referenced->locking->formulirable_type;
                                    $url = $model::showUrl($referenced->locking->formulirable_id);
                                    ?>
                                    <a href="{{ url($url) }}">{{ $referenced->locking->form_number }}</a> <br/>
                                @endforeach
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
                                            @foreach(\Point\Framework\Helpers\ReferHelper::getRefers(get_class($payment_order), $payment_order->id) as $refer)
                                                <?php
                                                $payment_order_detail = \Point\PointPurchasing\Models\Inventory\PaymentOrderDetail::find($refer->to_id);
                                                $reference_type = $refer->by_type;
                                                $reference = $reference_type::find($refer->by_id);
                                                if (get_class($reference) == get_class(new Point\PointAccounting\Models\CutOffPayableDetail())) {
                                                    $reference->formulir = $reference->cutoffPayable->formulir;
                                                }
                                                ?>
                                                <tr>
                                                    <td>
                                                        {{ date_format_view($reference->formulir->form_date) }}
                                                    </td>
                                                    <td>{{ $reference->formulir->form_number }}</td>
                                                    <td>{{ $payment_order_detail->detail_notes }}</td>
                                                    <td class="text-right">{{ number_format_quantity($payment_order_detail->amount) }}</td>
                                                </tr>
                                            @endforeach

                                            <tr>
                                                <td colspan="4"><h4><b>Others</b></h4></td>
                                            </tr>
                                            @foreach($payment_order->others as $payment_order_other)

                                                <tr>
                                                    <td colspan="2">
                                                        {{$payment_order_other->coa->account}} <br/>
                                                        <b>ALLOCATION :</b> {{$payment_order_other->allocation->name}}
                                                    </td>

                                                    <td>{{$payment_order_other->other_notes}}</td>
                                                    <td class="text-right">{{number_format_quantity($payment_order_other->amount)}}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                            <tfoot>
                                            <tr>
                                                <td colspan="3" class="text-right"><h4><b>TOTAL</b></h4></td>
                                                <td class="text-right">{{number_format_quantity($payment_order->total_payment)}}</td>
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
                                    <legend><i class="fa fa-angle-right"></i> Person In Charge</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Form Creator</label>

                                <div class="col-md-6 content-show">
                                    {{ $payment_order->formulir->createdBy->name }} ({{ date_format_view($payment_order->formulir->created_at, true) }})
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
                                @if(formulir_view_edit($payment_order->formulir, 'update.point.purchasing.payment.order'))
                                    <a href="{{url('purchasing/point/payment-order/'.$payment_order->id.'/edit')}}"
                                       class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                                @endif

                                @if(formulir_view_cancel_or_request_cancel($payment_order->formulir, 'delete.point.purchasing.payment.order', 'approval.point.purchasing.payment.order') == 1)
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger" onclick="secureCancelForm('{{url('formulir/cancel')}}', '{{ $payment_order->formulir_id }}','approval.point.purchasing.payment.order')">
                                        <i class="fa fa-times"></i> 
                                        Cancel Form
                                    </a>
                                @elseif(formulir_view_cancel_or_request_cancel($payment_order->formulir, 'delete.point.purchasing.payment.order', 'approval.point.purchasing.payment.order') == 2)
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger" onclick="secureRequestCancelForm(this, '{{url('formulir/requestCancel')}}', '{{ $payment_order->formulir_id }}', 'delete.point.purchasing.payment.order')">
                                        <i class="fa fa-times"></i> 
                                        Request Cancel Form
                                    </a>
                                @endif

                                @if(formulir_view_close($payment_order->formulir, 'update.point.purchasing.payment.order'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureCloseForm({{$payment_order->formulir_id}},'{{url('formulir/close')}}')">Close
                                        Form</a>
                                @endif
                                @if(formulir_view_reopen($payment_order->formulir, 'update.point.purchasing.payment.order'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureReopenForm({{$payment_order->formulir_id}},'{{url('formulir/reopen')}}')">Reopen
                                        Form</a>
                                @endif
                                @if(formulir_view_email_vendor($payment_order->formulir, 'create.point.purchasing.payment.order'))
                                    <form action="{{url('purchasing/point/payment-order/send-email-payment')}}" method="post">
                                        {!! csrf_field() !!}
                                        <input type="hidden" readonly="" name="payment_order_id" value="{{$payment_order->id}}">
                                        <input type="submit" class="btn btn-primary" value="Send Email Payment Order">
                                    </form>
                                @endif
                            </div>
                        </div>
                    </fieldset>

                    @if(formulir_view_approval($payment_order->formulir, 'approval.point.purchasing.payment.order'))
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Approval Action</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-6">
                                    <form action="{{url('purchasing/point/payment-order/'.$payment_order->id.'/approve')}}"
                                          method="post">
                                        {!! csrf_field() !!}

                                        <div class="input-group">
                                            <input type="text" name="approval_message" class="form-control"
                                                   placeholder="Message">
                                            <span class="input-group-btn">
                                                <input type="submit" class="btn btn-primary" value="Approve">
                                            </span>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-md-6">
                                    <form action="{{url('purchasing/point/payment-order/'.$payment_order->id.'/reject')}}"
                                          method="post">
                                        {!! csrf_field() !!}

                                        <div class="input-group">
                                            <input type="text" name="approval_message" class="form-control"
                                                   placeholder="Message">
                                            <span class="input-group-btn">
                                                <input type="submit" class="btn btn-danger" value="Reject">
                                            </span>
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
                                    <legend><i class="fa fa-angle-right"></i> Archived Form</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-12 content-show">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                            <tr>
                                                <th></th>
                                                <th>Form Date</th>
                                                <th>Form Number</th>
                                                <th>Created By</th>
                                                <th>Updated By</th>
                                                <th>Reason</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php $count = 0;?>
                                            @foreach($list_payment_order_archived as $payment_order_archived)
                                                <tr>
                                                    <td class="text-center">
                                                        <a href="{{ url('purchasing/point/payment-order/'.$payment_order_archived->id.'/archived') }}"
                                                           data-toggle="tooltip" title="Show"
                                                           class="btn btn-effect-ripple btn-xs btn-info"><i
                                                                    class="fa fa-file"></i> {{ 'Revision ' . $count++ }}
                                                        </a>
                                                    </td>
                                                    <td>{{ date_format_view($payment_order->formulir->form_date) }}</td>
                                                    <td>{{ $payment_order_archived->formulir->archived }}</td>
                                                    <td>{{ $payment_order_archived->formulir->createdBy->name }}</td>
                                                    <td>{{ $payment_order_archived->formulir->updatedBy->name }}</td>
                                                    <td>{{ $payment_order_archived->formulir->edit_notes }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    @endif

                    @if($email_history->count() > 0)
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> EMAIL HISTORY</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-12 content-show">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th style="text-align: right;">NO</th>
                                                    <th style="text-align: center;">SENT AT</th>
                                                    <th>SENDER</th>
                                                    <th>RECIPIENT</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($email_history as $index=>$item)
                                                <tr>
                                                    <td style="text-align: right;">{{ $index+1 }}</td>
                                                    <td style="text-align: center;">{{ date_format_view($item->sent_at, true) }}</td>
                                                    <td><a href="/master/user/{{ $item->sender }}">{{ $item->user->name }}</a></td>
                                                    <td>{!! get_url_person($item->recipient) !!}
                                                        <span style="text-transform: none !important;"> ({{ $item->recipient_email }})</span>
                                                    </td>
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
            <!-- END Tabs Content -->
        </div>
    </div>
@stop

@section('scripts')
    <style>
        tbody.manipulate-row:after {
            content: '';
            display: block;
            height: 100px;
        }
    </style>
    <script>
        var item_table = $('#item-datatable').DataTable({
            bSort: false,
            bPaginate: false,
            bInfo: false,
            bFilter: false,
            bScrollCollapse: false,
            scrollX: true
        });
    </script>
@stop
