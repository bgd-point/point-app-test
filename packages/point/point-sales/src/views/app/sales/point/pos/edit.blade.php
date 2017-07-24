@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-sales::app/sales/point/pos/_breadcrumb')
        <li>Create</li>
    </ul>
    <h2 class="sub-header">Point of Sales</h2>
    @include('point-sales::app.sales.point.pos._menu')

    <div class="panel panel-default">
        <div class="panel-body">
            <form action="{{ url('sales/point/pos/create') }}" name="addToCart" id="addToCart" method="get" class="form-horizontal">
                <div class="form-group">
                    <label class="col-md-3 control-label">Customer *</label>
                    <div class="col-md-6" id="content-customer">
                        <div class="content-show">{{ $pos->customer->name }}</div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Warehouse</label>
                    <div class="col-md-9 content-show">
                        {{ $warehouse->name }}
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-3 control-label">
                        <strong>Date</strong>
                    </div>
                    <div class="col-md-9 content-show">
                        {{ date_format_view(date('Y-m-d'))}}
                    </div>
                </div>
            </form>

            <br/><br/>

            <form action="{{ url('sales/point/pos/'.$pos->id) }}" method="post" class="form-horizontal">
                {!! csrf_field() !!}
                <input name="_method" type="hidden" value="PUT">
                <input type="hidden" name="form_date" value="{{ date('Y-m-d') }}" />
                <input type="hidden" readonly name="customer_id" id="customer_id" value="{{ session('customer_id') }}">
                <div class="table-responsive">
                    <table id="item-datatable" class="table table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th width="5%"></th>
                                <th width="20%">ITEM</th>
                                <th width="15%">QUANTITY</th>
                                <th width="15%" class="text-right">PRICE</th>
                                <th width="15%" class="text-right">DISCOUNT</th>
                                <th width="15%" class="text-right">TOTAL</th>
                            </tr>
                        </thead>
                        <tbody class="manipulate-row">
                            @for($i=0;$i<count($carts);$i++)
                            <?php $item = \Point\Framework\Models\Master\Item::find($carts[$i]['id']); ?>
                            <tr >
                                <td>
                                <a href="javascript:void(0)" class="remove-row btn btn-danger" data-item="{{$carts[$i]['id']}}"><i class="fa fa-trash"></i></a></td>
                                <td>
                                    <input type="text" readonly id="item_name-{{$i}}" name="item_name[]" value="{{ $item->codeName }}" class="form-control input-item">
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
                        <tfoot>
                            <tr>
                                <td></td>
                                <td>
                                    <select id="item-default" name="item_default" class="selectize" onChange="validate()" data-placeholder="Choose Item"></select>
                                    <!-- <input type="text" id="item-default" name="item_default" value="" style="width:100%" class="form-control" placeholder="Item" autofocus></td> -->
                                <td>
                                    <div class="input-group">
                                        <input name="quantity-default" id="item-quantity-default" class="form-control format-quantity calculate text-right" value="0" type="text" readonly>
                                        <span class="input-group-addon">
                                        </span>
                                    </div>
                                </td>
                                <td><input type="text" readonly id="price-default" name="price_default" value="0" class="form-control text-right" readonly></td>
                                <td>
                                    <div class="input-group">
                                        <input type="text" name="discount-default" id="discount-default"  class="form-control format-price text-right" value="0" readonly/>
                                        <span class="input-group-addon">%</span>
                                    </div>    
                                </td>
                                <td><input type="text" readonly id="nett-default" name="nett_default" value="0" class="form-control text-right" readonly></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-right">SUB TOTAL</td>
                                <td><input type="text" readonly id="subtotal" name="foot_subtotal" class="form-control format-quantity calculate text-right" value="0" /></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-right">DISCOUNT</td>
                                <td>
                                    <div class="input-group">
                                        <input type="text" id="discount" name="foot_discount" class="form-control format-quantity calculate text-right" value="0" />
                                        <span class="input-group-addon">%</span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-right">TAX BASE</td>
                                <td><input type="text" readonly id="tax_base" name="foot_tax_base" class="form-control format-quantity calculate text-right" value="0" /></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-right">TAX</td>
                                <td><input type="text" readonly="" id="tax" name="foot_tax" class="form-control format-quantity calculate text-right" value="0" /></td>
                            </tr>
                            <tr>
                                <td colspan="5"></td>
                                <td>
                                    <input type="radio" id="tax-choice-include-tax" name="tax_type" {{ old('tax_type') == 'on' ? 'checked'  : '' }}  onchange="calculate()" value="include"> Tax Included <br/>
                                    <input type="radio" id="tax-choice-exclude-tax" name="tax_type" {{ old('tax_type') == 'on' ? 'checked'  : '' }} onchange="calculate()" value="exclude"> Tax Excluded
                                </td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-right">TOTAL</td>
                                <td><input type="text" readonly id="total" name="foot_total" class="form-control format-quantity calculate text-right" value="0" /></td>
                            </tr>
                             <tr>
                                <td colspan="5" class="text-right">MONEY RECIEVED</td>
                                <td><input type="text" id="money-received" name="foot_money_received" onkeyup="calculateChange()" class="form-control format-quantity text-right" value="0" /></td>
                            </tr>
                             <tr>
                                <td colspan="5" class="text-right">CHANGE</td>
                                <td><input type="text" readonly id="change" name="foot_change" class="form-control format-quantity calculate text-right" value="0" /></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="form-group">
                    <div class="col-sm-3">
                        <a href="{{url('sales/point/pos/clear')}}" class="btn btn-effect-ripple btn-effect-ripple btn-danger btn-block">Clear</a>
                    </div>
                    <input type="hidden" name="action" id="action">
                    <div class="col-sm-3">
                        <input type="submit" onclick="setAction('cancel')" class="btn btn-effect-ripple btn-effect-ripple btn-warning btn-block" id="submit" value="cancel" />
                        <input type="radio" id="tax-choice-non-tax" name="tax_type" {{ old('tax_type') == 'on' ? 'checked'  : '' }} checked onchange="calculate()" value="non" style="visibility: hidden;">
                    </div>
                    <div class="col-sm-3">
                        <input type="submit" onclick="setAction('draft')" class="btn btn-effect-ripple btn-effect-ripple btn-info btn-block" id="submit" value="draft" />
                    </div>
                    <div class="col-sm-3">
                        <input type="submit" onclick="setAction('save')" class="btn btn-effect-ripple btn-effect-ripple btn-primary btn-block" id="submit" value="Close Transaction" />
                    </div>
                </div>
            </form>
        </div>
    </div>  
