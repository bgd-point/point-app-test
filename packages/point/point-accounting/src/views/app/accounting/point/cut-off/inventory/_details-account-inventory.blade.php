<div class="table-responsive">
    <table id="item-datatable" class="table table-striped">
        <thead>
            <tr>
                <th>Subledger</th>
                <th>Warehouse</th>
                <th>Stock In Database</th>
                <th>Stock</th>
                <th>Notes</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody class="manipulate-row">
            <?php $i=0;?>
            @foreach($details as $inventory)
            <?php
                $item_unit = optional(Point\Framework\Models\Master\ItemUnit::where('item_id', $inventory->subledger_id)->where('as_default', 1)->first())->name ?? '';
            ?>
                <tr>
                    <td><strong>{{Point\Framework\Models\Master\Item::find($inventory->subledger_id)->codeName}}</strong></td>
                    <td>
                        {{Point\Framework\Models\Master\Warehouse::find($inventory->warehouse_id)->name}}
                    </td>
                    <td>
                        <div class="input-group">
                            <input type="text" name="stock_in_db[]" class="form-control text-right format-quantity" value="{{$inventory->stock_in_database}}"  readonly/>
                            <span class="input-group-addon">{{$item_unit}}</span>
                        </div>
                    </td>
                    <td>
                        <div class="input-group">
                            <input type="text" name="stock_in_db[]" class="form-control text-right format-quantity" value="{{number_format_quantity($inventory->stock)}}"  readonly/>
                            <span class="input-group-addon">{{$item_unit}}</span>
                        </div>
                    </td>
                    <td><input type="text" readonly name="notes[]" value="{{$inventory->notes}}" class="form-control" ></td>
                    <td><input type="text" readonly name="amount[]" id="amount-{{$i}}" class="form-control" value="{{number_format_quantity($inventory->amount)}}" /></td>
                </tr>
                <?php $i++;?>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="text-right"><strong>Total</strong></td>
                <td><input type="text" style="width:100%;font-weight:bold" readonly id="total" class="form-control"></td>
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

