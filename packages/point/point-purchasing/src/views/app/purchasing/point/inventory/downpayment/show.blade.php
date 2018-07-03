@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li><a href="{{ url('purchasing/point/downpayment') }}">Downpayment</a></li>
            <li>Show</li>
        </ul>
        <h2 class="sub-header">Downpayment </h2>
        @include('point-purchasing::app.purchasing.point.inventory.downpayment._menu')

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
                                        'approval_status' => $downpayment->formulir->approval_status,
                                        'approval_message' => $downpayment->formulir->approval_message,
                                        'approval_at' => $downpayment->formulir->approval_at,
                                        'approval_to' => $downpayment->formulir->approvalTo->name,
                                    ])
                                    @include('framework::app.include._form_status_label', ['form_status' => $downpayment->formulir->form_status])
                                </div>
                            </div>

                            @if($downpayment->purchasing_order_id !== null && $downpayment->purchasing_order_id > 0)
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <legend><i class="fa fa-angle-right"></i> REF#
                                            @if($downpayment->purchasing_order_id !== null && $downpayment->purchasing_order_id > 0)
                                                <a target="_blank" href="{{ url('purchasing/point/purchase-order/'.$downpayment->purchasing_order_id) }}">{{$downpayment->purchaseOrder->formulir->form_number}}</a>@endif
                                        </legend>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">Form Date</label>
                                    <div class="col-md-6 content-show">
                                        {{ date_format_view($downpayment->purchaseOrder->formulir->form_date) }}
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Total amount</label>
                                    <div class="col-md-6 content-show">
                                        {{ number_format_price($downpayment->purchaseOrder->total) }}
                                    </div>
                                </div>
                            @endif

                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Downpayment Form</legend>
                                </div>
                            </div>
                        </fieldset>

                        @if($revision)
                            <div class="form-group">
                                <label class="col-md-3 control-label">Revision</label>
                                <div class="col-md-6 content-show">
                                    {{ $revision }}
                                </div>
                            </div>
                        @endif
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Number</label>
                            <div class="col-md-6 content-show">
                                {{ $downpayment->formulir->form_number }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Date</label>
                            <div class="col-md-6 content-show">
                                {{ date_format_view($downpayment->formulir->form_date, true) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Supplier</label>
                            <div class="col-md-6 content-show">
                                {!! get_url_person($downpayment->supplier->id) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Amount</label>
                            <div class="col-md-6 content-show">
                                {{ number_format_quantity($downpayment->amount) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Notes</label>
                            <div class="col-md-6 content-show">
                                {{ $downpayment->formulir->notes }}
                            </div>
                        </div>

                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Authorized User</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Form Creator</label>

                                <div class="col-md-6 content-show">
                                    {{ $downpayment->formulir->createdBy->name }} ({{ date_format_view($downpayment->formulir->created_at, true) }})
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Request Approval To</label>

                                <div class="col-md-6 content-show">
                                    {{ $downpayment->formulir->approvalTo->name }}
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
                                @if(formulir_view_edit($downpayment->formulir, 'update.point.purchasing.downpayment'))
                                    <a href="{{url('purchasing/point/downpayment/'.$downpayment->id.'/edit')}}"
                                       class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                                @endif

                                @if(formulir_view_cancel_or_request_cancel($downpayment->formulir, 'delete.point.purchasing.downpayment', 'approval.point.purchasing.downpayment') == 1)
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger" onclick="secureCancelForm('{{url('formulir/cancel')}}', '{{ $downpayment->formulir_id }}','approval.point.purchasing.downpayment')">
                                        <i class="fa fa-times"></i> 
                                        Cancel Form
                                    </a>
                                @elseif(formulir_view_cancel_or_request_cancel($downpayment->formulir, 'delete.point.purchasing.downpayment', 'approval.point.purchasing.downpayment') == 2)
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger" onclick="secureRequestCancelForm('{{url('formulir/requestCancel')}}', '{{ $downpayment->formulir_id }}', 'delete.point.purchasing.downpayment')">
                                        <i class="fa fa-times"></i> 
                                        Request Cancel Form
                                    </a>
                                @endif

                                @if(formulir_view_close($downpayment->formulir, 'update.point.purchasing.downpayment'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureCloseForm({{$downpayment->formulir_id}},'{{url('formulir/close')}}')">Close
                                        Form</a>
                                @endif
                                @if(formulir_view_reopen($downpayment->formulir, 'update.point.purchasing.downpayment'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger" onclick="secureReopenForm({{$downpayment->formulir_id}},'{{url('formulir/reopen')}}')">Reopen Form</a>
                                @endif
                            </div>
                        </div>
                    </fieldset>


                    @if(formulir_view_approval($downpayment->formulir, 'approval.point.purchasing.downpayment'))
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Approval Action</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-6">
                                    <form action="{{url('purchasing/point/downpayment/'.$downpayment->id.'/approve')}}"
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
                                    <form action="{{url('purchasing/point/downpayment/'.$downpayment->id.'/reject')}}"
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

                    @if($list_downpayment_archived->count() > 0)
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
                                            @foreach($list_downpayment_archived as $downpayment_archived)
                                                <tr>
                                                    <td class="text-center">
                                                        <a href="{{ url('purchasing/point/downpayment/'.$downpayment_archived->formulirable_id.'/archived') }}"
                                                           data-toggle="tooltip"
                                                           title="Show"
                                                           class="btn btn-effect-ripple btn-xs btn-info">
                                                            <i class="fa fa-file"></i> {{ 'Revision ' . $count++ }}
                                                        </a>
                                                    </td>
                                                    <td>{{ date_format_view($downpayment->formulir->form_date) }}</td>
                                                    <td>{{ $downpayment_archived->formulir->archived }}</td>
                                                    <td>{{ $downpayment_archived->formulir->createdBy->name }}</td>
                                                    <td>{{ $downpayment_archived->formulir->updatedBy->name }}</td>
                                                    <td>{{ $downpayment_archived->formulir->edit_notes }}</td>
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
