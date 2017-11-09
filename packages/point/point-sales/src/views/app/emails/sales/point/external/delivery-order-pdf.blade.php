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
        <td>{{ ucwords($delivery_order->person->name) }}</td>
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
        <th>Item</th>
        <th class="text-right">Quantity</th>
    </tr>
    </thead>
    <tbody>
    <?php $no = 1;?>
    @foreach($delivery_order->items as $delivery_order_item)
        <tr>
            <td>{{$no}}</td>
            <td>{{ucwords($delivery_order_item->item->name)}}</td>
            <td class="text-right">{{number_format_quantity($delivery_order_item->quantity, 0). ' ' .$delivery_order_item->unit}}</td>
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
        <div class="signature-title">Penerima,</div>
        <div class="signature-date">&nbsp;</div>
        <div class="signature">____________________</div>
        <div class="signature-person">( Nama Terang )</div>
    </td>
    <td>
        <div class="signature-title">Pengirim / Ekspedisi,</div>
        <div class="signature-date">&nbsp;</div>
        <div class="signature">____________________</div>
        <div class="signature-person">( Nama Terang )</div>
    </td>
    <td>
        <div class="signature-title">Mengetahui,</div>
        <div class="signature-date">&nbsp;</div>
        <div class="signature">____________________</div>
        <div class="signature-person">( Nama Terang )</div>
    </td>
@stop
