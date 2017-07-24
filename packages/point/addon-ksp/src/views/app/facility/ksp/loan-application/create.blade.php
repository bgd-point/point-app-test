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

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('facility/ksp/loan-application')}}" method="post" class="form-horizontal form-bordered">
                    {!! csrf_field() !!}

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Form</legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Date *</label>
                        <div class="col-md-3">
                            <input type="text" name="form_date" class="form-control date input-datepicker"
                                    data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}"
                                    value="{{date(date_format_get(), strtotime(\Carbon::now()))}}">
                        </div>
                        <div class="col-md-3">
                            <div class="input-group bootstrap-timepicker">
                                <input type="text" id="time" name="time" class="form-control timepicker">
                                <span class="input-group-btn">
                                <a href="javascript:void(0)" class="btn btn-effect-ripple btn-primary">
                                    <i class="fa fa-clock-o"></i>
                                </a>
                            </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Customer *</label>
                        <div class="col-md-6">
                            <select id="customer-id" name="customer_id" class="selectize" data-placeholder="Choose..">
                                <option></option>
                                @foreach($list_customer as $customer)
                                    <option value="{{$customer->id}}" @if(old('person_id') == $customer->id ? : $selected_customer) selected @endif>{{$customer->codeName}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Loan Amount *</label>
                        <div class="col-md-6">
                            <input type="text" id="loan-amount" name="loan_amount" class="form-control format-quantity" value="{{ old('loan_amount') ? : $loan_amount }}" onkeyup="calculate()" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">periods *</label>
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" id="periods" name="periods" class="form-control format-quantity" value="{{ old('periods') ? : $periods }}" onkeyup="calculate()" />
                                <span class="input-group-addon">months</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Interest *</label>
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" id="interest_rate" name="interest_rate" class="form-control format-quantity" value="{{ old('interest_rate') ? : $interest_rate }}" onkeyup="calculate()" />
                                <span class="input-group-addon">%</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Interest Rate Type *</label>
                        <div class="col-md-6">
                            <select id="interest-rate-type" name="interest_rate_type" class="selectize" data-placeholder="Choose..">
                                <option></option>
                                <option value="flat" {{ $interest_rate_type == 'flat' ? 'selected' : '' }}>Flat</option>
                                <option value="effective" {{ $interest_rate_type == 'effective' ? 'selected' : '' }}>Effective</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Payment Type *</label>
                        <div class="col-md-6">
                            <select id="payment-type" name="payment_type" class="selectize" data-placeholder="Choose..">
                                <option></option>
                                <option value="cash" @if(old('payment_type') == 'cash' ? : $payment_type) selected @endif>Cash</option>
                                <option value="bank" @if(old('payment_type') == 'bank' ? : $payment_type) selected @endif>Bank</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Payment Account *</label>
                        <div class="col-md-6">
                            <select id="payment-account" name="payment_account" class="selectize" data-placeholder="Choose..">
                                <option></option>
                                @foreach($list_account_bank as $account_bank)
                                    <option value="{{ $account_bank->id }}" @if(old('payment_account') == $account_bank->id ? : $payment_account) selected @endif>{{ $account_bank->account }}</option>
                                @endforeach
                            </select>
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
                                {{auth()->user()->name}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Request Approval To *</label>

                            <div class="col-md-6">
                                <select name="approval_to" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                    @foreach($list_user_approval as $user_approval)
                                        @if($user_approval->may('approval.point.purchasing.requisition'))
                                            <option value="{{$user_approval->id}}"
                                                    @if(old('approval_to') == $user_approval->id) selected @endif>{{$user_approval->name}}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </fieldset>

                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-3">
                            <button type="submit" class="btn btn-effect-ripple btn-primary" onclick="$('#submit_type').val('calculate')">Calculate</button>
                            <input type="hidden" id="submit_type" name="submit_type" value="calculate">
                            @if(app('request')->isMethod('post'))
                            <button type="submit" class="btn btn-effect-ripple btn-primary" onclick="$('#submit_type').val('submit')">Submit</button>
                            @endif
                        </div>
                    </div>
                </form>

                @if(app('request')->isMethod('post') && $interest_rate_type == 'flat')
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th>Month</th>
                            <th class="text-right">Main Installment</th>
                            <th class="text-right">Interest Installment</th>
                            <th class="text-right">Installment</th>
                            <th class="text-right">Balance</th>
                        </tr>
                        </thead>
                        <tbody>
                        @for($i = 0; $i <= $periods; $i++)
                            <?php
                            $main_installment = $loan_amount / $periods;
                            $interest_installment = ($loan_amount * $interest_rate / 100) / 12;
                            $installment = $main_installment + $interest_installment;
                            $balance = $loan_amount - $main_installment * $i;
                            ?>
                            <tr>
                                <td>{{ $i }}</td>
                                <td class="text-right">{{ $i == 0 ? '-' : number_format_price($main_installment, 0) }}</td>
                                <td class="text-right">{{ $i == 0 ? '-' : number_format_price($interest_installment, 0) }}</td>
                                <td class="text-right">{{ $i == 0 ? '-' : number_format_price($installment, 0) }}</td>
                                <td class="text-right">{{ number_format_price(abs($balance), 0) }}</td>
                            </tr>
                        @endfor
                        <tr>
                            <td></td>
                            <td class="text-right"><b>{{ number_format_price($loan_amount, 0) }}</b></td>
                            <td class="text-right"><b>{{ number_format_price($interest_installment * $periods, 0) }}</b></td>
                            <td class="text-right"><b>{{ number_format_price($installment * $periods, 0) }}</b></td>
                            <td></td>
                        </tr>
                        </tbody>
                    </table>
                @endif

                @if(app('request')->isMethod('post') && $interest_rate_type == 'effective')
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th>Month</th>
                            <th class="text-right">Main Installment</th>
                            <th class="text-right">Interest Installment</th>
                            <th class="text-right">Installment</th>
                            <th class="text-right">Balance</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $total_interest = 0;?>
                        @for($i = 0; $i <= $periods; $i++)
                            <?php
                            $main_installment = $loan_amount / $periods;
                            $e_balance = $loan_amount - ($main_installment * ($i - 1));
                            $interest_installment = ($e_balance * $interest_rate / 100) / 12;
                            $total_interest += $interest_installment;
                            $installment = $main_installment + $interest_installment;
                            $balance = $e_balance - $main_installment;
                            ?>
                            <tr>
                                <td>{{ $i }}</td>
                                <td class="text-right">{{ $i == 0 ? '-' : number_format_price($main_installment, 0) }}</td>
                                <td class="text-right">{{ $i == 0 ? '-' : number_format_price($interest_installment, 0) }}</td>
                                <td class="text-right">{{ $i == 0 ? '-' : number_format_price($installment, 0) }}</td>
                                <td class="text-right">{{ number_format_price(abs($balance), 0) }}</td>
                            </tr>
                        @endfor
                        <tr>
                            <td></td>
                            <td class="text-right"><b>{{ number_format_price($loan_amount, 0) }}</b></td>
                            <td class="text-right"><b>{{ number_format_price($total_interest, 0) }}</b></td>
                            <td class="text-right"><b>{{ number_format_price($loan_amount + $total_interest, 0) }}</b></td>
                            <td></td>
                        </tr>
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
@stop
