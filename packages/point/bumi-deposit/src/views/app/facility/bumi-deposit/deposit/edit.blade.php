@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('bumi-deposit::app/facility/bumi-deposit/_breadcrumb')
            <li><a href="{{ url('facility/bumi-deposit/deposit') }}">Deposit</a></li>
            <li><a href="{{ url('facility/bumi-deposit/deposit/'.$deposit->id) }}">{{ $deposit->formulir->form_number }}</a></li>
            <li>Edit</li>
        </ul>

        <h2 class="sub-header">Deposit</h2>
        @include('bumi-deposit::app.facility.bumi-deposit.deposit._menu')
        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{ url('facility/bumi-deposit/deposit/'.$deposit->id) }}" method="post" class="form-horizontal form-bordered">
                    <input type="hidden" name="_method" value="PUT">
                    {!! csrf_field() !!}

                    <div class="form-group">
                        <label class="col-md-3 control-label">Reason edit *</label>
                        <div class="col-md-9">
                            <input type="text" name="edit_notes" class="form-control" value="" required autofocus>
                        </div>
                    </div>
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Form</legend>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Date *</label>
                            <div class="col-md-9">
                                <input type="text" name="form_date" id="form_date" class="form-control date input-datepicker" data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}" value="{{ date(date_format_get(), strtotime($deposit->formulir->form_date)) }}" onchange="depositTime()">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Bank *</label>
                            <div class="col-md-9">
                                <select class="selectize" style="width: 100%;" data-placeholder="Choose one.." name="deposit_bank_id" id="deposit_bank_id">
                                    @foreach( $banks as $bank )
                                        <option value="{{ $bank->id }}" @if($bank->id == $deposit->deposit_bank_id) selected @endif> {{ $bank->name }}  ({{ $bank->branch }}) </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Bank Account *</label>
                            <div class="col-md-9">
                                <select class="selectize" style="width: 100%;" data-placeholder="Choose one.." name="deposit_bank_account_id" id="deposit_bank_account_id">

                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Bank Product *</label>
                            <div class="col-md-9">
                                <select class="selectize" style="width: 100%;" data-placeholder="Choose one.." name="deposit_bank_product_id" id="deposit_bank_product_id">

                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Category *</label>
                            <div class="col-md-9">
                                <select class="selectize" style="width: 100%;" data-placeholder="Choose one.." name="deposit_category_id">
                                    @foreach( $categories as $category )
                                        <option value="{{ $category->id }}" @if($deposit->deposit_category_id == $category->id) selected @endif> {{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Group *</label>
                            <div class="col-md-9">
                                <select class="selectize" style="width: 100%;" data-placeholder="Choose one.." name="deposit_group_id">
                                    @foreach( $groups as $group )
                                        <option value="{{ $group->id }}" @if($deposit->deposit_group_id == $group->id) selected @endif> {{ $group->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Owner *</label>
                            <div class="col-md-9">
                                <select class="selectize" style="width: 100%;" data-placeholder="Choose one.." name="deposit_owner_id">
                                    @foreach( $owners as $owner )
                                        <option value="{{ $owner->id }}" @if($deposit->deposit_owner_id == $owner->id) selected @endif> {{ $owner->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Bilyet Number</label>
                            <div class="col-md-9">
                                <input type="text" name="deposit_number" id="deposit_number" class="form-control" value="{{ $deposit->deposit_number }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Time Period *</label>
                            <div class="col-md-9">
                                <div class="input-group">
                                    <input type="text" name="deposit_time" id="deposit_time" class="form-control format-quantity point-control" value="{{ $deposit->deposit_time }}" onkeyup="depositTime()">
                                    <span class="input-group-addon">Days</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Due Date *</label>
                            <div class="col-md-9">
                                <input type="text" name="due_date" id="due_date" class="form-control date input-datepicker" data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}" value="{{ date(date_format_get(), strtotime($deposit->due_date)) }}" readonly />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Deposit Value *</label>
                            <div class="col-md-9">
                                <input type="text" name="original_amount" id="original_amount" class="form-control format-quantity point-control" value="{{ $deposit->original_amount }}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Bank Interest *</label>
                            <div class="col-md-9">
                                <div class="input-group">
                                    <input type="text" name="interest_percent" id="interest_percent" class="form-control format-percent point-control" value="{{ $deposit->interest_percent }}" />
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
                                    <input type="text" name="tax_percent" id="tax_percent" value="{{ $deposit->tax_percent }}" class="form-control format-percent point-control" />
                                    <span class="input-group-addon">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Bank Interest + Tax Fee</label>
                            <div class="col-md-9">
                                <input type="text" readonly name="total_interest" id="total_interest" value="{{ $deposit->total_interest }}" class="form-control format-quantity" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Total Amount</label>
                            <div class="col-md-9">
                                <input type="text" readonly name="total_amount" id="total_amount" value="{{ $deposit->total_amount }}" class="form-control format-quantity" />
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
                                <textarea type="text" name="important_notes" id="important_notes" class="form-control autosize">{{ $deposit->important_notes }}</textarea>
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
                                {{ auth()->user()->name }}
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
                                <label class="col-md-3 control-label">Date *</label>
                                <div class="col-md-9">
                                    <input type="text" name="withdraw_date" class="form-control date input-datepicker" data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}" value="{{ date(date_format_get(), strtotime($deposit->withdraw_date)) }}">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-3 control-label">Amount *</label>
                                <div class="col-md-9">
                                    <input type="text" name="withdraw_amount" id="withdraw_amount" class="form-control format-quantity point-control" value="{{ number_format_db($deposit->withdraw_amount) }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-3 control-label">Notes</label>
                                <div class="col-md-9">
                                    <textarea name="withdraw_notes" class="form-control autosize">{{ $deposit->withdraw_notes }}</textarea>
                                </div>
                            </div>
                        </fieldset>
                    @endif

                    <div class="form-group">
                        <div class="col-md-12 col-md-offset-3">
                            <button type="submit" class="btn btn-effect-ripple btn-primary">{{trans('framework::framework/global.button.submit')}}</button>
                        </div>
                    </div>
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

        selectBank($('#deposit_bank_id').val());

        function selectBank(bank_id)
        {
            $.ajax({
                url: "{{URL::to('facility/bumi-deposit/bank/select')}}",
                type: 'GET',
                data: {
                    bank_id: bank_id
                },
                success: function(data) {
                    var selectize = $("#deposit_bank_account_id")[0].selectize;
                    selectize.clear();
                    selectize.clearOptions();
                    selectize.load(function(callback) {
                        callback(eval(JSON.stringify(data.list_account)));
                        selectize.addItem({{$deposit->deposit_bank_account_id}});
                    });

                    var selectize = $("#deposit_bank_product_id")[0].selectize;
                    selectize.clear();
                    selectize.clearOptions();
                    selectize.load(function(callback) {
                        callback(eval(JSON.stringify(data.list_product)));
                        selectize.addItem({{$deposit->deposit_bank_product_id}});
                    });
                }, error: function(data) {

                }
            });
        }
    </script>
@stop
