<div class="table-responsive">
    <table id="item-datatable" class="table table-striped">
        <thead>
            <tr>
                <th style="minwidth:20px"></th>
                <th style="min-width:250px">Subledger</th>
                <th style="min-width:30px">Warehouse</th>
                <th style="min-width:150px">Stock In Database</th>
                <th style="min-width:150px">Stock</th>
                <th style="min-width:50px">Notes</th>
                <th style="min-width:50px">Amount</th>
            </tr>
        </thead>
        <tbody class="manipulate-row">
            @for($i=0;$i<count($details);$i++)
                <tr>
                    <td><a href="javascript:void(0)" class="remove-row btn btn-danger" onclick="removeItem({{$details[$i]['rowid']}})" ><i class="fa fa-trash"></i></a></td>
                    <td>
                        <select id="item-{{$i}}" name="item_id[]" class="selectize initSelectize" style="width:100%;" data-placeholder="Choose one..">
                            <option value="{{$details[$i]['item_id']}}"><?php echo $class::find($details[$i]['item_id'])->codeName;?></option>
                        </select>
                    </td>
                    <td>
                        <select id="warehouse-{{$i}}" name="warehouse_id[]" class="selectize initSelectize" style="width:100%;" data-placeholder="Choose one.." onchange="reloadIdentity({{$i}})">
                            <option value="{{$details[$i]['warehouse_id']}}"><?php echo Point\Framework\Models\Master\Warehouse::find($details[$i]['warehouse_id'])->name;?></option>
                        </select>
                    </td>
                    <td>
                        <div class="input-group">
                            <input type="text" id="stok-in-db-{{$i}}" name="stock_in_db[]" class="form-control text-right format-quantity" value="{{$details[$i]['stock_in_db']}}"  readonly/>
                            <span class="input-group-addon unit-{{$i}}">{{$details[$i]['unit1']}}</span>
                            <input type="hidden" name="unit1[]" class="input-unit-{{$i}}" value="{{$details[$i]['unit1']}}">
                        </div>
                    </td>
                    <td>
                        <div class="input-group">
                            <input type="text" id="stock-{{$i}}" name="stock[]" class="form-control text-right format-quantity" value="{{$details[$i]['stock']}}" />
                            <span class="input-group-addon unit-{{$i}}">{{$details[$i]['unit2']}}</span>
                            <input type="hidden" style="min-width:50px" name="unit2[]" class="input-unit-{{$i}}" value="{{$details[$i]['unit2']}}">
                        </div>
                    </td>
                    <td><input type="text" name="notes[]" value="{{$details[$i]['notes']}}" class="form-control" ></td>
                    <td><input type="text" name="amount[]" id="amount-{{$i}}" onkeyup="calculate()" class="form-control text-right format-quantity" value="{{$details[$i]['amount']}}" /></td>
                </tr>
            @endfor
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2"><input type="button" class="btn btn-primary" onclick="validateRow()" value="Add Subledger"></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td><input type="text" style="width:100%; font-weight:bold" readonly id="total" class="form-control text-right"></td>
            </tr>
        </tfoot>
    </table>
</div>

@include('framework::scripts.item')
<script>
var counter = {{count($details)}};
var datatable = initDatatable('#item-datatable');
initFunctionRemoveInDatatable('#item-datatable', datatable);
initSelectize('.initSelectize');

function validateRow() {
    for (var i = 0; i <= counter; i++) {
        if($('#master-'+i).length != 0){
            if(! $('#master-'+i).val()){
                swal("Please, select the subledger");
                selectizeInFocus('#master-'+i);
                return false;
                break;
            }

        }
    };
    addRow();
}

function addRow() {
    datatable.row.add( [
        '<a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a>',
        '<select id="item-'+counter+'" name="item_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
            +'<option ></option>'
        +'</select>',
        '<select id="warehouse-'+counter+'" name="warehouse_id[]" class="selectize" style="width: 100%;" onchange="reloadIdentity('+counter+')" data-placeholder="Choose one..">'
            +'<option ></option>'
        +'</select>',
        '<div class="input-group">'
            +'<input type="text" id="stock-in-db-'+counter+'" name="stock_in_db[]" class="form-control text-right format-quantity" value=0  readonly/>'
            +'<span class="input-group-addon unit-'+counter+'"></span>'
            +'<input type="hidden" name="unit1[]" class="input-unit-'+counter+'">'
            
        +'</div>',
        '<div class="input-group">'
            +'<input type="text" id="stock-'+counter+'" name="stock[]" class="form-control text-right format-quantity text-right" value=0 />'
            +'<span class="input-group-addon unit-'+counter+'"></span>'
            +'<input type="hidden" name="unit2[]" class="input-unit-'+counter+'">'
        +'</div>',
        '<input type="text" name="notes[]" id="notes-'+counter+'" class="form-control" />',
        '<input type="text" name="amount[]" id="amount-'+counter+'" onkeyup="calculate()" class="form-control format-quantity text-right" value="0" />',
    ] ).draw( false );

    initFormatNumber();
    initSelectize('#item-'+counter);
    initSelectize('#warehouse-'+counter);
    reloadItemInSelectize("#item-"+counter);
    reloadWarehouseInSelectize("#warehouse-"+counter);
    counter++;
}

function reloadIdentity(counter) {
    var item_id = $("#item-"+counter).val();
    getItemUnit(item_id, ".unit-"+counter, "html");
    getItemUnit(item_id, ".input-unit-"+counter, "input");
    getItemQuantity(item_id, $('#warehouse-'+counter).val(), $("#form_date").val(), $("#time").val(), "#stock-in-db-"+counter, 'input');
    $("#stock-"+counter).focus();
}

function reloadItemInSelectize(item_id) {
    var url = "{{URL::to('master/item/list')}}";

    $.ajax({
        url: url,
        success: function(data) {
            var item = $(item_id)[0].selectize;
            item.clear();
            item.load(function(callback) {
                callback(eval(JSON.stringify(data.lists)));
            });
        }
    });
}

function reloadWarehouseInSelectize(warehouse_id) {
    var url = "{{URL::to('master/warehouse/list')}}";

    $.ajax({
        url: url,
        success: function(data) {
            var warehouse = $(warehouse_id)[0].selectize;
            warehouse.clear();
            warehouse.load(function(callback) {
                callback(eval(JSON.stringify(data.lists)));
            });
        }
    });
}

function calculate() {
    var rows_length = $("#item-datatable").dataTable().fnGetNodes().length;
    var subtotal = 0;
    for(var i=0; i<rows_length; i++) {
        var total_per_row = dbNum($('#amount-'+i).val());
        subtotal += total_per_row;
    }

    $('#total').val(accountingNum(subtotal));
}

function initSelectizeMaster() {
    var url = "{{URL::to('master/item/list')}}";
    $.ajax({
        url: url,
        success: function(data) {
            for(var i=0; i<{{count($details)}}; i++) {
                if($('#item-'+i).length != 0){
                    var item = $("#item-"+i)[0].selectize;
                    item.load(function(callback) {
                        callback(eval(JSON.stringify(data.lists)));
                    });
                }
            }
            
        }
    });
}

@if(count($details) > 0)
    initSelectizeMaster();
@endif

function removeItem(id) {
    $.ajax({
        url: "{{URL::to('accounting/point/cut-off/inventory/delete-tmp')}}",
        type:'POST',
        data:{id:id},
        success: function(data) {
            calculate();
            reCalculate();
        }
    });
}
</script>
