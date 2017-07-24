<div class="table-responsive">
    <table id="item-datatable" class="table table-striped">
        <thead>
            <tr>
                <th style="min-width:250px">Supplier</th>
                <th style="min-width:250px">Date <br> Purchased</th>
                <th style="min-width:30px">Asset Name</th>
                <th style="min-width:50px">Country</th>
                <th style="min-width:50px" class="text-right">Total Paid</th>
                <th style="min-width:50px" class="text-right">Quantity</th>
                <th style="min-width:50px" class="text-right">Price</th>
                <th style="min-width:50px" class="text-right">Total Price</th>
            </tr>
        </thead>
        <tbody class="manipulate-row">
            <?php $i=0;?>
            @foreach($details as $fixed_assets)
            <tr>
                <td><?php echo Point\Framework\Models\Master\Person::find($fixed_assets->supplier_id)->codeName;?></td>
                <td><input type="text" class="form-control date input-datepicker" data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}" value="{{date(date_format_get(), strtotime($fixed_assets->date_purchased))}}" readonly=""></td>
                <td><input type="text" value="{{$fixed_assets->name}}" class="form-control"></td>
                <td><input type="text" value="{{$fixed_assets->country}}" class="form-control" ></td>
                <td><input type="text" value="{{number_format_quantity($fixed_assets->total_paid)}}" class="form-control format-quantity text-right" readonly=""></td>
                <td><input type="text" value="{{number_format_quantity($fixed_assets->quantity)}}" class="form-control format-quantity text-right" readonly=""></td>
                <td><input type="text" value="{{number_format_quantity($fixed_assets->price)}}" class="form-control format-quantity text-right" readonly=""></td>
                <td><input type="text" value="{{number_format_quantity($fixed_assets->total_price)}}" id="total-price-{{$i}}" class="form-control format-quantity text-right" readonly=""></td>
            </tr>
            <?php $i++;?>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td class="text-right" colspan="7"><strong>Total</strong></td>
                <td><input type="text" style="width:100%;font-weight:bold" readonly id="total" class="form-control"></td>
            </tr>
        </tfoot>
    </table>
</div>
<script type="text/javascript">
    $(function() {
        calculate();
    });
    function calculate() {
        var counter = {{count($details)}};
        var subtotal = 0;
        for(var i=0; i<counter; i++) {
            var total_per_row = dbNum($('#total-price-'+i).val());
            subtotal += total_per_row;
        }

        $('#total').val(accountingNum(subtotal));
    }
</script>

