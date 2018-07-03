@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-sales::app.sales.point.service._breadcrumb')
            <li><a href="{{ url('sales/point/service/payment-collection') }}">Payment Collection</a></li>
            <li>Show</li>
        </ul>
        <h2 class="sub-header">Payment Collection </h2>
        @include('point-sales::app.sales.point.service.payment-collection._menu')

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
                                        'approval_status' => $payment_collection->formulir->approval_status,
                                        'approval_message' => $payment_collection->formulir->approval_message,
                                        'approval_at' => $payment_collection->formulir->approval_at,
                                        'approval_to' => $payment_collection->formulir->approvalTo->name,
                                    ])
                                    @include('framework::app.include._form_status_label', ['form_status' => $payment_collection->formulir->form_status])
                                </div>
                            </div>
                        </fieldset>
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
                                {{date_Format_view($payment_collection->formulir->form_date, true)}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Number</label>
                            <div class="col-md-6 content-show">
                                {{ $payment_collection->formulir->form_number }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Customer</label>
                            <div class="col-md-6 content-show">
                                {!! get_url_person($payment_collection->person_id) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Notes</label>
                            <div class="col-md-6 content-show">
                                {!! replace_links($payment_collection->formulir->notes) !!}
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
                                        @foreach(\Point\Framework\Helpers\ReferHelper::getRefers(get_class($payment_collection), $payment_collection->id) as $refer)
                                            <?php
                                            $payment_collection_detail = \Point\PointSales\Models\Service\PaymentCollectionDetail::find($refer->to_id);
                                            $reference_type = $refer->by_type;
                                            $reference = $reference_type::find($refer->by_id);
                                            ?>
                                            <tr>
                                                <td>
                                                    {{ date_format_view($reference->formulir->form_date) }}
                                                </td>
                                                <td>
                                                    <a href="{{url('sales/point/service/invoice/'.$reference->id)}}">
                                                    {{ $reference->formulir->form_number }}
                                                    </a>
                                                </td>
                                                <td>{{ $payment_collection_detail->detail_notes }}</td>
                                                <td class="text-right">{{ number_format_quantity($payment_collection_detail->amount) }}</td>
                                            </tr>
                                        @endforeach

                                        <tr>
                                            <td colspan="4"><h4><b>Others</b></h4></td>
                                        </tr>
                                        @foreach($payment_collection->others as $payment_collection_other)

                                            <tr>
                                                <td colspan="2">
                                                    {{$payment_collection_other->coa->account}} <br/>
                                                </td>

                                                <td>{{$payment_collection_other->other_notes}}</td>
                                                <td class="text-right">{{number_format_quantity($payment_collection_other->amount)}}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                        <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-right"><h4><b>TOTAL</b></h4></td>
                                            <td class="text-right">{{number_format_quantity($payment_collection->total_payment)}}</td>
                                        </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
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
                                @if(formulir_view_edit($payment_collection->formulir, 'update.point.sales.service.payment.collection'))
                                    <a href="{{url('sales/point/service/payment-collection/'.$payment_collection->id.'/edit')}}"
                                       class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                                @endif

                                @if(formulir_view_cancel_or_request_cancel($payment_collection->formulir, 'delete.point.sales.service.payment.collection', 'approval.point.sales.service.payment.collection') == 1)
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger" onclick="secureCancelForm('{{url('formulir/cancel')}}', '{{ $payment_collection->formulir_id }}','approval.point.sales.service.payment.collection')">
                                        <i class="fa fa-times"></i> 
                                        Cancel Form
                                    </a>
                                @elseif(formulir_view_cancel_or_request_cancel($payment_collection->formulir, 'delete.point.sales.service.payment.collection', 'approval.point.sales.service.payment.collection') == 2)
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger" onclick="secureRequestCancelForm(this, '{{url('formulir/requestCancel')}}', '{{ $payment_collection->formulir_id }}', 'delete.point.sales.service.payment.collection')">
                                        <i class="fa fa-times"></i> 
                                        Request Cancel Form
                                    </a>
                                @endif

                                @if(formulir_view_close($payment_collection->formulir, 'update.point.sales.service.payment.collection'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureCloseForm({{$payment_collection->formulir_id}},'{{url('formulir/close')}}')">Close
                                        Form</a>
                                @endif
                                @if(formulir_view_reopen($payment_collection->formulir, 'update.point.sales.service.payment.collection'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureReopenForm({{$payment_collection->formulir_id}},'{{url('formulir/reopen')}}')">Reopen
                                        Form</a>
                                @endif
                                @if(formulir_view_email_vendor($payment_collection->formulir, 'create.point.sales.service.payment.collection'))
                                    <form action="{{url('sales/point/service/payment-collection/send-email-payment')}}" method="post">
                                        {!! csrf_field() !!}
                                        <input type="hidden" readonly="" name="payment_collection_id" value="{{$payment_collection->id}}">
                                        <input type="submit" class="btn btn-primary" value="Send Email To Customer">
                                    </form>
                                @endif
                            </div>
                        </div>
                    </fieldset>

                    @if(formulir_view_approval($payment_collection->formulir, 'approval.point.sales.service.payment.collection'))
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Approval Action</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-6">
                                    <form action="{{url('sales/point/service/payment-collection/'.$payment_collection->id.'/approve')}}"
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
                                    <form action="{{url('sales/point/service/payment-collection/'.$payment_collection->id.'/reject')}}"
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

                    @if($list_payment_collection_archived->count() > 0)
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
                                            @foreach($list_payment_collection_archived as $payment_collection_archived)
                                                <tr>
                                                    <td class="text-center">
                                                        <a href="{{ url('sales/point/service/payment-collection/'.$payment_collection_archived->id.'/archived') }}"
                                                           data-toggle="tooltip" title="Show"
                                                           class="btn btn-effect-ripple btn-xs btn-info"><i
                                                                    class="fa fa-file"></i> {{ 'Revision ' . $count++ }}
                                                        </a>
                                                    </td>
                                                    <td>{{ date_format_view($payment_collection->formulir->form_date) }}</td>
                                                    <td>{{ $payment_collection_archived->formulir->archived }}</td>
                                                    <td>{{ $payment_collection_archived->formulir->createdBy->name }}</td>
                                                    <td>{{ $payment_collection_archived->formulir->updatedBy->name }}</td>
                                                    <td>{{ $payment_collection_archived->formulir->edit_notes }}</td>
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
    <script>
        initDatatable('#item-datatable');
    </script>
@stop
