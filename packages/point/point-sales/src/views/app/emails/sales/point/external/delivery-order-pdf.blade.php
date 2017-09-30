@extends('core::pdf.layout')

@section('header')
    <tr>
        <td width="25%">No. Delivery</td>
        <td width="10px">:</td>
        <td>{{ $delivery_order->formulir->form_number }}</td>
    </tr>
    <tr>
        <td>Tanggal</td>
        <td>:</td>
        <td>{{ \DateHelper::formatView($delivery_order->formulir->form_date) }}</td>
    </tr>
    <tr>
        <td>Customer</td>
        <td>:</td>
        <td>{{ ucwords($delivery_order->person->codeName) }}</td>
    </tr>
    @if($delivery_order->license_plate)
    <tr>
        <td>Nopol</td>
        <td>:</td>
        <td>{{ ucwords($delivery_order->license_plate) }}</td>
    </tr>
    @endif
@stop

@section('content')
    <thead>
    <tr>
        <th width="10px">No</th>
        <th width="220px">Item</th>
        <th class="text-right">Quantity</th>
    </tr>
    </thead>
    <tbody>
    <?php $no = 1;?>
    @foreach($delivery_order->items as $delivery_order_item)
        <tr>
            <td>{{$no}}</td>
            <td>{{ucwords($delivery_order_item->item->codeName)}}</td>
            <td class="text-right">{{number_format_quantity($delivery_order_item->quantity, 0). ' ' .$delivery_order_item->unit}}</td>
        </tr>
        <?php $no++;?>
    @endforeach
    </tbody>
@stop

@section('end-notes')
    {{ get_end_notes('sales invoice') }}
@stop

@section('signature')
    <td>
        Penerima,
        <div class="signature-date">&nbsp;</div>
        <div class="signature">____________________</div>
        <div class="signature-person">( Nama Terang )</div>
    </td>
    <td>
        Pengirim / Ekspedisi,
        <div class="signature-date">&nbsp;</div>
        <div class="signature">____________________</div>
        <div class="signature-person">( Nama Terang )</div>
    </td>
    <td>
        Mengetahui,
        <div class="signature-date">&nbsp;</div>
        <div class="signature">____________________</div>
        <div class="signature-person">( Nama Terang )</div>
    </td>
@stop
