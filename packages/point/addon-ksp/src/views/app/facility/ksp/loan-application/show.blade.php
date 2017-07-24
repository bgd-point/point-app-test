@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('ksp::app/facility/ksp/_breadcrumb')
        <li>Loan Application</li>
    </ul>

    <h2 class="sub-header">Loan Application</h2>
    @include('ksp::app.facility.ksp.loan-application._menu')

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
                                @include('framework::app.include._form_status_label', ['form_status' => $loan_application->formulir->form_status])
                                @include('framework::app.include._approval_status_label', [
                                    'approval_status' => $loan_application->formulir->approval_status,
                                    'approval_message' => $loan_application->formulir->approval_message,
                                    'approval_at' => $loan_application->formulir->approval_at,
                                    'approval_to' => $loan_application->formulir->approvalTo->name,
                                ])
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Form</legend>
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
                            {{ $loan_application->formulir->form_number }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Date</label>
                        <div class="col-md-6 content-show">
                            {{ date_format_view($loan_application->formulir->form_date, true) }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Customer</label>
                        <div class="col-md-6 content-show">{{ $loan_application->customer->codeName }}</div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Loan Amount</label>
                        <div class="col-md-6 content-show">{{ number_format_price($loan_application->loan_amount) }}</div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Periods</label>
                        <div class="col-md-6 content-show">{{ number_format_price($loan_application->periods, 0) }} Month</div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Interest Rate</label>
                        <div class="col-md-6 content-show">{{ number_format_price($loan_application->interest_rate) }} %</div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Interest Rate Type</label>
                        <div class="col-md-6 content-show">{{ $loan_application->interest_rate_type }}</div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Payment Type</label>
                        <div class="col-md-6 content-show">{{ $loan_application->payment_type }}</div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Payment Account</label>
                        <div class="col-md-6 content-show">{{ $loan_application->paymentAccount->account }}</div>
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
                                {{ $loan_application->formulir->createdBy->name }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Approval To</label>
                            <div class="col-md-6 content-show">
                                {{ $loan_application->formulir->approvalTo->name }}
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

                        </div>
                    </div>
                </fieldset>


                @if(formulir_view_approval($loan_application->formulir,'approval.ksp.loan.application'))
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Approval Action</legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-6">
                            <form action="{{url('facility/ksp/loan-application/'.$loan_application->id.'/approve')}}" method="post">
                                {!! csrf_field() !!}
                                <div class="input-group">
                                    <input type="text" name="approval_message" class="form-control" placeholder="Message">
                                    <span class="input-group-btn">
                                        <input type="submit" class="btn btn-primary" value="Approve">
                                    </span>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form action="{{url('facility/ksp/loan-application/'.$loan_application->id.'/reject')}}" method="post">
                                {!! csrf_field() !!}
                                <div class="input-group">
                                    <input type="text" name="approval_message" class="form-control" placeholder="Message">
                                    <span class="input-group-btn">
                                        <input type="submit" class="btn btn-danger" value="Reject"> 
                                    </span>
                                </div>
                            </form>
                        </div>
                    </div>
                </fieldset>  
                @endif

                @if($list_loan_application_archived->count() > 0)
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
                                            <th>Date</th>
                                            <th>Number</th>
                                            <th>Created By</th>
                                            <th>Updated By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $count=0;?>
                                        @foreach($list_loan_application_archived as $loan_application_archived)
                                        <tr>
                                            <td class="text-center">
                                                <a href="{{ url('facility/ksp/loan-application/'.$loan_application_archived->formulir_id.'/archived') }}" data-toggle="tooltip" title="Show" class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-file"></i> {{ 'Revision ' . $count++ }}</a>
                                            </td>
                                            <td>{{ date_format_view($loan_application->formulir->form_date) }}</td>
                                            <td>{{ $loan_application_archived->formulir->archived }}</td>
                                            <td>{{ $loan_application_archived->createdBy->name }}</td>
                                            <td>{{ $loan_application_archived->updatedBy->name }}</td>
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
