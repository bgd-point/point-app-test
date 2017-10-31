@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-finance::app.finance.point.cash-advance._breadcrumb')
            <li><a href="{{ url('finance/point/cash-advance/'.$cash_advance->id) }}">{{$cash_advance->formulir->form_number}}</a></li>
            <li>Archived</li>
        </ul>
        <h2 class="sub-header">Cash Advance </h2>
        @include('point-finance::app.finance.point.cash-advance._menu')

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
                    <label class="col-md-3 control-label">Cash Account</label>
                    <div class="col-md-6 content-show">
                        {{ $cash_advance_archived->coa->account}}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Employee</label>
                    <div class="col-md-6 content-show">
                        {{ $cash_advance_archived->employee->name }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Amount</label>
                    <div class="col-md-6 content-show">
                        {{ number_format_quantity($cash_advance_archived->amount) }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Notes</label>
                    <div class="col-md-6 content-show">
                        {{ $cash_advance_archived->formulir->notes }}
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
            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script>
        initDatatable('#item-datatable');
    </script>
@stop
