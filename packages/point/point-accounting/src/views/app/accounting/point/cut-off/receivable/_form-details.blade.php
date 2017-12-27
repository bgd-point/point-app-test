<div class="table-responsive">
    <table id="item-datatable" class="table table-striped">
        <thead>
            <tr>
                <th></th>
                <th>Subledger</th>
                <th>Amount</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody class="manipulate-row">
        
            @for($i=0;$i<count($details);$i++)
                <tr>
                    <td><a href="javascript:void(0)" class="remove-row btn btn-danger" onclick="removeItem({{$details[$i]['rowid']}})" ><i class="fa fa-trash"></i></a></td>
                    <td>
                        <select id="subledger-{{$i}}" name="subledger_id[]" class="selectize initSelectize" style="width:100%;" data-placeholder="Choose one..">
                            <option value="{{$details[$i]['subledger_id']}}"><?php echo Point\Framework\Models\Master\Person::find($details[$i]['subledger_id'])->codeName;?></option>
                        </select>
                    </td>
                    <td><input type="text" name="amount[]" id="amount-{{$i}}" onkeyup="calculate()" class="form-control text-right format-quantity-alt" value="{{$details[$i]['amount']}}" /></td>
                    <td><input type="text" name="notes[]" value="{{$details[$i]['notes']}}" class="form-control" ></td>
                </tr>
            @endfor
        </tbody>
        <tfoot>
            <tr>
                <td><input type="button" class="btn btn-primary" onclick="addRow()" value="Add Subledger"></td>
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
        if($('#subledger-'+i).length != 0){
            if(! $('#subledger-'+i).val()){
                swal("Please, select the subledger");
                selectizeInFocus('#subledger-'+i);
                return false;
                break;
            }

        }
    };
}

function addRow() {
    validateRow();
    datatable.row.add( [
        '<a href="javascript:void(0)" onclick="calculate()" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a>',
        '<select id="subledger-'+counter+'" name="subledger_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
            +'<option ></option>'
        +'</select>',
        '<input type="text" name="amount[]" id="amount-'+counter+'" onkeyup="calculate()" class="form-control format-quantity text-right" value="0" />',
        '<input type="text" name="notes[]" id="notes-'+counter+'" class="form-control" />',
    ] ).draw( false );

    initFormatNumber();
    initSelectize('#subledger-'+counter);
    reloadPersonInSelectize("#subledger-"+counter);
    counter++;
}

function reloadPersonInSelectize(subledger_id) {
    var url = "{{URL::to('master/contact/list')}}";
    $.ajax({
        url: url,
        success: function(data) {
            var subledger = $(subledger_id)[0].selectize;
            subledger.clear();
            subledger.load(function(callback) {
                callback(eval(JSON.stringify(data.lists)));
            });
        }
    });
}

function calculate() {
    var subtotal = 0;
    for(var i=0; i<=counter; i++) {
        if($('#amount-'+i).length != 0){
            var total_per_row = dbNum($('#amount-'+i).val());
            subtotal += total_per_row;
        }
    }

    $('#total').val(accountingNum(subtotal));
}

@if(count($details) > 0)
    initSelectizePerson();
@endif

function initSelectizePerson() {
    var url = "{{URL::to('master/contact/list')}}";
    $.ajax({
        url: url,
        success: function(data) {
            for(var i=0; i<{{count($details)}}; i++) {
                if($('#subledger-'+i).length != 0){
                    var subledger = $("#subledger-"+i)[0].selectize;
                    subledger.load(function(callback) {
                        callback(eval(JSON.stringify(data.lists)));
                    });
                }
            }
            
        }
    });
}

function removeItem(id) {
    
    $.ajax({
        url: "{{URL::to('accounting/point/cut-off/receivable/delete-tmp')}}",
        type:'POST',
        data:{id:id},
        success: function(data) {
            calculate();
            reCalculate();
        }
    });
}
</script>
