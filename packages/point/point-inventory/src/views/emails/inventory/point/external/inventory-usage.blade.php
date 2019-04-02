@extends('core::pdf.layout')

@section('header')
    <tr>
        <td width="25%">No Form</td>
        <td width="10px">:</td>
        <td>{{ $inventory_usage->formulir->form_number }}</td>
    </tr>
    <tr>
        <td>Tanggal</td>
        <td>:</td>
        <td>{{ \DateHelper::formatView($inventory_usage->formulir->form_date) }}</td>
    </tr>
    <tr>
        <td>Warehouse</td>
        <td>:</td>
        <td>
            {{ ucwords($inventory_usage->warehouse->name) }} <br/>
            {{ ucwords($inventory_usage->warehouse->address) }} <br/>
            {{ ucwords($inventory_usage->warehouse->phone) }} <br/>
        </td>
    </tr>
@stop

@section('content')
    <thead>
    <tr>
        <th width="10px">No</th>
        <th>Item</th>
        <th class="text-right">Quantity</th>
    </tr>
    </thead>
    <tbody>
    <?php $no = 1;?>
    @foreach($inventory_usage->listInventoryUsage as $inventory_usage_item)
        <tr>
            <td>{{$no}}</td>
            <td>{{ucwords($inventory_usage_item->item->name)}}</td>
            <td class="text-right">{{number_format_quantity($inventory_usage_item->quantity_usage, 0). ' ' .$inventory_usage_item->unit}}</td>
        </tr>
        <?php $no++;?>
    @endforeach
    </tbody>
@stop

@section('end-notes')
    {{ get_end_notes('delivery order') }}
@stop

@section('signature')
    <td>
        <div class="signature-date">Peminta,</div>
        <div class="signature">_______________________</div>
        <div class="signature-person">( @if($inventory_usage->employee_id){{$inventory_usage->employee->name}}@endif )</div>
    </td>
    <td>
        <div class="signature-date">Mengetahui,</div>
        <div class="signature">_______________________</div>
        <div class="signature-person">( {{$inventory_usage->formulir->approvalTo->name }} )</div>
    </td>
@stop
