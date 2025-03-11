@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.service._breadcrumb')
            <li>
                <a href="{{ url('purchasing/point/service/purchase-order') }}">Purchase Order</a>
            </li>
            <li>Create</li>
        </ul>
        <h2 class="sub-header">Purchase Order</h2>
        @include('point-purchasing::app.purchasing.point.service.purchase-order._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('purchasing/point/service/purchase-order')}}"
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
                                name="form_date"
                                class="form-control date input-datepicker"
                                data-date-format="{{date_format_masking()}}"
                                placeholder="{{date_format_masking()}}"
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
                        <label class="col-md-3 control-label">Supplier</label>

                        <div class="col-md-6">
                            <div class="@if(access_is_allowed_to_view('create.supplier')) input-group @endif">
                                <select id="contact_id" name="person_id" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                    <option></option>
                                    @foreach($list_person as $person)
                                        <option value="{{$person->id}}" @if(old('person_id') == $person->id) selected @endif>{{$person->codeName}}</option>
                                    @endforeach
                                </select>
                                @if(access_is_allowed_to_view('create.supplier'))
                                <span class="input-group-btn">
                                    <a href="#modal-contact" class="btn btn-effect-ripple btn-primary" data-toggle="modal">
                                        <i class="fa fa-plus"></i>
                                    </a>
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>

                        <div class="col-md-6">
                            <input type="text" name="notes" class="form-control" value="">
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
                                    <tbody class="manipulate-row">
                                    <?php $service_counter = 0; ?>

                                    @if(count(old('service_id')) > 0)
                                        @foreach( old('service_id') as $service_id)
                                            @if($service_id)
                                            <tr>
                                                <td>
                                                    <a
                                                        href="javascript:void(0)"
                                                        class="remove-row btn btn-danger pull-right">
                                                        <i class="fa fa-trash"></i>
                                                    </a>
                                                </td>
                                                <td>
                                                    <?php $service = Point\Framework\Models\Master\Service::find(old('service_id')[$service_counter])?>
                                                    <select
                                                        id="service-id-{{$service_counter}}"
                                                        name="service_id[]"
                                                        class="selectize"
                                                        style="width: 100%;"
                                                        data-placeholder="Choose one.."
                                                        onchange="selectService(this.value, {{$service_counter}})">
                                                        <option value="{{(old('service_id')[$service_counter])}}">
                                                            {{ $service ? $service->name : ''}}
                                                        </option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select
                                                        id="service-allocation-id-{{$service_counter}}"
                                                        name="service_allocation_id[]"
                                                        class="selectize"
                                                            style="width: 100%;" data-placeholder="Choose one..">
                                                        @foreach($list_allocation as $allocation)
                                                            <option @if (old('service_allocation_id')[$service_counter] == $allocation->id) selected
                                                                    @endif value="{{$allocation->id}}">{{$allocation->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <input
                                                        type="text"
                                                        class="form-control"
                                                        name="service_notes[]"
                                                        value="{{old('service_notes')[$service_counter]}}">
                                                </td>
                                                <td class="text-right">
                                                    <input
                                                        id="service-quantity-{{$service_counter}}"
                                                        type="text"
                                                        name="service_quantity[]"
                                                        class="form-control format-quantity text-right calculate"
                                                        value="{{ number_format_db(old('service_quantity')[$service_counter]) }}"/>
                                                </td>
                                                <td>
                                                    <input
                                                        type="text"
                                                        id="service-price-{{$service_counter}}"
                                                        name="service_price[]"
                                                        class="form-control format-quantity calculate text-right"
                                                        value="{{ number_format_db(old('service_price')[$service_counter]) }}"/>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input 
                                                            type="text"
                                                            id="service-discount-{{$service_counter}}"
                                                            maxlength="3"
                                                            name="service_discount[]"
                                                            class="form-control calculate text-right"
                                                            value="{{ old('service_discount')[$service_counter] }}"/>
                                                            <span id="span-ordinary" class="input-group-addon">%</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <input
                                                        id="service-total-{{$service_counter}}"
                                                        type="text"
                                                        name="service_total[]"
                                                        readonly
                                                        class="form-control format-quantity text-right"
                                                        value="{{number_format_db(old('service_total')[$service_counter])}}"/>
                                                </td>
                                            </tr>
                                            <?php $service_counter++;?>
                                            @endif
                                        @endforeach
                                    @endif
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td class="text-left" colspan="8">
                                                <input type="button" id="addServiceRow" class="btn btn-primary" value="add Service">
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- END LIST SERVICE -->

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
        // SCRIPT SERVICE //
        var service_table = initDatatable('#service-datatable');
        var service_counter = {{$service_counter}} ? {{$service_counter}} : 0;

        $('#addServiceRow').on('click', function () {
            service_table.row.add([
                '<a href="javascript:void(0)" class="remove-row btn btn-danger pull-right"><i class="fa fa-trash"></i></a>',
                '<select id="service-id-' + service_counter + '" name="service_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectService(this.value,' + service_counter + ')">'
                + '</select>',
                '<select id="service-allocation-id-' + service_counter + '" name="service_allocation_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
                @foreach($list_allocation as $allocation)
                + '<option value="{{$allocation->id}}">{{$allocation->name}}</option>'
                @endforeach
                + '</select>',
                '<input type="text" class="form-control" name="service_notes[]" placeholder="write a note">',
                '<input type="text" id="service-quantity-' + service_counter + '" name="service_quantity[]" class="form-control format-quantity calculate text-right" value="0" />',
                '<input type="text" id="service-price-' + service_counter + '" name="service_price[]" class="form-control format-quantity calculate text-right" value="0" />',
                '<div class="input-group"><input type="text" id="service-discount-' + service_counter + '" name="service_discount[]"  class="form-control calculate text-right" value="0" /><span id="span-ordinary" class="input-group-addon">%</span></div>',
                '<input type="text" readonly id="service-total-' + service_counter + '" name="service_total[]" class="form-control format-quantity text-right" value="0" />'
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
        
        function selectizeInFocus(element) {
            $(element)[0].selectize.clear().focus();
        }

        if (service_counter > 0) {
            initSelectize('#contact_id');
            for (var i=0; i < service_counter; i++) {
                if ($('#service-id-' + i).length != 0) {
                    initSelectize('#service-id-' + i);
                    initSelectize('#service-allocation-id-' + i);
                    reloadService('#service-id-' + i);
                }
            }
        }
    </script>
@stop
