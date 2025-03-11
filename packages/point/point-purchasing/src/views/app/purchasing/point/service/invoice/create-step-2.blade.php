@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.service._breadcrumb')
            <li><a href="{{ url('purchasing/point/service/invoice') }}">Invoice</a></li>
            <li>Create</li>
        </ul>
        <h2 class="sub-header">Invoice</h2>
        @include('point-purchasing::app.purchasing.point.service.invoice._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form
                    action="{{url('purchasing/point/service/invoice')}}"
                    method="post"
                    class="form-horizontal form-bordered">
                    {!! csrf_field() !!}

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend>
									<i class="fa fa-angle-right"></i> Form
								</legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Date</label>

                        <div class="col-md-3">
                            <input
                                type="text"
                                name="form_date" class="form-control date input-datepicker"
                                data-date-format="{{date_format_masking()}}"
                                placeholder={date_format_masking()}}"
                                value="{{ date(date_format_get(), strtotime(\Carbon::now())) }}">
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
                        <label class="col-md-3 control-label">Due Date</label>

                        <div class="col-md-3">
                            <input
                                type="text"
                                name="due_date"
                                class="form-control date input-datepicker"
                                data-date-format="{{date_format_masking()}}"
                                placeholder="{{date_format_masking()}}"
                                value="{{ date(date_format_get(), strtotime(\Carbon::now())) }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Supplier</label>

                        <div class="col-md-6 content-show">
                            <input type="hidden" name="person_id" value="{{ $purchase_order->person_id }}">
                            {!! get_url_person($purchase_order->person->id) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>

                        <div class="col-md-6">
                            <input type="text" name="notes" class="form-control" value="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">References To</label>

                        <div class="col-md-6 content-show">
                            <input type="hidden" name="reference_id" value="{{ $purchase_order->id }}">
                            {!! formulir_url($purchase_order->formulir) !!}
                        </div>
                    </div>
                    <!-- LIST SERVICE -->
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend>
                                    <i class="fa fa-angle-right"></i> Service
                                </legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="service-datatable" class="table table-striped">
                                    <thead>
                                    <tr>
                                        {{-- <th style="width:20px"></th> --}}
                                        <th style="width:220px">SERVICE</th>
                                        <th style="width:220px">ALLOCATION</th>
                                        <th style="width:220px">NOTES</th>
                                        <th style="width:80px" class="text-right">QUANTITY</th>
                                        <th style="width:120px" class="text-right">PRICE</th>
                                        <th style="width:100px" class="text-right">DISCOUNT</th>
                                        <th style="width:150px" class="text-right">TOTAL</th>
                                    </tr>
                                    </thead>
                                    <tbody class="manipulate-row">

                                    @foreach($purchase_order->services as $key=>$service)
                                        <tr class="invoice-services">
                                            {{-- <td>
                                                <a href="javascript:void(0)" class="remove-row btn btn-danger">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            </td> --}}
                                            <td>
                                                <input type="hidden" name="detail_order_id[]" value="{{ $service->id }}">
                                                <input type="hidden" name="service_id[]" value="{{ $service->service->id }}">
                                                {{ $service->service->name }}
                                            </td>
                                            <td>
                                                <select
                                                    id="service-allocation-id-{{ $key }}"
                                                    name="service_allocation_id[]"
                                                    class="selectize"
                                                    style="width: 100%;"
                                                    data-placeholder="Choose one..">
                                                    @foreach($list_allocation as $allocation)
                                                        <option  value="{{  $allocation->id }}"
                                                            {{ $allocation->id == $service->allocation_id ?  'selected': '' }}>
                                                            {{$allocation->name}}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    name="service_notes[]"
                                                    value="{{ old('service_notes')[$key] }}">
                                            </td>
                                            <td class="text-right">
                                                <input
                                                    id="service-quantity-{{ $key }}"
                                                    type="text"
                                                    name="service_quantity[]"
                                                    class="form-control format-quantity text-right calculate"
                                                    value="{{ ReferHelper::remaining(get_class($service), $service->id, $service->quantity) }}"/>
                                            </td>
                                            <td>
                                                <input
                                                    type="text"
                                                    id="service-price-{{ $key }}"
                                                    name="service_price[]"
                                                    class="form-control format-quantity calculate text-right"
                                                    value="{{ number_format_db($service->price) }}"/>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input
                                                        type="text"
                                                        id="service-discount-{{ $key }}"
                                                        maxlength="3"
                                                        name="service_discount[]"
                                                        class="form-control format-quantity calculate text-right"
                                                        value="{{ number_format_db($service->discount) }}"/>
                                                        <span id="span-ordinary" class="input-group-addon">%</span>
                                                </div>
                                            </td>
                                            <td>
                                                <input
                                                    type="text"
                                                    name="service_total[]"
                                                    readonly
                                                    id="service-total-{{ $key }}"
                                                    class="form-control format-quantity text-right"
                                                    value="{{ number_format_db($service->quantity * $service->price * (100 - $service->discount) / 100) }}"/>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                    {{-- <tfoot>
                                        <tr>
                                            <td colspan="8" class="text-left">
                                                <input type="button" id="addServiceRow" class="btn btn-primary" value="add Service">
                                            </td>
                                        </tr>
                                    </tfoot> --}}
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- END LIST SERVICE -->
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Item</legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="item-datatable" class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th style="width:20px"></th>
                                        <th style="min-width:220px">ITEM</th>
                                        <th style="min-width:220px">ALLOCATION</th>
                                        <th style="min-width:220px">NOTES</th>
                                        <th style="min-width:120px" class="text-right">QUANTITY</th>
                                        <th style="min-width:120px" class="text-right">PRICE</th>
                                        <th style="min-width:100px" class="text-right">DISCOUNT</th>
                                        <th style="min-width:150px" class="text-right">TOTAL</th>
                                    </tr>
                                    </thead>
                                    <tbody class="manipulate-row">
                                    @if(count(old('item_id')) > 0)
                                    @foreach( old('item_id') as $key=>$delivery_order_item)
                                    @if($delivery_order_item)
                                    <tr class="invoice-items">
                                        <td>
                                            <a href="javascript:void(0)" class="remove-row btn btn-danger pull-right">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </td>
                                        <td>
                                            <select id="item-id-{{{ $key }}}" name="item_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectItem(this.value, {{ $key }})">
                                                <option value="{{(old('item_id')[$key])}}">
                                                    <?php echo Point\Framework\Models\Master\Item::find(old('item_id')[$key])->codeName;?>
                                                </option>
                                            </select>
                                        </td>
                                        <td>
                                            <select id="allocation-id-{{ $key }}" name="allocation_id[]" class="selectize"
                                                    style="width: 100%;" data-placeholder="Choose one..">
                                                @foreach($list_allocation as $allocation)
                                                    <option @if (old('allocation_id')[$key] == $allocation->id) selected
                                                            @endif value="{{$allocation->id}}">{{$allocation->name}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="item_notes[]"
                                                    value="{{old('item_notes')[$key]}}">
                                        </td>
                                        <td class="text-right">
                                            <div class="input-group">
                                                <input id="item-quantity-{{ $key }}" type="text"
                                                        name="item_quantity[]"
                                                        class="form-control format-quantity text-right calculate"
                                                        value="{{ number_format_db(old('item_quantity')[$key]) }}"/>
                                                <span id="span-unit-{{ $key }}" class="input-group-addon">{{old('item_unit')[$key]}}</span>
                                            </div>
                                            <input type="hidden" name="item_unit[]" id="item-unit-{{ $key }}" value="{{old('item_unit')[$key]}}">
                                        </td>
                                        <td>
                                            <input type="text" id="item-price-{{ $key }}" name="item_price[]"
                                                    class="form-control format-quantity calculate text-right"
                                                    value="{{ number_format_db(old('item_price')[$key]) }}"/>
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <input type="text" id="item-discount-{{ $key }}" maxlength="3"
                                                    name="item_discount[]" class="form-control calculate text-right"
                                                    value="{{ old('item_discount')[$key] }}"
                                                />
                                                <span id="span-ordinary" class="input-group-addon">%</span>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" readonly
                                                name="item_total[]"
                                                value="{{ number_format_db(old('item_total')[$key]) }}"
                                                id="item-total-{{ $key }}"
                                                class="form-control format-quantity text-right"/>
                                        </td>
                                    </tr>
                                    @endif
                                    @endforeach
                                    @endif
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="8">
                                                <input type="button"
                                                    id="addItemRow"
                                                    class="btn btn-primary"
                                                    value="add Item">
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="item-datatable" class="table table-striped">
                                    <tr>
                                        <td style="width: 100%;" class="text-right">SUB TOTAL</td>
                                        <td>
                                            <input
                                                type="text"
                                                readonly
                                                id="subtotal"
                                                
                                                name="subtotal"
                                                class="form-control format-quantity calculate text-right"
                                                value="0"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width: 100%;" class="text-right">DISCOUNT</td>
                                        <td>
                                            <div class="input-group">
                                                <input
                                                    type="text"
                                                    id="discount"
                                                    name="discount"
                                                    maxlength="3"
                                                    class="form-control calculate text-right"
                                                    style="min-width: 100px"
                                                    value="{{ old('discount') ?: 0 }}"/>
                                                <span class="input-group-addon">%</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width: 100%;" class="text-right">TAX BASE</td>
                                        <td>
                                            <input
                                                type="text"
                                                readonly
                                                id="tax_base"
                                                name="tax_base"
                                                class="form-control format-quantity calculate text-right"
                                                value="{{ old('tax_base') ?: 0 }}"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width: 100%;" class="text-right">TAX</td>
                                        <td>
                                            <input
                                                type="text"
                                                readonly
                                                id="tax"
                                                name="tax"
                                                class="form-control format-quantity calculate text-right"
                                                value="{{ old('tax') ?: 0 }}"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width: 100%;"></td>
                                        <td>
                                            <input
                                                type="radio"
                                                id="tax-choice-include-tax"
                                                name="type_of_tax"
                                                {{ $purchase_order->type_of_tax === 'include' ? 'checked'  : '' }}
                                                onchange="calculateTotal()"
                                                value="include">
                                                Tax Included<br/>
                                            <input
                                                type="radio"
                                                id="tax-choice-exclude-tax"
                                                name="type_of_tax"
                                                {{ $purchase_order->type_of_tax === 'exclude' ? 'checked'  : '' }}
                                                onchange="calculateTotal()"
                                                value="exclude">
                                                Tax Excluded
                                            <input
                                                type="hidden"
                                                id="tax-choice-non-tax"
                                                {{ $purchase_order->type_of_tax === 'non' ? 'checked'  : '' }}
                                                name="type_of_tax"
                                                value="non">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width: 100%;" class="text-right">TOTAL</td>
                                        <td>
                                            <input
                                                type="text"
                                                readonly
                                                id="total"
                                                name="total"
                                                class="form-control format-quantity calculate text-right"
                                                value="{{ old('total') ?: 0 }}"/>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend>
                                    <i class="fa fa-angle-right"></i> PERSON IN CHARGE
                                </legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">FORM CREATOR</label>

                            <div class="col-md-6 content-show">
                                {{\Auth::user()->name}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Request Approval To</label>
                            <div class="col-md-6">
                                <select name="approval_to" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                    @foreach($list_user_approval as $user_approval)
                                        @if($user_approval->may('approval.point.purchasing.service.invoice'))
                                            <option value="{{$user_approval->id}}" {{ old('approval_to') == $user_approval->id ? 'selected' : '' }}>
                                                {{ $user_approval->name }}
                                            </option>
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
@include('framework::app.master.contact.__create', ['person_type' => 'supplier'])
@include('framework::scripts.item')
@include('framework::scripts.service')
@stop

@section('scripts')
    <script>
        //* SCRIPT ITEM DATA TABLE *//
        var item_table = initDatatable('#item-datatable');
        var counter = $(".invoice-items").length;

        $('#addItemRow').on('click', function () {
            item_table.row.add([
                '<a href="javascript:void(0)" class="remove-row btn btn-danger pull-right"><i class="fa fa-trash"></i></a>',
                '<select id="item-id-' + counter + '" name="item_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectItem(this.value, ' + counter + ')">'
                + '</select>',
                '<select id="allocation-id-' + counter + '" name="allocation_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
                @foreach($list_allocation as $allocation)
                + '<option value="{{$allocation->id}}">{{$allocation->name}}</option>'
                @endforeach
                + '</select>',
                '<input type="text" class="form-control" name="item_notes[]" placeholder="write a note">',
                '<div class="input-group"><input type="text" id="item-quantity-' + counter + '" name="item_quantity[]" class="form-control format-quantity calculate text-right" value="1" /><span id="span-unit-' + counter + '" class="input-group-addon"></span></div>'
                +'<input type="hidden" name="item_unit[]" id="item-unit-' + counter + '">',
                '<input type="text" id="item-price-' + counter + '" name="item_price[]" class="form-control format-quantity calculate text-right" value="0" />',
                '<div class="input-group"><input type="text" id="item-discount-' + counter + '" name="item_discount[]"  class="form-control calculate text-right" value="0" /><span id="span-ordinary" class="input-group-addon">%</span></div>',
                '<input type="text" readonly id="item-total-' + counter + '" name="item_total[]" class="form-control format-quantity text-right" value="0" />'
            ]).draw(false);
            
            initSelectize('#item-id-' + counter);
            initSelectize('#allocation-id-' + counter);
            reloadItem('#item-id-' + counter);
            initFormatNumber();

            $("input[type='text']").on("click", function () {
                $(this).select();
            });

            $('.calculate').keyup(function () {
                calculate();
            });

            selectItem($('#item-id-' + counter).val(), counter);
        });

        $('#item-datatable tbody').on('click', '.remove-row', function () {
            item_table.row($(this).parents('tr')).remove().draw();
            calculate();
        });

        function selectItem(item_id, counter) {
            for (var i = 0; i < counter; i++) {
                id = $('#item-id-'+i).val();
                if(id == item_id && counter != i){
                    swal("Failed", "Item is already, please choose another item");
                    selectizeInFocus('#material-id-'+counter);
                    return false;
                    break;
                    
                }
            };
            getItemUnit(item_id, '#span-unit-'+counter, 'html');
            getItemUnit(item_id, '#item-unit-'+counter, 'input');
        }

        // SCRIPT SERVICE //
        var service_table = initDatatable('#service-datatable');
        var service_counter = $(".invoice-services").length;

        // $('#addServiceRow').on('click', function () {
        //     service_table.row.add([
        //         '<a href="javascript:void(0)" class="remove-row btn btn-danger pull-right"><i class="fa fa-trash"></i></a>',
        //         '<select id="service-id-' + service_counter + '" name="service_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectService(this.value,' + service_counter + ')">'
        //         + '</select>',
        //         '<select id="service-allocation-id-' + service_counter + '" name="service_allocation_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
        //         @foreach($list_allocation as $allocation)
        //         + '<option value="{{$allocation->id}}">{{$allocation->name}}</option>'
        //         @endforeach
        //         + '</select>',
        //         '<input type="text" class="form-control" name="service_notes[]" placeholder="write a note">',
        //         '<input type="text" id="service-quantity-' + service_counter + '" name="service_quantity[]" class="form-control format-quantity calculate text-right" value="0" />',
        //         '<input type="text" id="service-price-' + service_counter + '" name="service_price[]" class="form-control format-quantity calculate text-right" value="0" />',
        //         '<div class="input-group"><input type="text" id="service-discount-' + service_counter + '" name="service_discount[]"  class="form-control calculate text-right" value="0" /><span id="span-ordinary" class="input-group-addon">%</span></div>',
        //         '<input type="text" readonly id="service-total-' + service_counter + '" name="service_total[]" class="form-control format-quantity text-right" value="0" />'
        //     ]).draw(false);
            
        //     reloadService('#service-id-' + service_counter);
        //     initSelectize('#service-id-' + service_counter);
        //     initSelectize('#service-allocation-id-' + service_counter);
        //     initFormatNumber();

        //     $("input[type='text']").on("click", function () {
        //         $(this).select();
        //     });

        //     $('.calculate').keyup(function () {
        //         calculate();
        //     });
        // });

        // $('#service-datatable tbody').on('click', '.remove-row', function () {
        //     service_table.row($(this).parents('tr')).remove().draw();
        //     calculate();
        // });

        // function selectService(service_id, service_counter) {
        //     getPrice(service_id, '#service-price-' + service_counter, 'input');
        // }

        
        // GLOBAL FUNCTION

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
            $('#tax-choice-non-tax').hide();
            calculate();
        });

        function setToNontax() {
            $("#tax-choice-include-tax").attr("checked", false);
            $("#tax-choice-exclude-tax").attr("checked", false);
            $("#tax-choice-non-tax").val("non");
            calculate();
        }

        function calculate() {
            var subtotal = 0;
            console.log("calculate", service_counter);
            for (var i = 0; i < service_counter; i++) {
                if ($('#service-discount-' + i).length != 0) {
                    if (dbNum($('#service-discount-' + i).val()) > 100) {
                        dbNum($('#service-discount-' + i).val(100))
                    }

                    var qty = dbNum($('#service-quantity-' + i).val());
                    var price = dbNum($('#service-price-' + i).val());
                    var disc = dbNum($('#service-discount-' + i).val());
                    var service_total_per_row = qty * price - ( qty * price / 100 * disc );

                    subtotal += service_total_per_row;

                    $('#service-total-' + i).val(appNum(service_total_per_row));    
                }
            }

            for (var i = 0; i < counter; i++) {
                if ($('#item-discount-' + i).length != 0) {
                    if (dbNum($('#item-discount-' + i).val()) > 100) {
                        dbNum($('#item-discount-' + i).val(100))
                    }
                    var item_total_per_row = dbNum($('#item-quantity-' + i).val()) * dbNum($('#item-price-' + i).val())
                        - ( dbNum($('#item-quantity-' + i).val()) * dbNum($('#item-price-' + i).val()) / 100 * dbNum($('#item-discount-' + i).val()) );

                    subtotal += item_total_per_row;

                    $('#item-total-' + i).val(appNum(item_total_per_row));
                }
            }

            $('#subtotal').val(appNum(subtotal));

            calculateTotal();
        }

        function calculateTotal() {
            if (dbNum($('#discount').val()) > 100) {
                dbNum($('#discount').val(100))
            }

            var subtotal = dbNum($('#subtotal').val());
            var discount = dbNum($('#discount').val());
            if ($('#tax-choice-include-tax').prop('checked')) {
                $('#discount').val(0);
                $('#discount').prop('readonly', true);
                var discount = 0;
            } else {
                $('#discount').prop('readonly', false);
            }
            
            var tax_base = subtotal - (subtotal / 100 * discount);
            $('#tax_base').val(appNum(tax_base));
            var tax = 0;

            if ($('#tax-choice-exclude-tax').prop('checked')) {
                tax = tax_base * 11 / 100;
                $("#tax-choice-non-tax").val("exclude");

            }

            if ($('#tax-choice-include-tax').prop('checked')) {
                tax_base = tax_base * 100 / 111;
                tax = tax_base * 11 / 100;
                $("#tax-choice-non-tax").val("include");

            }

            $('#tax_base').val(appNum(tax_base));
            $('#tax').val(appNum(tax));
            $('#total').val(appNum(tax_base + tax));
        }
        
        function selectizeInFocus(element) {
            $(element)[0].selectize.clear().focus();
        }

        if (service_counter > 0 || counter > 0) {
            initSelectize('#contact_id');
        }

        if (service_counter > 0) {
            for (var i=0; i < service_counter; i++) {
                if ($('#service-id-' + i).length != 0) {
                    initSelectize('#service-id-' + i);
                    initSelectize('#service-allocation-id-' + i);
                    reloadService('#service-id-' + i);
                }
            }
        }

        if (counter > 0) {
            for (var i=0; i < counter; i++) {
                if ($('#item-id-' + i).length != 0) {
                    initSelectize('#item-id-' + i);
                    initSelectize('#allocation-id-' + i);
                    reloadItem('#item-id-' + i, false);
                }
            }
        }
    </script>
@stop
