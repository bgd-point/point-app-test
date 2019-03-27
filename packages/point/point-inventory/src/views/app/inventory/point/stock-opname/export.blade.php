<tr>
    <td>STOCK OPNAME</td>
</tr>

<tr>
    <td>#</td>
    <td>DATE</td>
    <td>WAREHOUSE</td>
    <td>ITEM</td>
    <td>FROM</td>
    <td>TO</td>
</tr>

@foreach($list_report as $opname)
    @foreach ($opname->items as $detail)
    <tr>
        <td>{{ $opname->formulir->form_number }}</td>
        <td>{{ $opname->formulir->form_date }}</td>
        <td>{{ $opname->warehouse->name }}</td>
        <td>[{{ $detail->item->code }}] {{ $detail->item->name }}</td>
        <td>{{ $detail->stock_in_database }}</td>
        <td>{{ $detail->quantity_opname }}</td>
    </tr>
    @endforeach
@endforeach
