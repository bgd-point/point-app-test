@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('ksp::app/facility/ksp/_breadcrumb')
            <li>Loan Simulator</li>
        </ul>

        <h2 class="sub-header">Loan Simulator</h2>

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('facility/ksp/loan-simulator')}}" method="post" class="form-horizontal form-bordered">
                    {!! csrf_field() !!}

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Form</legend>
                            </div>
                        </div>
                    </fieldset>
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
                                <input type="text" id="periods" name="periods" class="form-control format-quantity" value="{{ old('periods') ? : $periods  }}" onkeyup="calculate()" />
                                <span class="input-group-addon">months</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Interest Rate *</label>
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" id="interest" name="interest" class="form-control format-quantity" value="{{ old('interest') ? : $interest}}" onkeyup="calculate()" />
                                <span class="input-group-addon">%</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Interest Rate Type *</label>
                        <div class="col-md-6">
                            <select id="interest-rate-type" name="interest_rate_type" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                <option value="flat" {{ $interest_rate_type == 'flat' ? 'selected' : '' }}>Flat</option>
                                <option value="effective" {{ $interest_rate_type == 'effective' ? 'selected' : '' }}>Effective</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-3">
                            <button type="submit" class="btn btn-effect-ripple btn-primary">Calculate</button>
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
                                <th class="text-right">Total Installment</th>
                                <th class="text-right">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                        @for($i = 0; $i <= $periods; $i++)
                            <?php
                                $main_installment = $loan_amount / $periods;
                                $interest_installment = ($loan_amount * $interest / 100) / 12;
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
                            $interest_installment = ($e_balance * $interest / 100) / 12;
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
