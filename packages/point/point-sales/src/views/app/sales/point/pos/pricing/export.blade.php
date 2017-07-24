<html>
<body>
	<table>
	<thead>
		<?php 
            $pricing = session('pricing');
            $list_group = session('list_group');
            $list_item = session('list_item');

            session()->forget('pricing');
            session()->forget('list_group');
            session()->forget('list_item');
            $formulir = Point\Framework\Models\Formulir::find($pricing['formulir_id']);
        ?>
		<tr>
			<th >NO</th>
			<th >CODE</th>
			<th >ITEM</th>
			<th >QUANTITY</th>
			@for($i=0; $i < count($list_group); $i++)
				<th>[{{ strtoupper($list_group[$i]['name'])}}] PRICE</th>
				<th>[{{ strtoupper($list_group[$i]['name'])}}] DISCOUNT %</th>
				<th>[{{ strtoupper($list_group[$i]['name'])}}] NETT</th>
			@endfor
		</tr>
	</thead>
	<tbody>
		@foreach(Point\PointSales\Models\Pos\PosPricingItem::where('pos_pricing_id', $pricing['id'])->groupBy('item_id')->get() as $pos_pricing_item_list)
		<?php
        $quantity = 0;
        $inventory = Point\Framework\Models\Inventory::where('item_id', $pos_pricing_item_list->item_id)->where('form_date', '<=', $formulir->form_date)->get();
        $pos_pricing_item = Point\PointSales\Models\Pos\PosPricingItem::where('pos_pricing_id', $pricing['id'])->where('item_id', '=', $pos_pricing_item_list->item_id)->first();
        ?>
		@if($inventory && $pos_pricing_item)
		<?php
            $quantity = $inventory->sum('total_quantity');
        ?>
        <tr>
        	<td>{{$i+1}}</td>
        	<td>{{$pos_pricing_item_list->item->code}}</td>
			<td>{{$pos_pricing_item_list->item->name}}</td>
            <td>{{$quantity}}</td>
            @for($x=0; $x < count($list_group); $x++)
                <?php
                    $price = 0;
                    $discount = 0;
                    $nett = 0;
                    $pos_pricing_item = Point\PointSales\Models\Pos\PosPricingItem::where('pos_pricing_id', $pricing['id'])->where('item_id', '=', $pos_pricing_item_list->item->id)->where('person_group_id', $list_group[$x]['id'])->first();

                    if ($pos_pricing_item) {
                        $price = $pos_pricing_item->price;
                        $discount = $pos_pricing_item->discount;
                        $nett = $price - $price * $discount / 100;
                    }
                ?>
                <td>{{ $price ? number_format_db($price, 0) : '' }}</td>
                <td>{{ $discount ? number_format_db($discount, 0) : '' }}</td>
                <td>{{ $nett ? number_format_db($nett, 0) : '' }}</td>
            @endfor
        </tr>
		@endif
        @endforeach
	</tbody>
	</table>
</body>
</html>
