<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>form_number</th>
            <th>form_date</th>
            <th>warehouse</th>
            <th>code</th>
            <th>name</th>
            <th>quantity_usage</th>
            <th>unit</th>
            <th>status_form</th>
        </tr>
    </thead>
    <tbody>
        @foreach($list_inventory_usage as $inventoryusage)
        @foreach($inventoryusage->listInventoryUsage as $usage_item)
        <tr>
            <td>{{ $inventoryusage->formulir->form_number}}</td>
            <td>{{ date_format_view($inventoryusage->formulir->form_date) }}</td>
            <td>{{ $inventoryusage->warehouse->name}}</td>
            <td>{{$usage_item->item->code}}</td>
            <td>{{$usage_item->item->name}}</td>
            <td>{{number_format_quantity($usage_item->quantity_usage)}}</td>
            <td>{{$usage_item->unit}}</td>
            <td>
                @include('framework::app.include._approval_status_label', ['approval_status' => $inventoryusage->formulir->approval_status])
                @include('framework::app.include._form_status_label', ['form_status' => $inventoryusage->formulir->form_status])
            </td>
        </tr>
        @endforeach
        @endforeach
    </tbody>
</table>