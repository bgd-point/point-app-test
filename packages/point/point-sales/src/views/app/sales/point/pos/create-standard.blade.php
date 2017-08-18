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
            <form action="{{ url('sales/point/pos/create') }}" name="addToCart" id="addToCart" method="get" class="form-horizontal row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Customer *</label>
                        <div class="col-md-6" id="content-customer">
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
                                <div class="content-show">{{Point\Framework\Models\Master\Person::find($customer_id)->name}}</div>
                            @endif
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
                </div>
                <div class="col-md-6">
                    <img src="{{url_logo()}}" height="150px" width="auto" class="img pull-right" style="border:3px dotted #000; margin-left: 10px">
                    <div class="pull-right text-right v-center">
                        <div class="h3 text-primary"><strong>{{$warehouse_profiles->store_name}}</strong></div>
                        <p><b>{{$warehouse_profiles->address}}<br> {{$warehouse_profiles->phone}}</b></p>
                    </div>

                </div>
            </form>

            <br/><br/>

            <form action="{{ url('sales/point/pos') }}" method="post" class="form-horizontal row">
                {!! csrf_field() !!}
                <input type="hidden" name="form_date" value="{{ date('d-m-y', time()) }}" />
                <input type="hidden" readonly name="input_customer" id="input_customer" value="{{ Point\PointSales\Helpers\PosHelper::getCustomer() }}">
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
                    <table class="table table-striped">
                        <tr>
                            <td></td>
                            <td>
                                <select id="item-default" name="item_default" class="selectize" onChange="validate()" data-placeholder="Choose Item">
                                </select>
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
                    </table>
                </div>
                <div class="row" style="padding:20px">
                    <div class="col-sm-3">
                        <input type="radio" id="tax-choice-include-tax" name="tax_type" {{ old('tax_type') == 'on' ? 'checked'  : '' }}  onchange="calculate()" value="include"> Tax Included <br/>
                        <input type="radio" id="tax-choice-exclude-tax" name="tax_type" {{ old('tax_type') == 'on' ? 'checked'  : '' }} onchange="calculate()" value="exclude"> Tax Excluded
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <div class="col-md-6 control-label  content-show">
                                <label>SUB TOTAL</label>
                            </div>
                            <div class="col-md-6 content-show">
                                <input type="text" readonly id="subtotal" name="foot_subtotal" class="form-control format-quantity calculate text-right" value="0" />
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-6 control-label  content-show">
                                <label>DISCOUNT</label>
                            </div>
                            <div class="col-md-6 content-show">
                                <div class="input-group">
                                    <input type="text" id="discount" name="foot_discount" class="form-control format-quantity calculate text-right" value="0" />
                                    <span class="input-group-addon">%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <div class="col-md-6 control-label  content-show">
                                <label>TOTAL ITEM</label>
                            </div>
                            <div class="col-md-6 content-show text-right">
                                <label id="total-item">0</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-6 control-label  content-show">
                                <label>TOTAL QUANTITY</label>
                            </div>
                            <div class="col-md-6 content-show text-right">
                                <label id="total-quantity">0</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <input type="checkbox" name="print" value="true" checked=""><label>Print</label>
                    </div>
                </div>
                <div class="row" style="padding:20px">
                    <div class="col-sm-3">
                        <a href="{{url('sales/point/pos/clear')}}" class="btn btn-lg btn-effect-ripple btn-effect-ripple btn-danger btn-block">Cancel</a>
                        <input type="submit" onclick="setAction('draft')" class="btn btn-lg btn-effect-ripple btn-effect-ripple btn-info btn-block" id="submit" value="draft" />
                        <input type="radio" id="tax-choice-non-tax" name="tax_type" {{ old('tax_type') == 'on' ? 'checked'  : '' }} checked onchange="calculate()" value="non" style="visibility: hidden;">
                        <input type="hidden" name="action" id="action">
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <div class="col-md-6 control-label  content-show">
                                <label>TAX BASE</label>
                            </div>
                            <div class="col-md-6 content-show">
                                <input type="text" readonly id="tax_base" name="foot_tax_base" class="form-control format-quantity calculate text-right" value="0" />
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-6 control-label  content-show">
                                <label>TAX</label>
                            </div>
                            <div class="col-md-6 content-show">
                                <input type="text" readonly="" id="tax" name="foot_tax" class="form-control format-quantity calculate text-right" value="0" />
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <div class="col-md-6 control-label  content-show">
                                <label>TOTAL</label>
                            </div>
                            <div class="col-md-6 content-show">
                                <input type="text" readonly id="total" name="foot_total" class="form-control format-quantity calculate text-right" value="0" />
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-6 control-label  content-show">
                                <label>MONEY RECEIVED</label>
                            </div>
                            <div class="col-md-6 content-show">
                                <input type="text" id="money-received" name="foot_money_received" onkeyup="calculateChange()" class="form-control format-quantity text-right" value="0" />
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-6 control-label  content-show">
                                <label>CHANGE</label>
                            </div>
                            <div class="col-md-6 content-show">
                                <input type="text" readonly id="change" name="foot_change" class="form-control format-quantity calculate text-right" value="0" />
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <button type="submit" onclick="setAction('save')" class="btn btn-lg btn-effect-ripple btn-effect-ripple btn-primary btn-block" id="submit" style="padding:50px"><font size="20">Close</font> <br>Transaction</button>
                    </div>
                </div>
            </form>
        </div>
    </div>  
