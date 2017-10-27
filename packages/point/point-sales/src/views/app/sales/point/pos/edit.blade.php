@extends('core::app.layout')

@section('content')
<div id="page-content">
    <h2 class="sub-header">Point of Sales</h2>

    <div class="panel panel-default">
        <div class="panel-body" id="posview">
            <form action="{{ url('sales/point/pos/create') }}" name="addToCart" id="addToCart" method="get" class="form-horizontal row">
                <div class="col-xs-12 col-md-4">
                    <img src="{{url_logo()}}" height="80px" width="auto" class="img pull-left" style="margin-left: 10px">
                    <div class="pull-left text-left v-center">
                        <div class="h4 text-primary"><strong>{{$warehouse_profiles->store_name}}</strong></div>
                        <p><b>{{$warehouse_profiles->address}}<br> {{$warehouse_profiles->phone}}</b></p>
                    </div>
                </div>

                <div class="col-xs-12 col-md-8">
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-3 control-label">Customer *</label>
                        <div class="col-xs-12 col-sm-3 col-md-9 content-show" id="content-customer">
                            @if(!$carts)
                                <div class="@if(access_is_allowed_to_view('create.customer')) input-group @endif">
                                    <select id="contact_id" name="customer_id" class="selectize" onchange="selectCustomer(this.value)" data-placeholder="Choose customer...">
                                        <option value="">-Choose Customer-</option>
                                        @foreach($list_customer as $customer)
                                            <option value="{{$customer->id}}" class="opt-{{$customer->id}}" @if(\Input::get('customer_id')==$customer->id || Point\PointSales\Helpers\PosHelper::getCustomer() == $customer->id) selected @endif>{{$customer->name}}</option>
                                        @endforeach
                                    </select>
                                    @if(access_is_allowed_to_view('create.customer'))
                                    <span class="input-group-btn">
                                        <a href="#modal-contact" class="btn btn-effect-ripple btn-primary" data-toggle="modal">
                                            <i class="fa fa-plus"></i>
                                        </a>
                                    </span>
                                    @endif
                                </div>
                            @else
                                <?php $customer_id = Point\PointSales\Helpers\PosHelper::getCustomer();?>
                                <div>{{Point\Framework\Models\Master\Person::find($customer_id)->name}}</div>
                            @endif
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-3 control-label">Warehouse</label>
                        <div class="col-xs-12 col-sm-9 col-md-9 content-show">
                            {{ $warehouse->name }}
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12 col-sm-3 col-md-3 control-label">
                            <strong>Date</strong>
                        </div>
                        <div class="col-xs-12 col-sm-9 col-md-9 content-show">
                            {{ date_format_view(date('Y-m-d'))}}
                        </div>
                    </div>
                </div>
            </form>
            <form action="{{ url('sales/point/pos/'.$pos->id) }}" method="post" class="form-horizontal row">
                {!! csrf_field() !!}
                <input name="_method" type="hidden" value="PUT">
                <input type="hidden" name="form_date" value="{{ date('Y-m-d') }}" />
                <input type="hidden" readonly name="customer_id" id="customer_id" value="{{ session('customer_id') }}">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th width="5%"></th>
                                <th width="15%">ITEM</th>
                                <th width="20%">QUANTITY</th>
                                <th width="15%" class="text-right" style="padding-right:5%">PRICE</th>
                                <th width="15%" class="text-right" style="padding-right:5%">DISCOUNT</th>
                                <th width="20%" class="text-right" style="padding-right:5%">TOTAL</th>
                            </tr>
                        </thead>
                    </table>
                    <table id="item-datatable" class="table table-striped">
                        <thead style="display:none">
                            <tr>
                                <th width="5%"></th>
                                <th width="15%"></th>
                                <th width="20%"></th>
                                <th width="15%"></th>
                                <th width="15%"></th>
                                <th width="20%"></th>
                            </tr>
                        </thead>
                        <tbody class="manipulate-row">
                            @for($i=0;$i<count($carts);$i++)
                            <?php $item = \Point\Framework\Models\Master\Item::find($carts[$i]['id']); ?>
                            <tr>
                                <td>
                                    <a id="row-item-{{$item->id}}" href="javascript:void(0)" class="remove-row btn btn-danger" data-item="{{$carts[$i]['id']}}"><i class="fa fa-trash"></i></a></td>
                                <td style="vertical-align:middle">
                                    <div style="margin-top:5px" id="item-name-{{$i}}">{{ $item->codeName }}</div>
                                    <input type="hidden" id="item-id-{{$i}}" name="item_id[]" value="{{$carts[$i]['id']}}"/>
                                </td>
                                <td>
                                    <input type="hidden" name="old_quantity[]" id="old-quantity-{{$i}}" value="{{ $carts[$i]['qty'] }}">

                                    <div class="input-group">
                                        <input name="quantity[]" id="item-quantity-{{$i}}" class="form-control format-quantity calculate text-right" value="{{ $carts[$i]['qty'] }}" type="text" onchange="updateTemp({{$i}})">
                                        <span class="input-group-addon">
                                            {{ $item->defaultUnit($carts[$i]['id'])->name }}
                                        </span>
                                    </div>
                                </td>
                                <td><input type="text" name="price[]" id="item-price-{{$i}}" class="form-control format-price text-right" readonly value="{{ $carts[$i]['price'] }}"/></td>
                                <td>
                                    <div class="input-group">
                                        <input maxlength="2" name="discount[]" id="item-discount-{{$i}}" onkeypress="isDiscount(this.value, {{$i}})" class="form-control format-quantity calculate text-right" value="{{ $carts[$i]['discount'] }}" onchange="updateTemp({{$i}})" type="text"/>
                                        <span class="input-group-addon">%</span>
                                    </div>    
                                </td>
                                <td><input type="text" name="nett[]" id="item-total-{{$i}}" class="form-control format-price text-right" readonly value="{{ $carts[$i]['price'] - $carts[$i]['discount'] }}"/></td>
                            </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
                <div class="row" style="padding:20px">
                    <table class="table table-striped" width="100%">
                        <tr>
                            <td style="width:400px">
                                <select id="item-default" name="item_default" class="selectize" onChange="validate()" data-placeholder="Choose Item">
                                </select>
                            <td>
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </table>
                </div>
                <div class="row" style="padding:0 20px 0 20px">
                    <div class="col-sm-3">

                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label class="col-md-6 control-label">SUB TOTAL</label>
                            <div class="col-md-6 content-show text-right">
                                <label id="subtotal-label">0.00</label>
                                <input type="hidden" readonly id="subtotal" name="foot_subtotal" class="form-control format-quantity calculate text-right" value="0" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-6 control-label">DISCOUNT</label>
                            <div class="col-md-6 content-show text-right">
                                <div class="input-group">
                                    <input type="text" id="discount" name="foot_discount" class="form-control format-quantity calculate text-right" value="0" />
                                    <span class="input-group-addon">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-6 control-label  content-show">TAX BASE</label>
                            <div class="col-md-6 content-show text-right">
                                <label id="tax-base-label">0.00</label>
                                <input type="hidden" readonly id="tax_base" name="foot_tax_base" class="form-control format-quantity calculate text-right" value="0" />
                            </div>
                        </div>
                        <div class="form-group content-show">
                            <label class="col-md-6 control-label">TAX</label>
                            <div class="col-md-6 content-show text-right">
                                <label id="tax-label">0.00</label>
                                <input type="hidden" readonly="" id="tax" name="foot_tax" class="form-control format-quantity calculate text-right" value="0" />
                            </div>
                        </div>
                        <div class="col-md-6"></div>
                        <div class="col-md-6 content-show">
                            <input type="checkbox" id="tax-choice-include-tax" class="tax" name="tax_type" {{ $pos->tax_type == 'include' ? 'checked'  : '' }} onchange="calculate()" value="include"> Tax Included <br/>
                            <input type="checkbox" id="tax-choice-exclude-tax" class="tax" name="tax_type" {{ $pos->tax_type == 'exclude' ? 'checked'  : '' }} onchange="calculate()" value="exclude"> Tax Excluded
                            <input type="checkbox" id="tax-choice-non-tax" class="tax" name="tax_type" {{ $pos->tax_type == 'non' ? 'checked'  : '' }} onchange="calculate()" value="non" style="display:none">
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label class="col-md-6 control-label">TOTAL ITEM</label>
                            <div class="col-md-6 content-show text-right">
                                <label id="total-item">0</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-6 control-label">TOTAL QUANTITY</label>
                            <div class="col-md-6 content-show text-right">
                                <label id="total-quantity">0</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-6 control-label">TOTAL</label>
                            <div class="col-md-6 content-show text-right">
                                <label id="total-label" style="margin-top:10px">0.00</label>
                                <input type="hidden" readonly id="total" name="foot_total" class="form-control format-quantity calculate text-right" value="0" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-6 control-label">MONEY RECEIVED</label>
                            <div class="col-md-6 content-show text-right">
                                <input type="text" id="money-received" name="foot_money_received" onkeyup="calculateChange()" class="form-control format-quantity text-right" value="0" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-6 control-label">CHANGE</label>
                            <div class="col-md-6 content-show text-right">
                                <label id="change-label" style="margin-top:10px">0.00</label>
                                <input type="hidden" readonly id="change" name="foot_change" class="form-control format-quantity calculate text-right" value="0" />
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3" style="padding-right: 30px;">
                        <a href="{{url('sales/point/pos/clear')}}" class="btn btn-lg btn-effect-ripple btn-effect-ripple btn-danger btn-block" style="padding:10px">Cancel</a>
                        <input type="submit" onclick="setAction('draft')" class="btn btn-lg btn-effect-ripple btn-effect-ripple btn-info btn-block" id="submit" value="draft" style="padding:10px"/>
                        <input type="hidden" name="action" id="action">
                        <button type="submit" onclick="setAction('save')" class="btn btn-lg btn-effect-ripple btn-effect-ripple btn-primary btn-block" id="submit" style="padding:30px 0;"><font style="font-size:20px; font-weight:bold">Close</font> <br>Transaction</button>
                        <input type="checkbox" name="print" id="print" value="true" checked="" style="visibility: hidden;"/>
                    </div>
                </div>
            </form>
        </div>
    </div>  
