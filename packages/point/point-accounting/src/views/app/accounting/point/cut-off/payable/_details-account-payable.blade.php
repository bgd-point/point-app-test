<div class="table-responsive">
    <table id="item-datatable" class="table table-striped">
        <thead>
            <tr>
                <th>Subledger</th>
                <th>Notes</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody class="manipulate-row">
            <?php $i=0;?>
            @foreach($details as $payable)
                <tr>
                    <td><strong>{{Point\Framework\Models\Master\Person::find($payable->subledger_id)->codeName}}</strong></td>
                    <td><input type="text" readonly name="notes[]" value="{{$payable->notes}}" class="form-control" ></td>
                    <td><input type="text" readonly name="amount[]" id="amount-{{$i}}" class="form-control text-right" value="{{number_format_quantity($payable->amount)}}" /></td>
                </tr>
                <?php $i++;?>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td></td>
                <td class="text-right"><strong>Total</strong></td>
                <td><input type="text" style="width:100%;font-weight:bold" readonly id="total" class="form-control text-right"></td>
            </tr>
        </tfoot>
    </table>
</div>
<script type="text/javascript">
    calculate();
    function calculate() {
        var rows_length = {{count($details)}};
        var subtotal = 0;
        for(var i=0; i<rows_length; i++) {
            var total_per_row = dbNum($('#amount-'+i).val());
            subtotal += total_per_row;
        }

        $('#total').val(accountingNum(subtotal));
    }
</script>

