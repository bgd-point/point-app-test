@for($i=0;$i<count($details);$i++)
 <?php
    $data = \Point\Core\Helpers\TempDataHelper::searchKeyValue('stock.opname', ['item_id', 'stock_in_database', 'quantity_opname', 'unit1','unit2', 'notes'],
        [$details[$i]['item_id'], $details[$i]['stock_in_database'], $details[$i]['quantity_opname'], $details[$i]['unit1'],$details[$i]['unit2'], $details[$i]['notes']]);
    $item = \Point\Framework\Models\Master\Item::findOrFail($details[$i]['item_id']);
 	$cogs_in_database = number_format_price($item->averageCostOfSales($stockOpname->formulir->form_date));
    ?>

	<tr>
		<td><a href="javascript:void(0)" class="remove-row btn btn-danger" data-item="{{$data['rowid']}}"><i class="fa fa-trash"></i></a></td>
		<td>
			<select id="item-id-{{$i}}" name="item_id[]" class="selectize item-selectize" style="width:100%;" data-placeholder="Choose one.." onchange="validateItem(this.value, {{$i}})">
                <option value="{{$details[$i]['item_id']}}"><?php echo Point\Framework\Models\Master\Item::find($details[$i]['item_id'])->codeName;?></option>
            </select>
		</td>
		<td>
			<div class="input-group">
				<input type="text" id="stok-program-{{$i}}" name="stock_in_database[]" class="form-control text-right format-quantity" value="{{$details[$i]['stock_in_database']}}"  readonly/>
            	<span class="input-group-addon unit-{{$i}}">{{$details[$i]['unit1']}}</span>
            	<input type="hidden" name="unit1[]" class="input-unit-{{$i}}" value="{{$details[$i]['unit1']}}">
			</div>
		</td>
		<td>
			<div class="input-group">
	            <input type="text" id="stok-warehouse-{{$i}}" name="quantity_opname[]" class="form-control format-quantity text-right" value="{{$details[$i]['quantity_opname']}}" />
	            <span class="input-group-addon unit-{{$i}}">{{$details[$i]['unit2']}}</span>
	            <input type="hidden" name="unit2[]" class="input-unit-{{$i}}" value="{{$details[$i]['unit2']}}">
	        </div>
		</td>
		<td>
			<input type="text" readonly id="cogs-{{$i}}" name="cogs[]" class="form-control format-quantity text-right" value="{{ $cogs_in_database }}" />
		</td>
		<td>
			<input type="text" id="cogs-{{$i}}" name="cogs[]" class="form-control format-quantity text-right" value="{{ $cogs_in_database }}" />
		</td>
		<td><input type="text" name="opname_notes[]" class="form-control" value="{{$details[$i]['notes']}}" /></td>
	</tr>
@endfor

<script type="text/javascript">
function reloadItemDetailsInSelectize() {
	$.ajax({
	    url: "{{URL::to('master/item/list')}}",
	    success: function(data) {
	        for(var i = 0; i < {{count($details)}}; i++){
	        	if($('#item-id-'+i).length != 0){
	                var item = $('#item-id-'+i)[0].selectize;
	                item.load(function(callback) {
	                    callback(eval(JSON.stringify(data.lists)));
	                });
	            }
            }
        }
	});
}
</script>
