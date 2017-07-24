<html>
	<body>
		<table>
		
			<thead>
				<tr>
					<th >NO</th>
					<th >CODE</th>
					<th >ITEM</th>
					<th >COST OF SALES</th>
					<?php

                        $person_type= Point\Framework\Models\Master\PersonType::where('slug', 'customer')->first();
                        $list_group = Point\Framework\Models\Master\PersonGroup::where('person_type_id', '=', $person_type->id)->get();
                        $list_item = Point\Framework\Models\Master\Item::all();

                        $pos_pricing_date = Point\PointSales\Models\Pos\PosPricing::joinFormulir()
                            ->select('point_sales_pos_pricing.id')
                            ->where('formulir.form_date', '<=', \Carbon::now())
                            ->orderBy('formulir.id', 'desc')
                            ->first();
                    ?>
					@foreach($list_group as $group)
						<th>({{ strtoupper($group->name)}}) PRICE</th>
						<th>({{ strtoupper($group->name)}}) DISCOUNT %</th>
					@endforeach
				</tr>
					
			</thead>
			
			<tbody>
				<?php $i = 1;?>
				@foreach($list_item as $item)
					<tr>
						<td><b>{{ $i }}</b></td>
						<td><b>{{ $item->code }}</b></td>
						<td><b>{{ $item->name }}</b></td>
						<td>{{ $item->averageCostOfSales(date_format_db(date('Y-m-d'),date('H:i:s')) ) }}</td>
						@if($pos_pricing_date)
							@foreach($list_group as $group)
								<?php
                                    $pos_pricing_item = Point\PointSales\Models\Pos\PosPricingItem::where('point_sales_pos_pricing_item.pos_pricing_id', $pos_pricing_date->id)
                                    ->where('point_sales_pos_pricing_item.item_id', $item->id)
                                    ->where('point_sales_pos_pricing_item.person_group_id', $group->id)
                                    ->first();
                                ?>
								@if($pos_pricing_item)
									<td>{{ $pos_pricing_item->price }}</td>
									<td>{{ $pos_pricing_item->discount }}</td>
								@endif
							@endforeach
						@endif
					</tr>
					<?php $i++;?>
				@endforeach
			</tbody>
		</table>
	</body>
</html>
