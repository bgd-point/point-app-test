@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-finance::app.finance.point.cash-advance._breadcrumb')
            <li><a href="{{ url('finance/point/cash-advance') }}">Cash Advance</a></li>
            <li>Show</li>
        </ul>
        <h2 class="sub-header">Cash Advance </h2>
        @include('point-finance::app.finance.point.cash-advance._menu')

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
                                        'approval_status' => $cash_advance->formulir->approval_status,
                                        'approval_message' => $cash_advance->formulir->approval_message,
                                        'approval_at' => $cash_advance->formulir->approval_at,
                                        'approval_to' => $cash_advance->formulir->approvalTo->name,
                                    ])
                                    @include('framework::app.include._form_status_label', ['form_status' => $cash_advance->formulir->form_status])
                                </div>
                            </div>
                        </fieldset>
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Cash Advance Form</legend>
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
                                {{ $cash_advance->formulir->form_number }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Date</label>
                            <div class="col-md-6 content-show">
                                {{ date_format_view($cash_advance->formulir->form_date, true) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Cash Account</label>
                            <div class="col-md-6 content-show">
                                {{ $cash_advance->coa->account}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Employee</label>
                            <div class="col-md-6 content-show">
                                {{ $cash_advance->employee->name }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Cash Advance Amount</label>
                            <div class="col-md-6 content-show">
                                {{ number_format_quantity($cash_advance->amount) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Remaining Amount</label>
                            <div class="col-md-6 content-show">
                                {{ number_format_quantity($cash_advance->remaining_amount) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Referenced By</label>
                            <div class="col-md-6 content-show">
                                @foreach($list_referenced as $referenced)
                                    <?php
                                        $model = $referenced->used->formulirable_type;
                                        $url = $model::showUrl($referenced->used->formulirable_id);
                                    ?>
                                    <a href="{{ url($url) }}">{{ $referenced->used->form_number }}</a> <br/>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Notes</label>
                            <div class="col-md-6 content-show">
                                {{ $cash_advance->formulir->notes }}
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
                                    {{ $cash_advance->formulir->createdBy->name }} ({{ date_format_view($cash_advance->formulir->created_at) }})
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Request Approval To</label>

                                <div class="col-md-6 content-show">
                                    {{ $cash_advance->formulir->approvalTo->name }}
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
                                @if(formulir_view_edit($cash_advance->formulir, 'update.point.finance.cash.advance'))
                                    <a href="{{url('finance/point/cash-advance/'.$cash_advance->id.'/edit')}}"
                                            class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                                @endif
                                @if(formulir_view_cancel($cash_advance->formulir, 'delete.point.finance.cash.advance'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                            onclick="secureCancelForm('{{url('formulir/cancel')}}',
                                                    '{{ $cash_advance->formulir_id }}',
                                                    'delete.point.finance.cash.advance')"><i class="fa fa-times"></i> Cancel
                                        Form</a>
                                @endif
                                @if(formulir_view_close($cash_advance->formulir, 'update.point.finance.cash.advance'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                            onclick="secureCloseForm({{$cash_advance->formulir_id}},'{{url('formulir/close')}}')">Close
                                        Form</a>
                                @endif
                                @if(formulir_view_reopen($cash_advance->formulir, 'update.point.finance.cash.advance'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger" onclick="secureReopenForm({{$cash_advance->formulir_id}},'{{url('formulir/reopen')}}')">Reopen Form</a>
                                @endif
                            </div>
                        </div>
                    </fieldset>

                    @if(formulir_view_approval($cash_advance->formulir, 'approval.point.finance.cash.advance'))
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Approval Action</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-6">
                                    <form action="{{url('finance/point/cash-advance/'.$cash_advance->id.'/approve')}}"
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
                                    <form action="{{url('finance/point/cash-advance/'.$cash_advance->id.'/reject')}}"
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

                    @if($list_cash_advance_archived->count() > 0)
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Archived Form</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-12 content-show">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered table-vcenter">
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
                                            @foreach($list_cash_advance_archived as $cash_advance_archived)
                                                <tr>
                                                    <td class="text-center">
                                                        <a href="{{ url('finance/point/cash-advance/'.$cash_advance_archived->formulirable_id.'/archived') }}"
                                                                data-toggle="tooltip"
                                                                title="Show"
                                                                class="btn btn-effect-ripple btn-xs btn-info">
                                                            <i class="fa fa-file"></i> {{ 'Revision ' . $count++ }}
                                                        </a>
                                                    </td>
                                                    <td>{{ date_format_view($cash_advance->formulir->form_date) }}</td>
                                                    <td>{{ $cash_advance_archived->formulir->archived }}</td>
                                                    <td>{{ $cash_advance_archived->formulir->createdBy->name }}</td>
                                                    <td>{{ $cash_advance_archived->formulir->updatedBy->name }}</td>
                                                    <td>{{ $cash_advance_archived->formulir->edit_notes }}</td>
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