</div>

@include('framework::scripts.item')
@stop

@section('scripts')
<style type="text/css">
     div.dataTables_wrapper { 
        height: 200px;
        overflow-y: scroll;
        overflow-x: hidden;
    }

    .form-group {
        margin-bottom: 0;
    }

    .form-group input[type="checkbox"] {
        display: none;
    }

    .form-group input[type="checkbox"] + .btn-group > label span {
        width: 20px;
    }

    .form-group input[type="checkbox"] + .btn-group > label span:first-child {
        display: none;
    }
    .form-group input[type="checkbox"] + .btn-group > label span:last-child {
        display: inline-block;
    }

    .form-group input[type="checkbox"]:checked + .btn-group > label span:first-child {
        display: inline-block;
    }
    .form-group input[type="checkbox"]:checked + .btn-group > label span:last-child {
        display: none;
    }

   tbody.manipulate-row:after {
        content: "";
        display: block;
        height: 0; 
    }
</style>
<script>
    var item_table = initDatatable('#item-datatable');
    var counter = $("#item-datatable").dataTable().fnGetNodes().length;
    initFunctionRemoveInDatatable('#item-datatable', item_table);

    $(function() {
        $(".tax").change(function() {
            var checked = $(this).is(':checked');
            $(".tax").prop('checked',false);
            if(checked) {
                $(this).prop('checked',true);
                if ($(this).val() == 'include') {
                    $('#discount').val(0);
                    $('#discount').prop('readonly', true);
                } else {
                    $('#discount').prop('readonly', false);
                }
            } else {
                $('#tax-choice-non-tax').prop('checked', true);
            }

        });
        @if(!Session::has('customer_id'))
            $('#contact_id')[0].selectize.focus();
        @else
            $('#item-default')[0].selectize.focus();
        @endif

        calculate();       
        reloadItemHavingQuantity('#item-default');
    });
    
    $('.calculate').keyup(function(){
        calculate();
    });

    $('#item-datatable tbody').on('mouseup', '.remove-row', function () {
        $.post(
            '<?php print url('sales/point/pos/remove_item_cart') ?>',
            {item_id: $(this).attr('data-item')},
            function(data){
                if(data>0) {
                    item_table.row($(this).parents('tr')).remove().draw();
                    document.getElementById('tax-choice-non-tax').checked = true;
                }
                calculate();
        });
        
        resetItemDefault();
    });

    function calculate() {
        var rows_length = $("#item-datatable").dataTable().fnGetNodes().length;
        var subtotal = 0;
        var total_item = 0;
        var total_quantity = 0;
        for(var i=0; i<rows_length; i++) {
            var total_per_row = dbNum($('#item-quantity-'+i).val()) * dbNum($('#item-price-'+i).val()) - ( dbNum($('#item-discount-'+i).val()) / 100 * dbNum($('#item-quantity-'+i).val()) * dbNum($('#item-price-'+i).val()) );
            subtotal += total_per_row;
            $('#item-total-'+i).val(appNum(total_per_row));
            total_quantity += dbNum($('#item-quantity-'+i).val());
            total_item += 1;
        }

        $('#subtotal').val(appNum(subtotal));
        $('#subtotal-label').html(appNum(subtotal));

        var discount = dbNum($('#discount').val());
        if($('#tax-choice-include-tax').prop('checked')) {
            $('#discount').val(0);
            $('#discount').prop('readonly', true);
            var discount = 0;
        } else {
            $('#discount').prop('readonly', false);
        }
        var tax_base = subtotal - subtotal * discount / 100;
        var tax = 0;

        if($('#tax-choice-exclude-tax').prop('checked')) {
            tax = tax_base * 10 / 100;
        }

        if($('#tax-choice-include-tax').prop('checked')) {
            tax_base =  tax_base * 100 / 110;
            tax =  tax_base * 0.1;
        }

        $('#tax_base').val(appNum(tax_base));
        $('#tax').val(appNum(tax));
        $('#total').val(appNum(tax_base + tax));
        $("#money-received").val(appNum(tax_base + tax));
        $("#total-item").html(appNum(total_item)+' ITEM');
        $("#total-quantity").html(appNum(total_quantity));
        
        $('#tax-base-label').html(appNum(tax_base));
        $('#tax-label').html(appNum(tax));
        $('#total-label').html(appNum(tax_base + tax));

        calculateChange();
    }

    function updateTemp(id){
        calculate();
        $.ajax({
            url: '{{url("sales/point/pos/insert")}}',
            type: 'get',
            data: {
                id_item: $('#item-id-'+id).val(),
                qty: dbNum($('#item-quantity-'+id).val()),
                discount: dbNum($('#item-discount-'+id).val()),
                price: dbNum($('#item-price-'+id).val()),
                customer_id: $('#customer_id').val(),
                old_quantity: $('#old-quantity-'+id).val()
            },
            success: function(data) {
                $('#item-quantity-'+id).css('color', 'black');
                if (data.status_quantity == false) {
                    $('#item-quantity-'+id).css('color', 'red');
                    notification('Failed', 'Quantity column is not more than '+ data.available_stock);
                }
            },
            error: function(data) { console.log(data.status); }
        });
    }

    function addRow(result){
        var label = $("#unit_name_default").val();
        item_table.row.add( [
            '<a id="row-item-'+result.id+'" href="javascript:void(0)" class="remove-row btn btn-danger" data-item="'+result.id+'"><i class="fa fa-trash"></i></a>',
            '<div style="margin-top:5px" id="item-name-'+counter+'">'+result.item_name+'</div>'
            +'<input type="hidden" id="item-id-'+counter+'" name="item_id[]" value="'+result.id+'"/>'
            +'<input type="hidden" name="old_quantity[]" id="old-quantity-'+counter+'" value="0">',
            '<div class="input-group">'
                +'<input name="quantity[]"  value="'+result.quantity+'" id="item-quantity-'+counter+'" class="form-control format-quantity calculate text-right" value="1" type="text" onchange="updateTemp('+counter+')" autocomplete="off">'
                +'<span class="input-group-addon" id="item-unit-'+counter+'"> '+result.unit+'</span></div>',
            '<input type="text" readonly  value="'+appNum(result.price)+'" name="price[]" id="item-price-'+counter+'" class="form-control text-right" readonly/>',
            '<div class="input-group">'
                +'<input name="discount[]" maxlength="2"  value="'+result.discount+'" id="item-discount-'+counter+'" class="form-control format-quantity  calculate text-right" type="text" onchange="updateTemp('+counter+')">'
                +'<span class="input-group-addon">%</span></div>',
            '<input type="text" name="nett[]"  value="'+result.nett+'" id="item-total-'+counter+'" class="form-control format-price text-right" readonly/>',
            
        ] ).draw( false );
        counter++;
    }
    
    function validate(){
        var item = $("#item-default option:selected").val();
        var customer_id = $('#customer_id').val();
        if (item.trim() == "") {
            resetItemDefault();
        } else if (customer_id.trim()=="") {
            resetItemDefault();
            notification('Failed', 'Please, choose customer');
        } else {
            addToCart(item);
        }

        reloadItemHavingQuantity('#item-default');
        return false;
    }

    function addToCart(item_id){
        customer_id = $('#customer_id').val();
        $.ajax({
            url: "{{url('sales/point/pos/add-to-chart')}}",
            data:{
                item_id : item_id,
                customer_id : customer_id,
            },
            success:function(result){
                console.log(result);
                reloadTable(result, result.item_name);
                calculate();
            },
            error:function(result){
                console.log(result);
            }
        });
    }
    
    function reloadTable(result, item_name){
        var quantity = result.quantity;
        var temps = result.temps;
        if(result.status == "failed"){
            resetItemDefault();
            notification('Failed', result.msg);
        }
        if(temps === true){ 
            for (var i = 0; i < counter; i++) {
                if($('#item-name-'+i).length != 0){
                    var item = $("#item-name-"+i).html();
                    if( item_name === item){
                        $("#item-quantity-"+i).val(quantity);
                        break;
                    }
                }
                
            };
        }else if(temps === false){
            addRow(result);
        }
        resetItemDefault();
        reloadItemHavingQuantity('#item-default');
        $("#item-quantity-default").val("0");
        $("#discount-default").val("0");
        document.getElementById('row-item-' +result.id).scrollIntoView();
        document.getElementById('tax-choice-non-tax').checked = true;
    }

    function isDiscount(val, counter){
        if(val.length >= 2){
            $("#item-discount-"+counter).val("");
        }
    }

    function calculateChange(){
        var money_received = dbNum($("#money-received").val());
        var total = dbNum($('#total').val());

        $("#change").val(appNum(money_received - total));
        $("#change-label").html(appNum(money_received - total));
    }

    function resetItemDefault() {
        var selectize = $("#item-default")[0].selectize;
        selectize.clear();
        selectize.focus();
    }

    function setAction(action) {
        calculate();
        $('#action').val(action);
    }
</script>
@stop
