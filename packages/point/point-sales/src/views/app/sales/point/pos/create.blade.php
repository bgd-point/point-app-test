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
                        @if(!Session::has('customer_id'))
                            <div class="@if(access_is_allowed_to_view('create.customer')) input-group @endif">
                                <select id="contact_id" name="customer_id" class="selectize" onchange="selectCustomer(this.value)" data-placeholder="Choose customer...">
                                        <option value="">-Choose Customer-</option>
                                    @foreach($list_customer as $customer)
                                        <option value="{{$customer->id}}" class="opt-{{$customer->id}}" @if(\Input::get('customer_id')==$customer->id || session('customer_id') == $customer->id) selected @endif>{{$customer->name}}</option>
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
                            <div class="content-show">{{Point\Framework\Models\Master\Person::find(session('customer_id'))->name}}</div>
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
            </form>

            <br/><br/>

            <form action="{{ url('sales/point/pos') }}" method="post" class="form-horizontal">
                {!! csrf_field() !!}
                <input type="hidden" name="form_date" value="{{ date('d-m-y', time()) }}" />
                <input type="hidden" readonly name="input_customer" id="input_customer" value="{{ session('customer_id') }}">
                <div class="table-responsive">
                    <table id="item-datatable" class="table table-striped" min-width="1280px">
                        <thead>
                            <tr>
                                <th style="width:50px"></th>
                                <th style="min-width:300px">ITEM</th>
                                <th style="min-width:110px">QUANTITY</th>
                                <th style="min-width:110px" class="text-right">PRICE</th>
                                <th style="min-width:110px" class="text-right">DISCOUNT</th>
                                <th style="min-width:110px" class="text-right">TOTAL</th>
                            </tr>
                        </thead>
                        <tbody class="manipulate-row">
                            @for($i=0;$i<count($carts);$i++)
                            <?php $item = \Point\Framework\Models\Master\Item::find($carts[$i]['id']); ?>
                            <tr >
                                <td>
                                <a href="javascript:void(0)" class="remove-row btn btn-danger" data-item="{{$carts[$i]['id']}}"><i class="fa fa-trash"></i></a></td>
                                <td>
                                    <input type="text" readonly id="item_name-{{$i}}" name="item_name[]" value="{{ $item->name }}" class="form-control input-item">
                                    <input type="hidden" id="item-id-{{$i}}" name="item_id[]" value="{{$carts[$i]['id']}}"/>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <input name="quantity[]" id="item-quantity-{{$i}}" class="form-control format-quantity calculate text-right" value="{{ $carts[$i]['qty'] }}" type="text" onchange="updateTemp({{$i}})">
                                        <span class="input-group-addon">
                                            <?php
                                                $item = \Point\Framework\Models\Master\ItemUnit::where('item_id', $carts[$i]['id'])
                                                ->where('converter', 1)->first();
                                                print $item->name;
                                            ?>
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
                                <td><strong></strong></td>
                                <td>
                                    <input type="text" id="item-default" name="item_default" value="" style="width:100%" class="form-control" placeholder="Item" autofocus></td>
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
                    <div class="col-sm-6"><input type="radio" id="tax-choice-non-tax" name="tax_type" {{ old('tax_type') == 'on' ? 'checked'  : '' }} checked onchange="calculate()" value="non" style="visibility: hidden;"></div>
                    <div class="col-sm-3">
                        <a href="{{url('sales/point/pos/clear')}}" class="btn btn-effect-ripple btn-effect-ripple btn-danger btn-block">Cancel</a>
                    </div>
                    <div class="col-sm-3">
                        <input type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary btn-block" id="submit" value="save" />
                    </div>
                </div>
            </form>
        </div>
    </div>  
</div>

@include('framework::app.master.contact.__create', ['person_type' => 'customer'])
@stop

@section('scripts')
<style>
    tbody.manipulate-row:after {
      content: '';
      display: block;
  }

.twitter-typeahead{
    width: 100%
}
.tt-query {
  -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
     -moz-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
          box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
}

.tt-hint {
  color: #999
}

