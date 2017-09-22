<table class="table table-striped table-bordered" style="white-space: nowrap; ">
    <thead>
        <tr>
            <th style="font-weight: bold">Form Date</th>
            <th style="font-weight: bold">Form Number</th>
            <th style="font-weight: bold">Group</th>
            <th style="font-weight: bold">Owner</th>
            <th style="font-weight: bold">Broker</th>
            <th class="text-right" style="font-weight: bold">Fee</th>
            <th class="text-right" style="font-weight: bold">Quantity</th>
            <th class="text-right" style="font-weight: bold">Remining Quantity</th>
            <th class="text-right" style="font-weight: bold">Price</th>
            <th class="text-right" style="font-weight: bold">Ex Sale</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ date_format_view($buy->formulir->form_date) }}</td>
            <td>{{ $buy->formulir->form_number }}</td>
            <td>{{ $buy->ownerGroup->name }}</td>
            <td>{{ $buy->owner->name }}</td>
            <td>{{ $buy->broker->name }}</td>
            <td class="text-right">{{ number_format_quantity($buy->fee) }}</td>
            <td class="text-right">{{ number_format_quantity($buy->quantity, 0) }}</td>
            <td class="text-right">{{ number_format_quantity($buy->quantity, 0) }}</td>
            <td class="text-right">{{ number_format_quantity($buy->price) }}</td>
            <td class="text-center"> - </td>
        </tr>

        <?php $remaining_quantity = $buy->quantity; ?>
        @foreach($list_stock_fifo as $stock_fifo)
        <?php
        $sell = Point\BumiShares\Models\Sell::where('formulir_id', $stock_fifo->shares_out_id)->first();
        $remaining_quantity -= $stock_fifo->quantity;
        ?>
        <tr>
            <td>{{ date_format_view($sell->formulir->form_date) }}</td>
            <td>{{ $sell->formulir->form_number }}</td>
            <td>{{ $sell->ownerGroup->name }}</td>
            <td>{{ $sell->owner->name }}</td>
            <td>{{ $sell->broker->name }}</td>
            <td class="text-right">{{ number_format_quantity($sell->fee) }}</td>
            <td class="text-right">{{ number_format_quantity($stock_fifo->quantity, 0) }}</td>
            <td class="text-right">{{ number_format_quantity($remaining_quantity, 0) }}</td>
            <td class="text-right">{{ number_format_quantity($stock_fifo->price) }}</td>
            <td class="text-right">{{ number_format_quantity($stock_fifo->average_price) }}</td>
        </tr>
        @endforeach
        
    </tbody>
</table>