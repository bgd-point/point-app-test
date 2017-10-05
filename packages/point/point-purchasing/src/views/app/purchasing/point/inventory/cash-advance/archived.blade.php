@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li><a href="{{ url('purchasing/point/cash-advance') }}">Cash Advance</a></li>
            <li><a href="{{ url('purchasing/point/cash-advance/'.$cash_advance_archived->id) }}">{{$cash_advance_archived->formulir->form_number}}</a></li>
            <li>Archived</li>
        </ul>
        <h2 class="sub-header">Cash Advance </h2>
        @include('point-purchasing::app.purchasing.point.inventory.cash-advance._menu')

        @include('core::app.error._alert')

        <div class="block full">
            <div class="form-horizontal form-bordered">
                <div class="form-group">
                    <div class="col-md-12">
                        <div class="alert alert-danger alert-dismissable">
                            <h1 class="text-center"><strong>Archived</strong></h1>
                        </div>
                    </div>
                </div>
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> REF
                                <a target="_blank" href="{{ url('purchasing/point/purchase-requisition/'.$cash_advance_archived->purchaseRequisition->id) }}"># {{$cash_advance_archived->purchaseRequisition->formulir->form_number}}</a>
                            </legend>
                        </div>
                    </div>
                </fieldset>
                <div class="form-group">
                    <label class="col-md-3 control-label">Form Date</label>
                    <div class="col-md-6 content-show">
                        {{ date_format_view($cash_advance_archived->purchaseRequisition->formulir->form_date) }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Employee</label>
                    <div class="col-md-6 content-show">
                        {!! get_url_person($cash_advance_archived->purchaseRequisition->employee_id) !!}
                    </div>
                </div>
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Formulir Cash Advance</legend>
                        </div>
                    </div>
                </fieldset>
                <div class="form-group">
                    <label class="col-md-3 control-label">Form Number</label>
                    <div class="col-md-6 content-show">
                        {{ $cash_advance_archived->formulir->archived }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Form Date</label>
                    <div class="col-md-6 content-show">
                        {{ date_format_view($cash_advance_archived->formulir->form_date, true) }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Employee</label>
                    <div class="col-md-6 content-show">
                        {!! get_url_person($cash_advance_archived->employee->id) !!}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Notes</label>
                    <div class="col-md-6 content-show">
                        {{ $cash_advance_archived->formulir->notes }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Amount</label>
                    <div class="col-md-6 content-show">
                        {{ number_format_quantity($cash_advance_archived->amount) }}
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
                            {{ $cash_advance_archived->formulir->createdBy->name }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Request Approval To</label>

                        <div class="col-md-6 content-show">
                            {{ $cash_advance_archived->formulir->approvalTo->name }}
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Status</legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Approval Status</label>

                        <div class="col-md-6 content-show">
                            @include('framework::app.include._approval_status_label_detailed', [
                                'approval_status' => $cash_advance_archived->formulir->approval_status,
                                'approval_message' => $cash_advance_archived->formulir->approval_message,
                                'approval_at' => $cash_advance_archived->formulir->approval_at,
                                'approval_to' => $cash_advance_archived->formulir->approvalTo->name,
                            ])
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Status</label>

                        <div class="col-md-6 content-show">
                            @include('framework::app.include._form_status_label', ['form_status' => $cash_advance_archived->formulir->form_status])
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script>
        initDatatable('#item-datatable');
    </script>
@stop