.tt-menu {    /* used to be tt-dropdown-menu in older versions */
  width: 100%;
  margin-top: 4px;
  padding: 4px 0;
  background-color: #fff;
  border: 1px solid #ccc;
  border: 1px solid rgba(0, 0, 0, 0.2);
  -webkit-border-radius: 4px;
     -moz-border-radius: 4px;
          border-radius: 4px;
  -webkit-box-shadow: 0 5px 10px rgba(0,0,0,.2);
     -moz-box-shadow: 0 5px 10px rgba(0,0,0,.2);
          box-shadow: 0 5px 10px rgba(0,0,0,.2);
}

.tt-suggestion {
  padding: 3px 20px;
  line-height: 24px;
}

.tt-suggestion.tt-cursor,.tt-suggestion:hover {
  color: #fff;
  background-color: #0097cf;

}

.tt-suggestion p {
  margin: 0;
}
</style>
<script>
    var list_item = <?php echo json_encode($items);?>;
    // constructs the suggestion engine
    var engine = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        // `states` is an array of state names defined in "The Basics"
        local: $.map(list_item, function(state) { return { value: state }; })
    });

    engine.initialize();
    $('#item-default').typeahead({
        hint: true,
        highlight: true,
        minLength: 1
    },
    {
        name: 'list_item',
        displayKey: 'value',
        source: engine.ttAdapter()
    });
    
    var item_table = $('#item-datatable').DataTable({
            bSort: false,
            bPaginate: false,
            bInfo: false,
            bFilter: false,
            bScrollCollapse: false,
            scrollX: true
    });
    
    $(function() {
        document.getElementById('tax-choice-non-tax').checked = true;
        calculate();       
    });
    
    var counter = $("#item-datatable").dataTable().fnGetNodes().length;
    
    $(document).on("keypress", 'form', function (e) {
        var code = e.keyCode || e.which;
        if (code == 13) {
            e.preventDefault();
            return false;
        }
    });

    $('.calculate').keyup(function(){
        calculate();
    });

    $("#item-default").keyup(function(event){
        if(event.keyCode == 13){
            event.preventDefault();
            validate();
        }
        
    });

    $('#item-datatable tbody').on('mouseup', '.remove-row', function () {
        $.post(
            '<?php print url('sales/point/pos/remove_item_cart') ?>',
            {item_id: $(this).attr('data-item')},
            function(data){
                if(data>0) {
                    item_table.row($(this).parents('tr')).remove().draw();
                    calculate();
                    document.getElementById('tax-choice-non-tax').checked = true;
                }
        });
        
        $("#item-default").focus();
    });

    $('#item-datatable tbody').on( 'mouseup', '.remove-row', function () {
        item_table.row( $(this).parents('tr') ).remove().draw();
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
                price: dbNum($('#item-price-'+id).val())
            },
            success: function(data) { console.log(data.status); },
            error: function(data) { console.log(data.status); }
        });
    }

    function selectCustomer(value){
        customer = $(".item").html();
        html = "<div class='content-show'>"+customer+"</div>";
        $("#content-customer").html(html);
        $("#input_customer").val(value);
        $("#item-default").focus();
    }

    function addRow(result){
        var label = $("#unit_name_default").val();
        item_table.row.add( [
            '<a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a>',
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
        var item = $("#item-default").val();
        var customer_id = $('#input_customer').val();
        if(item.trim() == ""){
            swal('Failed', 'Please, fill all provided column');
            $("#item-default").focus();
        }else if(customer_id.trim()==""){
            swal('Failed', 'Please, choose customer');
        }else{
            addToCart(item);
        }
    }

    function addToCart(item){
        customer_id = $('#input_customer').val();
        $.ajax({
            url: "{{url('sales/point/pos/add-to-chart')}}",
            data:{
                item_name : item,
                customer_id : customer_id,
            },
            success:function(result){
                console.log(result);
                reloadTable(result, item);
                calculate();
            },
            error:function(result){
                console.log(result);
            }
        });
    }
    
    function reloadTable(result, viewItem){
        var quantity = result.quantity;
        var temps = result.temps;
        if(result.status == "failed"){
            swal('Failed', result.msg);
        }
        if(temps === true){ 
            for (var i = 0; i < counter; i++) {
                if($('#item_name-'+i).length != 0){
                    var item = $("#item_name-"+i).val();
                    if( viewItem === item){
                        $("#item-quantity-"+i).val(quantity);
                        break;
                    }
                }
                
            };
        }else if(temps === false){
            addRow(result);
        }
        $("#item-default").val("");
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
</script>
@stop