</div>

@include('framework::app.master.contact.__create', ['person_type' => 'customer'])
@include('framework::scripts.item')

@stop

@section('scripts')
<style type="text/css">
    div.dataTables_wrapper { height: 300px; }
</style>
<script>

    var item_table = initDatatable('#item-datatable');
    var counter = $("#item-datatable").dataTable().fnGetNodes().length;
    initFunctionRemoveInDatatable('#item-datatable', item_table);

    $(function() {
        
        @if(!Point\PointSales\Helpers\PosHelper::getCustomer())
            $('#contact_id')[0].selectize.focus();
        @else
            $('#item-default')[0].selectize.focus();
        @endif

        document.getElementById('tax-choice-non-tax').checked = true;
        calculate();  
        populateJsonItem();
        reloadItem('#item-default');

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
                    calculate();
                }
        });

        resetItemDefault();
    });

    function calculate() {
        var subtotal = 0;
        var total_item = 0;
        var total_quantity = 0;
        for(var i=0; i<counter; i++) {
            if($('#item-quantity-'+i).length != 0){
                var total_per_row = dbNum($('#item-quantity-'+i).val()) * dbNum($('#item-price-'+i).val()) - ( dbNum($('#item-discount-'+i).val()) / 100 * dbNum($('#item-quantity-'+i).val()) * dbNum($('#item-price-'+i).val()) );
                subtotal += total_per_row;
                $('#item-total-'+i).val(appNum(total_per_row));
                total_quantity += dbNum($('#item-quantity-'+i).val());
                total_item += 1;
            }
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
        $("#total-item").html(appNum(total_item)+' ITEM');
        $("#total-quantity").html(appNum(total_quantity));
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
                customer_id: dbNum($('#input_customer').val()),
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

    function selectCustomer(value){
        var customer = $("#contact_id option:selected").text();
        html = "<div class='content-show'>"+customer+"</div>";
        $("#content-customer").html(html);
        $("#input_customer").val(value);
        resetItemDefault();
    }

    function addRow(result){
        var label = $("#unit_name_default").val();
        item_table.row.add( [
            '<a href="javascript:void(0)" class="remove-row btn btn-danger" data-item="'+result.id+'"><i class="fa fa-trash"></i></a>',
            '<input type="text" value="'+result.item_name+'" readonly name="item_name[]" id="item_name-'+counter+'" class="form-control input-item" placeholder="Search Item..." autofocus />'
            +'<input type="hidden" id="item-id-'+counter+'" name="item_id[]" value="'+result.id+'"/>',
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
        var customer_id = $('#input_customer').val();
        if(item.trim() == ""){
            resetItemDefault();
        }else if(customer_id.trim()==""){
            resetItemDefault();
            notification('Failed', 'Please, choose customer');
        }else{
            addToCart(item);
        }
        reloadItem('#item-default');
    }

    function addToCart(item_id){
        customer_id = $('#input_customer').val();
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
        $("#item-quantity-default").val("0");
        $("#discount-default").val("0");
        document.getElementById('tax-choice-non-tax').checked = true;
        reloadItem('#item-default');
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
