@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.fixed-assets._breadcrumb')
            <li>Create</li>
        </ul>
        <h2 class="sub-header">Contract | Fixed Assets</h2>
        @include('point-purchasing::app.purchasing.point.fixed-assets.contract._menu')

        @include('core::app.error._alert')
        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('purchasing/point/fixed-assets/contract')}}" method="post"
                      class="form-horizontal form-bordered">
                    {!! csrf_field() !!}
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Form</legend>
                            </div>
                        </div>
                    </fieldset>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Asset Account *</label>
                        <div class="col-md-6 content-show">
                        <input type="hidden" name="coa_id" value="{{$contract_reference->journal->coa_id}}"> 
                        <input type="hidden" name="journal_id" value="{{$contract_reference->journal->id}}"> 
                        
                        {{$contract_reference->journal->coa->name}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Acquisition date *</label>
                        <div class="col-md-6">
                            <input type="text" name="form_date" class="form-control date input-datepicker"
                                   data-date-format="{{\DateHelper::formatMasking()}}"
                                   placeholder="{{\DateHelper::formatMasking()}}"
                                   value="{{old('form_date')}}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Asset name *</label>
                        <div class="col-md-6">
                            <input type="text" name="name" class="form-control" value="{{$contract_reference->name}}">
                            <input type="hidden" name="asset_name" class="form-control" value="{{$contract_reference->name}}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Useful life *</label>
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" id="useful-life" name="useful_life" class="form-control text-right format-quantity" value="{{ old('useful_life') }}" onkeyup="calculateDepreciation()"/>
                                <span class="input-group-addon">MONTH</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Salvage Value *</label>
                        <div class="col-md-6">
                            <input type="text" id="salvage-value" name="salvage_value" class="form-control text-right format-quantity"
                                   value="{{ old('salvage_value') ?  : $contract_reference->salvage_value ? : 0 }}" onkeyup="calculateDepreciation()"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Purchase date *</label>
                        <div class="col-md-6">
                            <input type="text" name="purchase_date" class="form-control date input-datepicker"
                                   data-date-format="{{\DateHelper::formatMasking()}}"
                                   placeholder="{{\DateHelper::formatMasking()}}"
                                   value="{{ date(date_format_get(), strtotime($contract_reference->date_purchased)) }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Supplier</label>
                        <div class="col-md-6 content-show">
                            {{$contract_reference->supplier->codeName}}
                            <input type="hidden" name="supplier_id" value="{{$contract_reference->supplier_id}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Quantity *</label>
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" id="quantity" name="quantity"
                                       class="form-control text-right format-quantity" value="{{ $contract_reference->quantity }}" readonly="" />
                                <input type="hidden" name="unit" value="{{$contract_reference->unit}}">
                                <span class="input-group-addon">{{$contract_reference->unit}}</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Price *</label>
                        <div class="col-md-6">
                            <input type="text" id="price" name="price" class="form-control text-right format-quantity"
                                   value="{{ $contract_reference->price }}" readonly="" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Total price *</label>
                        <div class="col-md-6">
                            <input type="text" id="total-price" name="total_price" class="form-control text-right format-quantity"
                                   value="{{ $contract_reference->total_price }}" readonly/>
                            <span id="hitung-total" class="help-block"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Total paid </label>
                        <div class="col-md-6">
                            <input type="text" id="total-paid" name="total_paid" class="form-control text-right format-quantity" value="{{ $contract_reference->total_paid }}" onkeyup="calculate()"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">depreciation *</label>
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" id="depreciation" name="depreciation" class="form-control text-right format-quantity" value="{{ old('depreciation') }}"/>
                                <span class="input-group-addon">/ month</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">notes</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" value="{{ $contract_reference->formulir->notes}}" name="notes" />
                        </div>
                    </div>
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Details</legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="service-datatable" class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th></th>
                                        <th style="min-width:220px">Description</th>
                                        <th style="min-width:220px">Date</th>
                                    </tr>
                                    </thead>
                                    <tbody class="manipulate-row">
                                        <tr>
                                            <td>
                                                <a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a>
                                            </td>
                                            <td>
                                                <input type="hidden" name="fixed_assets_contract_reference_id[]" value="{{$contract_reference->id}}">
                                                <select class="selectize" name="form_reference_id" place-holder="select one ...">
                                                    <option value="{{$contract_reference->formulir->id}}">
                                                        {{$contract_reference->formulir->form_number . ' - ' . $contract_reference->formulir->form_notes}}
                                                    </option>
                                                </select>
                                            </td>
                                            <td>
                                                {{date_format_view($contract_reference->formulir->form_date)}}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Person In Charge</legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Creator</label>
                            <div class="col-md-6 content-show">
                                {{\Auth::user()->name}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Ask Approval To</label>
                            <div class="col-md-6">
                                <select id="approval-to" name="approval_to" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                    <option></option>
                                    @foreach($list_user_approval as $user_approval)
                                        @if($user_approval->may('approval.point.purchasing.contract'))
                                            <option value="{{$user_approval->id}}" @if(old('user_approval') == $user_approval->id) selected @endif>{{$user_approval->name}}</option>
                                        @endif
                                    @endforeach
                                </select>
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

        function calculateDepreciation() {
            var quantity = $('#quantity').val();
            var price = $('#price').val();
            var total_price = dbNum(quantity) * dbNum(price);
            $('#total-price').val(appNum(total_price));

            var salvage_value = dbNum($('#salvage-value').val());
            var useful_life = dbNum($('#useful-life').val());
            var acquisition = dbNum($('#total-price').val());
            var activa_cost = dbNum($('#total-paid').val());
            var result = (acquisition + activa_cost - salvage_value) / (12 * useful_life);

            $('#depreciation').val(appNum(result));
        }
        var counter = 0;
        var other_datatable = initDatatable('#other-datatable');

        $('#addItemRow').on('click', function () {
            other_datatable.row.add([
                '<a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a>',
                '<select id="coa-id-' + counter + '" name="coa_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
                + '<option ></option>'
                @foreach($list_account as $coa)
                + '<option value="{{$coa->id}}">{{$coa->account}}</option>'
                @endforeach
                + '</select>',
                '<input type="text" name="detail_notes[]" class="form-control" value="" />',
                '<input type="text" id="total-other-' + counter + '" name="total[]"  class="form-control format-quantity row-total calculate" value="0" />',
                '<select id="allocation-id-' + counter + '" name="allocation_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
                @foreach($list_allocation as $allocation)
                + '<option value="{{$allocation->id}}">{{$allocation->name}}</option>'
                @endforeach
                + '</select>'
            ]).draw(false);

            initSelectize('#coa-id-' + counter);
            initSelectize('#allocation-id-' + counter);
            initFormatNumber();
            $('.calculate').keyup(function () {
                calculate();
            });
            counter++;
        });

        $(document).on("keypress", 'form', function (e) {
            var code = e.keyCode || e.which;
            if (code == 13) {
                e.preventDefault();
                return false;
            }
        });

        $('.calculate').keyup(function () {
            calculate();
        });

        $(function () {
            calculate();
            // reloadInvoice('#search-invoice');
        });

        function reloadInvoice(element) {
            $.ajax({
                url: "{{URL::to('purchasing/point/fixed-assets/invoice/list')}}",
                success: function (data) {
                    var invoice = $(element)[0].selectize;
                    invoice.load(function (callback) {
                        callback(eval(JSON.stringify(data.lists)));
                    });

                }, error: function (data) {
                    // swal('Failed', 'Something went wrong', 'error');
                }
            });
        }

        function searchDetailInvoice(value) {
            $('.loader').fadeIn();
            $("#result-invoice-detail").fadeOut();
            $.ajax({
                url: "{{URL::to('purchasing/point/fixed-assets/invoice/search')}}",
                data: { id : value },
                success: function (data) {
                    $('.loader').fadeOut();
                    $("#result-invoice-detail").fadeIn();
                    $("#result-invoice-detail").html(data);

                }, error: function (data) {
                    swal('Failed', 'Something went wrong', 'error');
                }
            });

        }
        function calculate() {
            var rows = $("#other-datatable").dataTable().fnGetNodes();
            var total_other = 0;
            for (var i = 0; i < rows.length; i++) {
                if ($("#total-other-" + i).length != 0) {
                    total_other += dbNum($("#total-other-" + i).val());
                }
            }

            $('#total-detail').val(appNum(total_other));
        }

        $('#other-datatable tbody').on('mouseup', '.remove-row', function () {
            other_datatable.row($(this).parents('tr')).remove().draw();
            calculate();
        });
    </script>
@stop
