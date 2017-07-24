<div class="table-responsive">
    <table id="item-datatable" class="table table-striped">
        <thead>
            <tr>
                <th style="min-width:20px"></th>
                <th style="min-width:250px">Supplier</th>
                <th style="min-width:250px">Date Purchased</th>
                <th style="min-width:30px">Name Asset</th>
                <th style="min-width:50px">Country</th>
                <th style="min-width:50px">Total Paid</th>
                <th style="min-width:50px">Quantity</th>
                <th style="min-width:50px">Price</th>
                <th style="min-width:50px">Total Price</th>
            </tr>
        </thead>
        <tbody class="manipulate-row">
            @for($i=0;$i<count($details);$i++)
                <tr>
                    <td><a href="javascript:void(0)" class="remove-row btn btn-danger" onclick="removeItem({{$details[$i]['rowid']}})" ><i class="fa fa-trash"></i></a></td>
                    <td>
                        <select id="supplier-{{$i}}" name="supplier_id[]" class="selectize initSelectize" style="width:100%;" data-placeholder="Choose one..">
                            <option value="{{$details[$i]['supplier_id']}}"><?php echo Point\Framework\Models\Master\Person::find($details[$i]['supplier_id'])->codeName;?></option>
                        </select>
                    </td>
                    <td><input type="text" name="date_purchased[]" id="date-purchased-{{$i}}" class="form-control date input-datepicker" data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}" value="{{date(date_format_get(), strtotime(\Carbon::now()))}}"></td>
                    <td><input type="text" name="name_asset[]" id="name-asset-{{$i}}" value="{{$details[$i]['name_asset']}}" class="form-control"></td>
                    <td><input type="text" name="country[]" id="country-{{$i}}" value="{{$details[$i]['country']}}" class="form-control" ></td>
                    <td><input type="text" name="total_paid[]" id="total-paid-{{$i}}" value="{{$details[$i]['total_paid']}}" class="form-control format-quantity text-right" ></td>
                    <td><input type="text" name="quantity[]" id="quantity-{{$i}}" value="{{$details[$i]['quantity']}}" class="form-control format-quantity text-right"></td>
                    <td><input type="text" name="price[]" id="price-{{$i}}" value="{{$details[$i]['price']}}" class="form-control format-quantity text-right"></td>
                    <td><input type="text" name="total_price[]" id="total-price-{{$i}}" value="{{$details[$i]['total_price']}}" class="form-control format-quantity text-right" onkeyup="calculate()"></td>
                </tr>
            @endfor
        </tbody>
        <tfoot>
            <tr>
                <td><input type="button" class="btn btn-primary" onclick="validateRow()" value="Add Fixed Asset"></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td colspan="2"><input type="text" style="width:100%; font-weight:bold" readonly id="total" class="form-control text-right"></td>
            </tr>
        </tfoot>
    </table>
</div>

@include('framework::scripts.item')
<script>
var counter = {{count($details) ? : 0}};
var datatable = initDatatable('#item-datatable');
initFunctionRemoveInDatatable('#item-datatable', datatable);
initSelectize('.initSelectize');

$('#item-datatable tbody').on('click', '.remove-row', function () {
    datatable.row($(this).parents('tr')).remove().draw();
    calculate();
});

$(function() {
    calculate();
});

function validateRow() {
    for (var i = 0; i <= counter; i++) {
        if($('#supplier-'+i).length != 0){
            if(! $('#supplier-'+i).val()){
                swal("Please, select the supplier");
                selectizeInFocus('#supplier-'+i);
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
        '<select id="supplier-'+counter+'" name="supplier_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
            +'<option ></option>'
        +'</select>',
        '<input type="text" name="date_purchased[]" id="date-purchased-'+counter+'" class="form-control date input-datepicker" data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}" value="{{date(date_format_get(), strtotime(\Carbon::now()))}}">',
        '<input type="text" name="name_asset[]" id="name-asset-'+counter+'" class="form-control" />',
        '<input type="text" name="country[]" id="country-'+counter+'" class="form-control" />',
        '<input type="text" name="total_paid[]" id="total-paid-'+counter+'" class="form-control format-quantity text-right" />',
        '<input type="text" name="quantity[]" id="quantity-'+counter+'" class="form-control format-quantity text-right"/>',
        '<input type="text" name="price[]" id="price-'+counter+'" class="form-control format-quantity text-right"/>',
        '<input type="text" name="total_price[]" id="total-price-'+counter+'" class="form-control format-quantity text-right" onkeyup="calculate()"/>',
        
    ] ).draw( false );

    initSelectize('#supplier-'+counter);
    reloadSupplierInSelectize("#supplier-"+counter);
    counter++;
    initFormatNumber();
}

function reloadSupplierInSelectize(element) {
    var url = "{{URL::to('master/contact/list-by-type/supplier')}}";
    $.ajax({
        url: url,
        success: function(data) {
            console.log(data);
            var supplier = $(element)[0].selectize;
            supplier.clear();
            supplier.load(function(callback) {
                callback(eval(JSON.stringify(data.lists)));
            });
        }
    });
}

function calculate() {
    var subtotal = 0;
    for(var i=0; i < counter; i++) {
        if ($('#total-price-'+i).length != 0) {
            var total_per_row = dbNum($('#total-price-'+i).val());
            subtotal += total_per_row;    
        }
    }

    $('#total').val(accountingNum(subtotal));
}

function initSelectizeMaster() {
    var url = "{{URL::to('master/contact/list-by-type/supplier')}}";
    $.ajax({
        url: url,
        success: function(data) {
            for(var i=0; i<{{count($details)}}; i++) {
                if($('#supplier-'+i).length != 0){
                    var supplier = $("#supplier-"+i)[0].selectize;
                    supplier.load(function(callback) {
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
        url: "{{URL::to('accounting/point/cut-off/fixed-assets/delete-tmp')}}",
        type:'POST',
        data:{id:id},
        success: function(data) {
            calculate();
            reCalculate();
        }
    });
}
</script>
