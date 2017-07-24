@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('bumi-deposit::app/facility/bumi-deposit/_breadcrumb')
        <li><a href="{{ url('facility/bumi-deposit/deposit') }}">Deposit</a></li>
        <li><a href="{{ url('facility/bumi-deposit/deposit/'.$deposit->id) }}">{{ $deposit->formulir->form_number }}</a></li>
        <li>Withdraw</li>
    </ul>

    <h2 class="sub-header">Deposit</h2>
    @include('bumi-deposit::app.facility.bumi-deposit.deposit._menu')
    @include('core::app.error._alert')

    <div class="panel panel-default">
        <div class="panel-body">
            <form action="{{url('facility/bumi-deposit/deposit/'.$deposit->id.'/store-withdraw')}}" method="post" class="form-horizontal form-bordered">
                {!! csrf_field() !!}

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


                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Date</label>
                        <div class="col-md-9 content-show">
                            {{date_format_view($deposit->formulir->form_date)}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Bank</label>
                        <div class="col-md-9 content-show">
                            {{ $deposit->bank->name }} ({{ $deposit->bank->branch }})
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
                            {{number_format_quantity($deposit->interest_percent)}}%
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
                            {{number_format_quantity($deposit->interest_value)}}
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
                            <legend><i class="fa fa-angle-right"></i> Withdrawal</legend>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Date *</label>
                        <div class="col-md-9">
                            <input type="text" name="withdraw_date" class="form-control date input-datepicker" data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}" value="{{ date(date_format_get(), strtotime($deposit->due_date)) }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Amount *</label>
                        <div class="col-md-9">
                            <input type="text" name="withdraw_amount" id="withdraw_amount" class="form-control format-quantity point-control" value="{{ number_format_db($deposit->total_amount) }}" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>
                        <div class="col-md-9">
                            <textarea name="withdraw_notes" class="form-control autosize"></textarea>
                        </div>
                    </div>
                </fieldset>

                <div class="form-group">
                    <div class="col-md-6 col-md-offset-3">
                        <button type="submit" class="btn btn-effect-ripple btn-primary">Withdraw</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
