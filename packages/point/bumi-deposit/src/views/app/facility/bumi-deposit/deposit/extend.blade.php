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
                <form action="{{url('facility/bumi-deposit/deposit/'.$deposit->id.'/store-extend')}}" method="post" class="form-horizontal form-bordered">
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
                            <label class="col-md-3 control-label">Form Date *</label>
                            <div class="col-md-9">
                                <input type="text" name="form_date" id="form_date" class="form-control date input-datepicker" data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}" value="{{ date(date_format_get(), strtotime($deposit->due_date)) }}" onchange="depositTime()">
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
                            <label class="col-md-3 control-label">Time Period *</label>
                            <div class="col-md-9">
                                <div class="input-group">
                                    <input type="hidden" name="deposit_number" id="deposit_number" class="form-control" value="">
                                    <input type="text" name="deposit_time" id="deposit_time" class="form-control format-quantity point-control" value="{{ $deposit->deposit_time }}" onkeyup="depositTime()">
                                    <span class="input-group-addon">Days</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Due Date *</label>
                            <div class="col-md-9">
                                <input type="text" name="due_date" id="due_date" class="form-control" value="" readonly />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Deposit Value *</label>
                            <div class="col-md-9">
                                <input type="text" name="original_amount" id="original_amount" class="form-control format-quantity point-control" value="{{ $deposit->total_amount }}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Bank Interest *</label>
                            <div class="col-md-9">
                                <div class="input-group">
                                    <input type="text" name="interest_percent" id="interest_percent" class="form-control format-quantity point-control" value="{{ $deposit->interest_percent }}" />
                                    <span class="input-group-addon">%</span>
                                </div>

                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Total Days in a Year *</label>
                            <div class="col-md-9">
                                <div class="input-group">
                                    <input type="text" name="total_days_in_year" id="total_days_in_year" class="form-control format-quantity point-control" value="{{ $deposit->total_days_in_year }}" />
                                    <span class="input-group-addon">Days</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Tax Fee *</label>
                            <div class="col-md-9">
                                <div class="input-group">
                                    <input type="text" name="tax_percent" id="tax_percent" value="{{ $deposit->tax_percent }}" class="form-control format-quantity point-control" />
                                    <span class="input-group-addon">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Bank Interest + Tax Fee</label>
                            <div class="col-md-9">
                                <input type="text" readonly name="total_interest" id="total_interest" value="" class="form-control format-quantity" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Total Amount</label>
                            <div class="col-md-9">
                                <input type="text" readonly name="total_amount" id="total_amount" value="" class="form-control format-quantity" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Notes</label>
                            <div class="col-md-9">
                                <textarea name="notes" id="notes" class="form-control autosize">{{ $deposit->formulir->notes }}</textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Important Notes</label>
                            <div class="col-md-9">
                                <textarea type="text" name="important_notes" id="important_notes" class="form-control">{{ $deposit->important_notes }}</textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-3">
                                <input type="hidden" name="withdraw_date" class="form-control date input-datepicker" data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}" value="{{ date(date_format_get(), strtotime($deposit->due_date)) }}">
                                <input type="hidden" name="withdraw_amount" id="withdraw_amount" class="form-control format-quantity point-control" value="{{ number_format_db($deposit->total_amount) }}" />
                                <input type="hidden" name="withdraw_notes" class="form-control" value="{{$deposit->withdraw_notes}}" />
                                <button type="submit" class="btn btn-effect-ripple btn-primary">Extend</button>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script>
        function calculate() {
            var deposit_time = dbNum( $("#deposit_time").val() );
            var original_amount = dbNum( $("#original_amount").val() );
            var interest_percent = dbNum( $("#interest_percent").val() );
            var tax_percent = dbNum( $("#tax_percent").val() );
            var total_days_in_year = dbNum( $("#total_days_in_year").val() );

            var total_interest = (deposit_time * original_amount * interest_percent) / (total_days_in_year * 100);
            total_interest = Math.round(total_interest);
            total_interest = total_interest - (total_interest * tax_percent / 100);
            total_interest = Math.round(total_interest);
            var total_amount = total_interest + original_amount;
            $("#total_interest").val( accountingNum(total_interest) );
            $("#total_amount").val( accountingNum(total_amount) );
        }
        $('.point-control').on('keyup change', function(){ calculate(); });

        function depositTime() {
            var form_date = $('#form_date').val();
            var deposit_time = $('#deposit_time').val();
            var due_date = moment(form_date, '{{\Point\Core\Models\Setting::where('name','=','date-moment')->first()->value}}').add(deposit_time, 'days').format('{{\Point\Core\Models\Setting::where('name','=','date-moment')->first()->value}}');
            $('#due_date').val(due_date);
        }

        $( document ).ready(function() {
            depositTime();
            calculate();
        });

    </script>
@stop