</div>

@include('framework::scripts.item')
@stop

@section('scripts')
<script>
    var item_table = initDatatable('#item-datatable');
    var counter = $("#item-datatable").dataTable().fnGetNodes().length;
    initFunctionRemoveInDatatable('#item-datatable', item_table);

    $(function() {
        
        @if(!Session::has('customer_id'))
            $('#contact_id')[0].selectize.focus();
        @else
            $('#item-default')[0].selectize.focus();
        @endif

        document.getElementById('tax-choice-non-tax').checked = true;
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
        for(var i=0; i<rows_length; i++) {
            var total_per_row = dbNum($('#item-quantity-'+i).val()) * dbNum($('#item-price-'+i).val()) - ( dbNum($('#item-discount-'+i).val()) / 100 * dbNum($('#item-quantity-'+i).val()) * dbNum($('#item-price-'+i).val()) );
            subtotal += total_per_row;
            $('#item-total-'+i).val(appNum(total_per_row));
        }

        $('#subtotal').val(appNum(subtotal));

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
            '<a href="javascript:void(0)" class="remove-row btn btn-danger" data-item="'+result.id+'"><i class="fa fa-trash"></i></a>',
            '<input type="text" value="'+result.item_name+'" readonly name="item_name[]" id="item_name-'+counter+'" class="form-control input-item" placeholder="Search Item..." autofocus />'
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
        reloadItemHavingQuantity('#item-default');
        var item = $("#item-default option:selected").val();
        var customer_id = $('#customer_id').val();
        if (item.trim() == "") {
            resetItemDefault();
            notification('Failed', 'Please, select item');
        } else if (customer_id.trim()=="") {
            resetItemDefault();
            notification('Failed', 'Please, choose customer');
        } else {
            addToCart(item);
        }

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
                if($('#item_name-'+i).length != 0){
                    var item = $("#item_name-"+i).val();
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
