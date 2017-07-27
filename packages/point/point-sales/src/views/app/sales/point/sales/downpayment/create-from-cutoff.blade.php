@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-sales::app.sales.point.sales._breadcrumb')
            <li><a href="{{ url('sales/point/indirect/downpayment') }}">Downpayment</a></li>
            <li>Create</li>
        </ul>
        <h2 class="sub-header">DOWNPAYMENT</h2>
        @include('point-sales::app.sales.point.sales.downpayment._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('sales/point/indirect/downpayment')}}" method="post" class="form-horizontal form-bordered">
                    {!! csrf_field() !!}
                    <input type="hidden" name="close" value="1">
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Downpayment Form</legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Select Cutoff Account *</label>
                        <div class="col-md-6">
                            <select class="selectize" name="cutoff_account_id" id="cutoff-account-id" data-placeholder="Please choose" onchange="selectCutoff(this.value)">
                                <option></option>
                                @foreach($list_cutoff_account as $cutoff_account)
                                <option value="{{$cutoff_account->id}}">{{$cutoff_account->formulir->form_number}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Select Account</label>
                        <div class="col-md-6">
                            <select class="selectize" name="coa_id" id="coa-id" data-placeholder="Please choose" onchange="selectAccount(this.value)">
                                
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Select Customer</label>
                        <div class="col-md-6">
                            <select class="selectize" name="person_id" id="person-id" data-placeholder="Please choose" onchange="selectCustomer(this.value)">
                                
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Select Sales Order</label>
                        <div class="col-md-6">
                            <select class="selectize" name="order_reference" id="sales-order-id"  data-placeholder="Please choose">
                                
                            </select>
                        </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Date</label>
                        <div class="col-md-3">
                            <input type="text" name="form_date" class="form-control date input-datepicker"
                                   data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}"
                                   value="{{ date(date_format_get(), strtotime(\Carbon::now())) }}">
                        </div>
                        <div class="col-md-3">
                            <div class="input-group bootstrap-timepicker">
                                <input type="text" id="time" name="time" class="form-control timepicker">
                                <span class="input-group-btn">
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-primary"><i class="fa fa-clock-o"></i></a>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Payment Type</label>
                        <div class="col-md-6">
                            <select id="payment_type" name="payment_type" class="selectize" style="width: 100%;" data-placeholder="Please choose">
                                <option value="cash">Cash</option>
                                <option value="bank">Bank</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Amount</label>
                        <div class="col-md-6">
                            <input type="text" readonly="" name="amount" id="amount" class="form-control format-quantity" value="0"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>
                        <div class="col-md-6">
                            <input type="text" name="notes" class="form-control" value="{{old('notes')}}">
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
                    </fieldset>
                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-3">
                            <button type="submit" class="btn btn-effect-ripple btn-primary">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script>
        initDatatable('#item-datatable');

        function selectCutoff(id) {
            $.ajax({
                url: "{{url('sales/point/indirect/downpayment/select-cutoff')}}",
                data: { id : id},
                success: function(res) {
                    var coa = $('#coa-id')[0].selectize;
                    coa.load(function (callback) {
                        callback(eval(JSON.stringify(res.lists)));
                    });
                },
            });
        }

        function selectAccount(coa_id) {
            cutoff_id = $("#cutoff-account-id").val();
            $.ajax({
                url: "{{url('sales/point/indirect/downpayment/select-account')}}",
                data: { coa_id : coa_id, cutoff_id : cutoff_id},
                success: function(res) {
                    var customer = $('#person-id')[0].selectize;
                    customer.load(function (callback) {
                        callback(eval(JSON.stringify(res.lists)));
                    });
                },
            });
        }

        function selectCustomer(id) {
            $.ajax({
                url: "{{url('sales/point/indirect/downpayment/select-customer')}}",
                data: { id : id},
                success: function(res) {
                    var sales_order = $('#sales-order-id')[0].selectize;
                    sales_order.load(function (callback) {
                        callback(eval(JSON.stringify(res.lists)));
                    });
                    console.log(res.lists);
                    $('#amount').val(appNum(res.cutoff_account_detail.amount));
                },
            });
        }
    </script>
@stop
