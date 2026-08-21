<table class="table table-striped table-bordered">
    <thead></thead>
    <tbody>
        @foreach($list_inventory_usage as $inventoryusage)
        <tr>
            <td>{{ date_format_view($inventoryusage->formulir->form_date) }}</td>
            <td><a href="{{ url('inventory/point/inventory-usage/'.$inventoryusage->id) }}">{{ $inventoryusage->formulir->form_number}}</a></td>
            <td>{{ $inventoryusage->warehouse->codeName}}</td>
            <td>
                @include('framework::app.include._approval_status_label', ['approval_status' => $inventoryusage->formulir->approval_status])
                @include('framework::app.include._form_status_label', ['form_status' => $inventoryusage->formulir->form_status])
            </td>
        </tr>

        <tr>
            <th></th>
            <th>ITEM</th>
            <th>STOCK BEFORE USAGE</th>
            <th>QUANTITY USAGE</th>
        </tr>

        @foreach($inventoryusage->listInventoryUsage as $usage_item)
            <tr>
                <td></td>
                <td>{{$usage_item->item->name}}</td>
                <td>{{number_format_quantity($usage_item->stock_in_database)}} {{$usage_item->unit}}</td>
                <td>{{number_format_quantity($usage_item->quantity_usage)}} {{$usage_item->unit}}</td>
            </tr>
        @endforeach
        
        @endforeach
    </tbody>
</table>