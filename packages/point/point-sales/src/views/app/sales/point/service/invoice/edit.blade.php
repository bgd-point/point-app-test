@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-sales::app.sales.point.service._breadcrumb')
            <li><a href="{{ url('sales/point/service/invoice') }}">Invoice</a></li>
            <li>Edit</li>
        </ul>
        <h2 class="sub-header">Invoice</h2>
        @include('point-sales::app.sales.point.service.invoice._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('sales/point/service/invoice/'.$invoice->id)}}" method="post"
                      class="form-horizontal form-bordered">
                    {!! csrf_field() !!}
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="person_id" value="{{$invoice->person_id}}">

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Form</legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Reason edit *</label>

                        <div class="col-md-6">
                            <input type="text" name="edit_notes" class="form-control"
                                   value="{{$invoice->formulir->approval_message}}" autofocus>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Date</label>

                        <div class="col-md-3">
                            <input type="text" name="form_date" class="form-control date input-datepicker"
                                   data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}"
                                   value="{{ date(date_format_get(), strtotime($invoice->formulir->form_date)) }}">
                        </div>
                        <div class="col-md-3">
                            <div class="input-group bootstrap-timepicker">
                                <input type="text" id="time" name="time" class="form-control timepicker">
                                <span class="input-group-btn">
                                <a href="javascript:void(0)" class="btn btn-effect-ripple btn-primary"><i
                                            class="fa fa-clock-o"></i></a>
                            </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Due Date</label>
                        <div class="col-md-3">
                            <input type="text" name="due_date" class="form-control date input-datepicker"
                                   data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}"
                                   value="{{ date(date_format_get(), strtotime($invoice->due_date)) }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Customer</label>
                        <div class="col-md-6 content-show">
                            <input type="hidden" name="customer_id" value="{{$invoice->person_id}}">
                            {!! get_url_person($invoice->person_id) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>

                        <div class="col-md-6">
                            <input type="text" name="notes" class="form-control" value="">
                        </div>
                    </div>
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
                                <!-- TABLE SERVICE -->
                                <table id="service-datatable" class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th style="width:20px"></th>
                                        <th style="min-width:220px">SERVICE</th>
                                        <th style="min-width:220px">ALLOCATION</th>
                                        <th style="min-width:220px">NOTES</th>
                                        <th style="min-width:120px">QUANTITY</th>
                                        <th style="min-width:220px">PRICE</th>
                                        <th style="min-width:100px">DISCOUNT</th>
                                        <th style="min-width:220px">TOTAL</th>
                                    </tr>
                                    </thead>
                                    <?php $service_counter  = 0; ?>
                                    <tbody class="manipulate-row">
                                    @foreach($invoice->services as $invoice_service)
                                        <tr>
                                            <td>
                                                <a href="javascript:void(0)"
                                                   class="remove-row btn btn-danger pull-right"><i
                                                            class="fa fa-trash"></i></a>
                                            </td>
                                            <td>
                                                <select id="service-id-{{$service_counter}}" name="service_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectService(this.value, {{$service_counter}})">
                                                    <option value="{{$invoice_service->service_id}}">
                                                        <?php echo Point\Framework\Models\Master\Service::find($invoice_service->service_id)->name;?>
                                                    </option>
                                                </select>
                                            </td>
                                            <td>
                                                <select id="allocation-id-{{$service_counter}}" name="allocation_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
                                                    @foreach($list_allocation as $allocation)
                                                        <option @if($invoice_service->allocation_id == $allocation->id) selected @endif value="{{$allocation->id}}">{{$allocation->name}}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" name="service_notes[]" class="form-control"
                                                       value="{{$invoice_service->service_notes}}">
                                            </td>
                                            <td class="text-right">
                                                    <input id="service-quantity-{{$service_counter}}" type="text"
                                                           name="service_quantity[]"
                                                           class="form-control format-quantity text-right calculate"
                                                           value="{{ number_format_db($invoice_service->quantity) }}"/>
                                            </td>
                                            <td><input type="text" id="service-price-{{$service_counter}}" name="service_price[]"
                                                       class="form-control format-quantity calculate text-right"
                                                       value="{{ $invoice_service->price }}"/></td>
                                            <td>
                                                <div class="input-group">
                                                    <input type="text" id="service-discount-{{$service_counter}}"
                                                           name="service_discount[]"
                                                           class="form-control format-quantity calculate text-right"
                                                           value="{{ $invoice_service->discount }}"/><span
                                                            id="span-ordinary" class="input-group-addon">%</span></div>
                                            </td>
                                            <td><input type="text" readonly id="service-total-{{$service_counter}}"
                                                       class="form-control format-quantity text-right"
                                                       value="{{ $invoice_service->quantity * $invoice_service->price - $invoice_service->quantity*$invoice_service->price/100 * $invoice_service->discount }}"/>
                                            </td>
                                        </tr>
                                        <?php $service_counter++;?>
                                    @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td class="text-left">
                                                <input type="button" id="addServiceRow" class="btn btn-primary" value="add Service">
                                            </td>
                                            <td colspan="7"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                                <!-- END TABLE SERVICE -->
                                <table id="item-datatable" class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th style="width:20px"></th>
                                        <th style="min-width:220px">ITEM</th>
                                        <th style="min-width:220px">ALLOCATION</th>
                                        <th style="min-width:220px">NOTES</th>
                                        <th style="min-width:120px">QUANTITY</th>
                                        <th style="min-width:220px">PRICE</th>
                                        <th style="min-width:100px">DISCOUNT</th>
                                        <th style="min-width:220px">TOTAL</th>
                                    </tr>
                                    </thead>
                                    <?php $counter = 0; ?>
                                    <tbody class="manipulate-row">
                                    @foreach($invoice->items as $invoice_item)
                                        <tr>
                                            <td>
                                                <a href="javascript:void(0)"
                                                   class="remove-row btn btn-danger pull-right"><i
                                                            class="fa fa-trash"></i></a>
                                            </td>
                                            <td>
                                                <select id="item-id-{{$counter}}" name="item_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectItem(this.value, {{$counter}})">
                                                    <option value="{{$invoice_item->item_id}}">
                                                        <?php echo Point\Framework\Models\Master\Item::find($invoice_item->item_id)->codeName;?>
                                                    </option>
                                                </select>
                                            </td>
                                            <td>
                                                <select id="item-allocation-id-{{$counter}}" name="item_allocation_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
                                                    @foreach($list_allocation as $allocation)
                                                        <option @if($invoice_item->allocation_id == $allocation->id) selected @endif value="{{$allocation->id}}">{{$allocation->name}}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" name="item_notes[]" class="form-control"
                                                       value="{{$invoice_item->item_notes}}">
                                            </td>
                                            <td class="text-right">
                                                <div class="input-group">
                                                    <input id="item-quantity-{{$counter}}" type="text"
                                                           name="item_quantity[]"
                                                           class="form-control format-quantity text-right calculate"
                                                           value="{{ number_format_db($invoice_item->quantity) }}"/>
                                                    <span id="span-unit-{{$counter}}" class="input-group-addon">{{ $invoice_item->unit }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" id="item-price-{{$counter}}" name="item_price[]"
                                                       class="form-control format-quantity calculate text-right"
                                                       value="{{ $invoice_item->price }}"/>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input type="text" id="item-discount-{{$counter}}"
                                                           name="item_discount[]"
                                                           class="form-control format-quantity calculate text-right"
                                                           value="{{ $invoice_item->discount }}"/><span
                                                            id="span-ordinary" class="input-group-addon">%</span></div>
                                            </td>
                                            <td>
                                                <input type="text" readonly id="item-total-{{$counter}}"
                                                       class="form-control format-quantity text-right"
                                                       value="{{ $invoice_item->quantity * $invoice_item->price - $invoice_item->quantity*$invoice_item->price/100 * $invoice_item->discount }}"/>
                                            </td>
                                        </tr>
                                        <?php $counter++;?>
                                    @endforeach
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td>
                                            <input type="button" id="addItemRow" class="btn btn-primary"
                                                   value="add Item">
                                        </td>
                                        <td colspan="7"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" class="text-right">SUB TOTAL</td>
                                        <td>
                                            <input type="text" readonly id="subtotal" onclick="setToNontax()"
                                                   class="form-control format-quantity calculate text-right" value="0"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" class="text-right">DISCOUNT</td>
                                        <td>
                                            <div class="input-group"><input type="text" id="discount" name="discount"
                                                                            class="form-control format-quantity calculate text-right"
                                                                            style="min-width: 100px"
                                                                            value="{{$invoice->discount}}"/><span
                                                        class="input-group-addon">%</span></div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" class="text-right">TAX BASE</td>
                                        <td>
                                            <input type="text" readonly id="tax_base" name="tax_base" class="form-control format-quantity calculate text-right" value="{{$invoice->tax_base}}"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" class="text-right">TAX</td>
                                        <td>
                                            <input type="text" readonly="" id="tax" name="tax" class="form-control format-quantity calculate text-right" value="{{$invoice->tax}}"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="7"></td>
                                        <td>
                                            <input type="radio" id="tax-choice-include-tax" name="type_of_tax"
                                                   {{ $invoice->type_of_tax == 'include' ? 'checked'  : '' }} onchange="calculateTotal()"
                                                   value="include"> Include Tax <br/>
                                            <input type="radio" id="tax-choice-exclude-tax" name="type_of_tax"
                                                   {{ $invoice->type_of_tax == 'exclude' ? 'checked'  : '' }} onchange="calculateTotal()"
                                                   value="exclude"> Exlude Tax <br/>
                                            <input type="hidden" id="tax-choice-non-tax" name="type_of_tax" {{ $invoice->type_of_tax == 'non' ? 'checked'  : '' }} value="non">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" class="text-right">TOTAL</td>
                                        <td>
                                            <input type="text" name="total" readonly id="total" class="form-control format-quantity calculate text-right" value="0"/>
                                        </td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> PERSON IN CHARGE</legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">FORM CREATOR</label>

                            <div class="col-md-6 content-show">
                                {{\Auth::user()->name}}
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

@include('framework::scripts.item')
@include('framework::scripts.service')
@section('scripts')
    <script>
        //* SCRIPT ITEM DATA TABLE *//
        var item_table = initDatatable('#item-datatable');
        var counter = {{$counter}} ? {{$counter}} : 0;

        $('#addItemRow').on('click', function () {
            item_table.row.add([
                '<a href="javascript:void(0)" class="remove-row btn btn-danger pull-right"><i class="fa fa-trash"></i></a>',
                '<select id="item-id-' + counter + '" name="item_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectItem(this.value, ' + counter + ')">'
                +'</select>',
                '<select id="item-allocation-id-' + counter + '" name="item_allocation_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
                @foreach($list_allocation as $allocation)
                + '<option value="{{$allocation->id}}">{{$allocation->name}}</option>'
                @endforeach
                + '</select>',
                '<input type="text" name="item_notes[]" class="form-control">',
                '<div class="input-group"><input type="text" id="item-quantity-' + counter + '" name="item_quantity[]" class="form-control format-quantity calculate text-right" value="0" /><span id="span-unit-' + counter + '" class="input-group-addon"></span></div>',
                '<input type="text" id="item-price-' + counter + '" name="item_price[]" class="form-control format-quantity calculate text-right" value="0" />',
                '<div class="input-group"><input type="text" id="item-discount-' + counter + '" name="item_discount[]"  class="form-control calculate text-right" value="0" /><span id="span-ordinary" class="input-group-addon">%</span></div>',
                '<input type="text" readonly id="item-total-' + counter + '" class="form-control format-quantity text-right" value="0" />'
            ]).draw(false);

            initSelectize('#item-id-' + counter);
            initSelectize('#item-allocation-id-' + counter);
            reloadItemHavingQuantity('#item-id-' + counter);
            initFormatNumber('.format-quantity');
            initFormatNumber('.format-price');

            $("input[type='text']").on("click", function () {
                $(this).select();
            });

            $('.calculate').keyup(function () {
                calculate();
            });

            selectItem($('#item-id-' + counter).val(), counter);
            counter++;
        });

        $('#item-datatable tbody').on('click', '.remove-row', function () {
            item_table.row($(this).parents('tr')).remove().draw();
            calculate();
        });

        // SCRIPT SERVICE //
        var service_table = initDatatable('#service-datatable');
        var service_counter = {{$service_counter}} ? {{$service_counter}} : 0;

        $('#addServiceRow').on('click', function () {
            service_table.row.add([
                '<a href="javascript:void(0)" class="remove-row btn btn-danger pull-right"><i class="fa fa-trash"></i></a>',
                '<select id="service-id-' + service_counter + '" name="service_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectService(this.value,' + service_counter + ')">'
                + '</select>',
                '<select id="allocation-id-' + service_counter + '" name="allocation_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
                @foreach($list_allocation as $allocation)
                + '<option value="{{$allocation->id}}">{{$allocation->name}}</option>'
                @endforeach
                + '</select>',
                '<input type="text" class="form-control" name="service_notes[]">',
                '<input type="text" id="service-quantity-' + service_counter + '" name="service_quantity[]" class="form-control format-quantity calculate text-right" value="1" />',
                '<input type="text" id="service-price-' + service_counter + '" name="service_price[]" class="form-control format-quantity calculate text-right" value="0" />',
                '<div class="input-group"><input type="text" id="service-discount-' + service_counter + '" name="service_discount[]"  class="form-control calculate text-right" value="0" /><span id="span-ordinary" class="input-group-addon">%</span></div>',
                '<input type="text" readonly id="service-total-' + service_counter + '" class="form-control format-quantity text-right" value="0" />'
            ]).draw(false);
            
            reloadService('#service-id-' + service_counter);
            initSelectize('#service-id-' + service_counter);
            initSelectize('#allocation-id-' + service_counter);
            initFormatNumber();

            $("input[type='text']").on("click", function () {
                $(this).select();
            });

            $('.calculate').keyup(function () {
                calculate();
            });

            service_counter++;
        });

        $('#service-datatable tbody').on('click', '.remove-row', function () {
            service_table.row($(this).parents('tr')).remove().draw();
            calculate();
        });

        function selectService(service_id, service_counter) {
            getPrice(service_id, '#service-price-' + service_counter, 'input');
        }

        
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
            var tax_status = {!! json_encode($invoice->type_of_tax) !!};
            if (tax_status == 'include') {
                $("#tax-choice-include-tax").trigger("click");
            } else if (tax_status == 'exclude') {
                $("#tax-choice-exclude-tax").trigger("click");
            } else {
                $("#tax-choice-non-tax").val("non");
            }

            calculate();
        });

        function setToNontax() {
            $("#tax-choice-include-tax").attr("checked", false);
            $("#tax-choice-exclude-tax").attr("checked", false);
            $("#tax-choice-non-tax").val("non");
            calculate();
        }

        function calculate() {
            var item_row = $("#item-datatable").dataTable().fnGetNodes().length;
            var service_row = $("#service-datatable").dataTable().fnGetNodes().length;
            var subtotal = 0;
            for (var i = 0; i < service_row; i++) {
                if (dbNum($('#service-discount-' + i).val()) >= 100) {
                    dbNum($('#service-discount-' + i).val(100))
                }

                var qty = dbNum($('#service-quantity-' + i).val());
                var price = dbNum($('#service-price-' + i).val());
                var disc = dbNum($('#service-discount-' + i).val());
                var service_total_per_row = qty * price - ( qty * price / 100 * disc );

                subtotal += service_total_per_row;

                $('#service-total-' + i).val(appNum(service_total_per_row));
            }

            for (var i = 0; i < item_row; i++) {
                if (dbNum($('#item-discount-' + i).val()) >= 100) {
                    dbNum($('#item-discount-' + i).val(100))
                }
                var item_total_per_row = dbNum($('#item-quantity-' + i).val()) * dbNum($('#item-price-' + i).val())
                    - ( dbNum($('#item-quantity-' + i).val()) * dbNum($('#item-price-' + i).val()) / 100 * dbNum($('#item-discount-' + i).val()) );

                subtotal += item_total_per_row;

                $('#item-total-' + i).val(appNum(item_total_per_row));
            }

            $('#subtotal').val(appNum(subtotal));

            calculateTotal();
        }

        function calculateTotal() {
            if (dbNum($('#discount').val()) >= 100) {
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
                tax = tax_base * 10 / 100;
                $("#tax-choice-non-tax").val("exclude");

            }

            if ($('#tax-choice-include-tax').prop('checked')) {
                tax_base = tax_base * 100 / 110;
                tax = tax_base * 10 / 100;
                $("#tax-choice-non-tax").val("include");

            }

            $('#tax_base').val(appNum(tax_base));
            $('#tax').val(appNum(tax));
            $('#total').val(appNum(tax_base + tax));
        }

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
        }

        // reload data item with ajax
        if (service_counter > 0) {
            for(var i=0; i< service_counter; i++) {
                if($('#service-id-'+i).length != 0){
                    reloadService('#service-id-' + i);
                }
            }    
        }
        // reload data item with ajax
        if (counter > 0) {
            for(var i=0; i< counter; i++) {
                if($('#item-id-'+i).length != 0){
                    reloadItemHavingQuantity('#item-id-' + i, false);
                }
            }    
        }
    </script>
@stop
