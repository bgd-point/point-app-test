@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.service._breadcrumb')
            <li>
                <a href="{{ url('purchasing/point/service/invoice') }}">Purchase Order</a>
            </li>
            <li>Edit</li>
        </ul>
        <h2 class="sub-header">Purchase Order</h2>
        @include('point-purchasing::app.purchasing.point.service.purchase-order._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form
                    action="{{url('purchasing/point/service/purchase-order/'.$purchase_order->id)}}" method="post"
                    class="form-horizontal form-bordered">
                    {!! csrf_field() !!}
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="person_id" value="{{ $person->id}}">

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
                        <label class="col-md-3 control-label">Reason edit *</label>
                        <div class="col-md-6">
                            <input
                            type="text"
                            name="edit_notes"
                            class="form-control"
                            value="{{ $purchase_order->formulir->approval_message}}"
                            autofocus>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Date</label>
                        <div class="col-md-3">
                            <input
                                type="text"
                                name="form_date"
                                class="form-control date input-datepicker"
                                data-date-format="{{date_format_masking()}}"
                                placeholder="{{date_format_masking()}}"
                                value="{{ date(date_format_get(), strtotime($purchase_order->formulir->form_date)) }}">
                        </div>
                        <div class="col-md-3">
                            <div class="input-group bootstrap-timepicker">
                                <input
                                    type="text"
                                    id="time"
                                    name="time"
                                    class="form-control timepicker">
                                <span class="input-group-btn">
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-primary">
                                        <i class="fa fa-clock-o"></i>
                                    </a>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Supplier</label>
                        <div class="col-md-6 content-show">
                            <input type="hidden" name="person_id" value="{{ $person->id}}">
                            {!! get_url_person($person->id) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>
                        <div class="col-md-6">
                            <input type="text" name="notes" class="form-control" value="{{ $purchase_order->formulir->notes }}">
                        </div>
                    </div>
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend>
                                    <i class="fa fa-angle-right"></i> Item
                                </legend>
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
                                        <th style="min-width:120px" class="text-right">QUANTITY</th>
                                        <th style="min-width:120px" class="text-right">PRICE</th>
                                        <th style="min-width:100px" class="text-right">DISCOUNT</th>
                                        <th style="min-width:150px" class="text-right">TOTAL</th>
                                    </tr>
                                    </thead>
                                    <?php $service_counter  = 0; ?>
                                    <tbody class="manipulate-row">
                                    @foreach($purchase_order->services as $purchase_order_service)
                                        <tr>
                                            <td>
                                                <a href="javascript:void(0)" class="remove-row btn btn-danger pull-right">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            </td>
                                            <td>
                                                <select
                                                    id="service-id-{{ $service_counter}}"
                                                    name="service_id[]"
                                                    class="selectize"
                                                    style="width: 100%;"
                                                    data-placeholder="Choose one.."
                                                    onchange="selectService(this.value, {{ $service_counter}})">
                                                    <option value="{{ $purchase_order_service->service_id}}">
                                                        <?php echo Point\Framework\Models\Master\Service::find($purchase_order_service->service_id)->name;?>
                                                    </option>
                                                </select>
                                            </td>
                                            <td>
                                                <select
                                                    id="service-allocation-id-{{ $service_counter}}"
                                                    name="service_allocation_id[]"
                                                    class="selectize"
                                                    style="width: 100%;"
                                                    data-placeholder="Choose one.."
                                                    onchange="selectService(this.value, {{ $service_counter}})">
                                                    @foreach($list_allocation as $allocation)
                                                    <option
                                                        value="{{ $allocation->id}}"
                                                        @if($allocation->id == $purchase_order_service->allocation_id) selected @endif >
                                                        {{ $allocation->name}}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input
                                                    type="text"
                                                    name="service_notes[]"
                                                    class="form-control"
                                                    value="{{ $purchase_order_service->service_notes}}">
                                            </td>
                                            <td class="text-right">
                                                <input
                                                    id="service-quantity-{{ $service_counter}}"
                                                    type="text"
                                                    name="service_quantity[]"
                                                    class="form-control format-quantity text-right calculate"
                                                    value="{{ number_format_db($purchase_order_service->quantity) }}"/>
                                            </td>
                                            <td>
                                                <input
                                                    type="text"
                                                    id="service-price-{{ $service_counter}}"
                                                    name="service_price[]"
                                                    class="form-control format-quantity calculate text-right"
                                                    value="{{ $purchase_order_service->price }}"/>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input
                                                        type="text"
                                                        id="service-discount-{{ $service_counter}}"
                                                        name="service_discount[]"
                                                        class="form-control format-quantity calculate text-right"
                                                        value="{{ $purchase_order_service->discount }}"/>
                                                        <span id="span-ordinary" class="input-group-addon">%</span>
                                                </div>
                                            </td>
                                            <td>
                                                <input
                                                    type="text"
                                                    readonly
                                                    id="service-total-{{ $service_counter}}"
                                                    class="form-control format-quantity text-right"
                                                    value="{{ $purchase_order_service->quantity * $purchase_order_service->price * (100 - $purchase_order_service->discount) / 100 }}"/>
                                            </td>
                                        </tr>
                                        <?php $service_counter++;?>
                                    @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td class="text-left" colspan="8">
                                                <input
                                                    type="button"
                                                    id="addServiceRow"
                                                    class="btn btn-primary"
                                                    value="add Service">
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                                <!-- END TABLE SERVICE -->
                            </div>

                            <div>
                                <div class="row form-group">
                                    <div class="text-right col-lg-10 col-sm-6">SUB TOTAL</div>
                                    <div class="col-lg-2 col-sm-6">
                                        <input
                                            type="text" readonly
                                            id="subtotal"
                                            
                                            name="subtotal"
                                            class="form-control format-quantity calculate text-right"
                                            value="{{old('subtotal')}}"/>
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <div class="text-right col-lg-10 col-sm-6">DISCOUNT</div>
                                    <div class="col-lg-2 col-sm-6">
                                        <div class="input-group">
                                            <input
                                                type="text"
                                                id="discount"
                                                name="discount"
                                                maxlength="3"
                                                class="form-control calculate text-right"
                                                style="min-width: 100px"
                                                value="0"/>
                                            <span class="input-group-addon">%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <div class="text-right col-lg-10 col-sm-6">TAX BASE</div>
                                    <div class="col-lg-2 col-sm-6">
                                        <input
                                            type="text"
                                            id="tax_base"
                                            name="tax_base"
                                            class="form-control format-quantity calculate text-right"
                                            readonly
                                            value="0"/>
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <div class="text-right col-lg-10 col-sm-6">TAX</div>
                                    <div class="col-lg-2 col-sm-6">
                                        <input
                                            type="text"
                                            readonly
                                            id="tax"
                                            name="tax"
                                            class="form-control format-quantity calculate text-right"
                                            value="0"/>
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <div class="col-lg-10 col-sm-6"></div>
                                    <div class="col-lg-2 col-sm-6">
                                        <input
                                            type="radio"
                                            id="tax-choice-include-tax"
                                            name="type_of_tax"
                                            {{ old('type_of_tax') == 'on' ? 'checked'  : '' }}
                                            onchange="calculateTotal()"
                                            value="include"> Tax Included<br/>
                                        <input
                                            type="radio"
                                            id="tax-choice-exclude-tax"
                                            name="type_of_tax"
                                            {{ old('type_of_tax') == 'on' ? 'checked'  : '' }}
                                            onchange="calculateTotal()"
                                            value="exclude"> Tax Excluded
                                        <input
                                            type="hidden"
                                            id="tax-choice-non-tax"
                                            {{ old('type_of_tax') == 'on' ? 'checked'  : '' }}
                                            name="type_of_tax"
                                            value="non">
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <div class="col-lg-10 col-sm-6 text-right">TOTAL</div>
                                    <div class="col-lg-2 col-sm-6">
                                        <input
                                            type="text"
                                            readonly
                                            id="total"
                                            name="total"
                                            class="form-control format-quantity calculate text-right"
                                            value="{{old('total')}}"/>
                                    </div>
                                </div>
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
                                <select
                                    name="approval_to"
                                    class="selectize"
                                    style="width: 100%;"
                                    data-placeholder="Choose one..">
                                    @foreach($list_user_approval as $user_approval)
                                        @if($user_approval->may('approval.point.purchasing.service.purchase.order'))
                                            <option value="{{ $user_approval->id}}" {{ old('approval_to') == $user_approval->id ? 'selected' : '' }}>
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
@stop

@include('framework::scripts.service')
@section('scripts')
    <script>
        // SCRIPT SERVICE //
        var service_table = initDatatable('#service-datatable');
        var service_counter = {{ $service_counter}} ? {{ $service_counter}} : 0;

        $('#addServiceRow').on('click', function () {
            service_table.row.add([
                '<a href="javascript:void(0)" class="remove-row btn btn-danger pull-right"><i class="fa fa-trash"></i></a>',
                '<select id="service-id-' + service_counter + '" name="service_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectService(this.value,' + service_counter + ')">'
                + '</select>',
                '<select id="service-allocation-id-' + service_counter + '" name="service_allocation_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
                @foreach($list_allocation as $allocation)
                + '<option value="{{ $allocation->id}}">{{ $allocation->name}}</option>'
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
            initSelectize('#service-allocation-id-' + service_counter);
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
            var tax_status = {!! json_encode($purchase_order->type_of_tax) !!};
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
            var subtotal = 0;
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

        // reload data item with ajax
        if (service_counter > 0) {
            for(var i=0; i< service_counter; i++) {
                if($('#service-id-'+i).length != 0){
                    reloadService('#service-id-' + i);
                }
            }    
        }
    </script>
@stop
