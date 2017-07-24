@section('scripts')
<script>
var counter_product = {{$counter_product}};
var product_table = initDatatable('#product-datatable');
initFunctionRemoveInDatatable('#product-datatable', product_table);

$('#addProductRow').on( 'click', function () {

    product_table.row.add( [
        '<a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a>',
        '<select id="product-id-'+counter_product+'" name="product_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectProduct(this.value, '+counter_product+')" >'
            +'<option ></option>'
        +'</select>',
        '<div class="input-group">'
           +'<input type="text" name="product_quantity[]" class="form-control format-quantity text-right" value="0" />'
        +'</div>',
        '<div class="input-group">'
            +'<input type="text" name="product_unit_id[]" class="form-control input-unit-'+counter_product+'" readonly>'
        +'</div>',
        '<select id="warehouse-id-' + counter_product + '" name="product_warehouse_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
            + '<option ></option>'
            @foreach($list_warehouse as $warehouse)
             + '<option value="{{$warehouse->id}}">{{$warehouse->codeName}}</option>'
            @endforeach
        + '</select>',
    ] ).draw( false );

    initFormatNumber();
    initSelectize('#product-id-'+counter_product);
    reloadItem("#product-id-"+counter_product);
    initSelectize('#warehouse-id-' + counter_product);
    counter_product++;
} );

function selectProduct(product_id, counter) {
    for (var i = 0; i < counter; i++) {
        id = $('#product-id-'+i).val();
        if(id == product_id && counter != i){
            swal("Failed", "Item is already, please choose another item");
            selectizeInFocus('#product-id-'+counter);
            return false;
            break;
            
        }
    };
    getItemUnit(product_id, '.unit-'+counter, 'html');
    getItemUnit(product_id, '.input-unit-'+counter, 'input');
}

var counter_material = {{$counter_material}};
var material_table = initDatatable('#material-datatable');
initFunctionRemoveInDatatable('#material-datatable', material_table);

$('#addItemRow').on( 'click', function () {

    material_table.row.add( [
        '<a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a>',
        '<select id="material-id-'+counter_material+'" name="material_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectItem(this.value, '+counter_material+')" >'
            +'<option ></option>'
        +'</select>',
        '<input type="text" name="material_quantity[]" class="form-control format-quantity text-right" value="0" />',
        '<input type="text" name="material_unit[]" class="form-control input-unit-'+counter_material+'" readonly>',
        '<select id="warehouse-id-' + counter_material + '" name="material_warehouse_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
                + '<option ></option>'
                @foreach($list_warehouse as $warehouse)
                 + '<option value="{{$warehouse->id}}">{{$warehouse->codeName}}</option>'
                @endforeach
        + '</select>',
    ] ).draw( false );

    initFormatNumber();
    initSelectize('#material-id-'+counter_material);
    reloadItemHavingQuantity("#material-id-"+counter_material);
    initSelectize('#warehouse-id-' + counter_material);
    counter_material++;
} );

function selectItem(material_id, counter) {
    for (var i = 0; i < counter; i++) {
        id = $('#material-id-'+i).val();
        if(id == material_id && counter != i){
            swal("Failed", "Item is already, please choose another item");
            selectizeInFocus('#material-id-'+counter);
            return false;
            break;
            
        }
    };
    getItemUnit(material_id, '.unit-'+counter, 'html');
    getItemUnit(material_id, '.input-unit-'+counter, 'input');
}

function selectizeInFocus(item_id) {
    $(item_id)[0].selectize.clear().focus();
}

// reload data item with ajax
if (counter_product > 0) {
    for(var i=0; i< counter_product; i++) {
        if($('#product-id-'+i).length != 0){
            reloadItem('#product-id-' + i, false);
        }
    }    
}
// reload data item with ajax
if (counter_material > 0) {
    for(var i=0; i< counter_material; i++) {
        if($('#material-id-'+i).length != 0){
            reloadItemHavingQuantity('#material-id-' + i, false);
        }
    }    
}

</script>
@stop
