@extends('core::app.layout')
@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('bumi-deposit::app/facility/bumi-deposit/_breadcrumb')
        <li><a href="{{ url('facility/bumi-deposit/deposit') }}">Deposit</a></li>
        <li>Show</li>
    </ul>

    <h2 class="sub-header">Deposit</h2>
    @include('bumi-deposit::app.facility.bumi-deposit.deposit._menu')

    <div class="block full">
        <!-- Block Tabs Title -->
        <div class="block-title">
            <ul class="nav nav-tabs" data-toggle="tabs">
                <li class="active"><a href="#block-tabs-form">Form</a></li>
                <li><a href="#block-tabs-settings"><i class="gi gi-settings"></i></a></li>
            </ul>
        </div>
        <!-- END Block Tabs Title -->

        <!-- Tabs Content -->
        <div class="tab-content">
            <div class="tab-pane active" id="block-tabs-form">
                <div class="form-horizontal form-bordered">
                    <fieldset>
                        <div class="form-group pull-right">
                            <div class="col-md-12">
                                @include('framework::app.include._form_status_label', ['form_status' => $deposit->formulir->form_status])
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Form {{$deposit->formulir->form_number}}</legend>
                            </div>
                        </div>

                        @if($deposit->reference_deposit_id)
                        <div class="form-group">
                            <label class="col-md-3 control-label">Extended From</label>
                            <div class="col-md-9 content-show">
                                <a href="{{ url('facility/bumi-deposit/deposit/'.$deposit->reference_deposit_id) }}">{{ $deposit->reference->formulir->form_number }}</a>
                            </div>
                        </div>
                        @endif
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Date</label>
                            <div class="col-md-9 content-show">
                                {{date_format_view($deposit->formulir->form_date)}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Bank</label>
                            <div class="col-md-9 content-show">
                                {{ $deposit->bank->name }} ({{ $deposit->bank->branch }}) <br>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Bank Account</label>
                            <div class="col-md-9 content-show">
                                {{ $deposit->bankAccount->account_number }} a/n {{ $deposit->bankAccount->account_name }} <br>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Bank Product</label>
                            <div class="col-md-9 content-show">
                                {{ $deposit->bankProduct->product_name }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Category</label>
                            <div class="col-md-9 content-show">
                                {{ $deposit->category->name }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Group</label>
                            <div class="col-md-9 content-show">
                                {{ $deposit->group->name }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Owner</label>
                            <div class="col-md-9 content-show">
                                {{ $deposit->owner->name }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Bilyet Number</label>
                            <div class="col-md-9 content-show">
                                {{ $deposit->deposit_number }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Time Period</label>
                            <div class="col-md-9 content-show">
                                {{number_format_quantity($deposit->deposit_time, 2)}} {{($deposit->deposit_time < 2)?'day':'days'}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Due Date</label>
                            <div class="col-md-9 content-show">
                                {{date_format_view($deposit->due_date)}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Deposit Value</label>
                            <div class="col-md-9 content-show">
                                {{ number_format_quantity($deposit->original_amount) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Bank Interest</label>
                            <div class="col-md-9 content-show">
                                {{number_format_quantity($deposit->interest_percent)}} % @if($deposit->total_days_in_year)( {{ number_format_quantity(($deposit->deposit_time * $deposit->original_amount * $deposit->interest_percent) / ($deposit->total_days_in_year * 100)) }} )@endif
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Total Days in a Year</label>
                            <div class="col-md-9 content-show">
                                {{number_format_quantity($deposit->total_days_in_year, 0)}} days
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Tax Fee</label>
                            <div class="col-md-9 content-show">
                                {{number_format_quantity($deposit->tax_percent)}}%
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Bank Interest + Tax Fee</label>
                            <div class="col-md-9 content-show">
                                {{number_format_quantity($deposit->total_interest)}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Total Amount</label>
                            <div class="col-md-9 content-show">
                                {{ number_format_quantity($deposit->total_amount) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Notes</label>
                            <div class="col-md-9 content-show">
                                {!! nl2br(e($deposit->formulir->notes)) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Important Notes</label>
                            <div class="col-md-9 content-show">
                                {!! nl2br(e($deposit->important_notes)) !!}
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
                            <div class="col-md-9 content-show">
                                {{ $deposit->formulir->createdBy->name }}
                            </div>
                        </div>
                    </fieldset>

                    @if(formulir_is_close($deposit->formulir_id))
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Withdrawal</legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Date</label>
                            <div class="col-md-9 content-show">
                                {{ date_format_view($deposit->withdraw_date) }}
                             </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Amount</label>
                            <div class="col-md-9 content-show">
                                {{ number_format_quantity($deposit->withdraw_amount) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Notes</label>
                            <div class="col-md-9 content-show">
                                {!! nl2br(e($deposit->withdraw_notes)) !!}
                            </div>
                        </div>
                    </fieldset>
                    @endif
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
                            @if(formulir_view_edit($deposit->formulir, 'update.bumi.deposit'))
                            <a href="{{url('facility/bumi-deposit/deposit/'.$deposit->id.'/edit')}}" class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                            @endif

                            @if(formulir_view_edit($deposit->formulir, 'create.bumi.deposit'))
                                @if($deposit->formulir->form_status == 0)
                                    <a href="{{url('facility/bumi-deposit/deposit/'.$deposit->id.'/withdraw')}}" class="btn btn-effect-ripple btn-info"><i class="fa fa-money"></i> Withdraw</a>
                                    <a href="{{url('facility/bumi-deposit/deposit/'.$deposit->id.'/extend')}}" class="btn btn-effect-ripple btn-info"><i class="fa fa-arrow-circle-up"></i> Extend</a>
                                @endif
                            @endif
                            @if(formulir_view_cancel($deposit->formulir, 'update.bumi.deposit'))
                            <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                               onclick="secureCancelForm('{{url('formulir/cancel')}}',
                                       '{{ $deposit->formulir_id }}',
                                       'delete.bumi.deposit')"><i class="fa fa-times"></i> Cancel</a>
                            @endif
                        </div>
                    </div>
                </fieldset>

                @if(formulir_view_approval($deposit->formulir, 'update.bumi.deposit'))
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Approval</legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-6">
                            <form action="{{url('facility/bumi-deposit/deposit/'.$deposit->id.'/approve')}}" method="get">
                                {!! csrf_field() !!}
                                <input type="text" name="approval_message" class="form-control" placeholder="Message">
                                <input type="hidden" name=type" value="{{ $deposit->formulir->edit_notes }}" />
                                <hr/>
                                <input type="submit" class="btn btn-primary" value="Approve">
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form action="{{url('facility/bumi-deposit/deposit/'.$deposit->id.'/reject')}}" method="get">
                                {!! csrf_field() !!}
                                <input type="text" name="approval_message" class="form-control" placeholder="Message">
                                <input type="hidden" name=type" value="{{ $deposit->formulir->edit_notes }}" />
                                <hr/>
                                <input type="submit" class="btn btn-danger" value="Reject">
                            </form>
                        </div>
                    </div>
                </fieldset>
                @endif

                @if($revision)
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
                                        <th>Edit Reason</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $count=0;?>
                                    @foreach($list_deposit_archived as $deposit_archived)
                                        <tr>
                                            <td class="text-center">
                                                <a href="{{ url('facility/bumi-deposit/deposit/'.$deposit_archived->id.'/archived') }}" data-toggle="tooltip" title="Show" class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-file"></i> {{ 'Revision ' . $count++ }}</a>
                                            </td>
                                            <td>{{ date_format_view($deposit_archived->formulir->form_date) }}</td>
                                            <td>{{ $deposit_archived->formulir->archived }}</td>
                                            <td>{{ $deposit_archived->formulir->createdBy->name }}</td>
                                            <td>{{ $deposit_archived->formulir->updatedBy->name }}</td>
                                            <td>{{ $deposit_archived->formulir->edit_notes}}</td>
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
