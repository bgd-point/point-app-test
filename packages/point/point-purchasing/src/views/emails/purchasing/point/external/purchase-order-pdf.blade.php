@extends('core::pdf.layout')

@section('header')
    <tr>
        <td width="25%">No. PO</td>
        <td width="10px">:</td>
        <td>{{ $purchase_order->formulir->form_number }}</td>
    </tr>
    <tr>
        <td>Tanggal Order</td>
        <td>:</td>
        <td>{{ \DateHelper::formatView($purchase_order->formulir->form_date) }}</td>
    </tr>
    <tr>
        <td>Supplier</td>
        <td>:</td>
        <td>{{ ucwords($purchase_order->supplier->name) }}</td>
    </tr>
@stop

@section('content')
    <thead>
    <tr>
        <th width="10px">No</th>
        <th>Item</th>
        <th class="text-right">Quantity</th>
        <th class="text-right">Price</th>
        <th class="text-right">Discount</th>
        <th class="text-right">Total</th>
    </tr>
    </thead>
    <tbody>
    <?php $no = 1;?>
    @foreach($purchase_order->items as $purchase_order_item)
        <tr>
            <td>{{$no}}</td>
            <td>{{ucwords($purchase_order_item->item->name)}}</td>
            <td class="text-right">{{number_format_quantity($purchase_order_item->quantity, 0). ' ' .$purchase_order_item->unit}}</td>
            <td class="text-right">{{number_format_quantity($purchase_order_item->price)}}</td>
            <td class="text-right">{{number_format_quantity($purchase_order_item->discount)}}</td>
            <td class="text-right">{{number_format_quantity($purchase_order_item->quantity * $purchase_order_item->price - ($purchase_order_item->quantity * $purchase_order_item->discount) )}}</td>
        </tr>
        <?php $no++;?>
    @endforeach
    </tbody>
    <tfoot>
    @if($purchase_order->total != $purchase_order->subtotal)
    <tr>
        <td colspan="5" class="text-right">Subtotal</td>
        <td class="text-right">{{ number_format_quantity($purchase_order->subtotal) }}</td>
    </tr>
    @endif
    @if($purchase_order->discount > 0)
    <tr>
        <td colspan="5" class="text-right">Discount (%)</td>
        <td class="text-right">{{ number_format_quantity($purchase_order->discount) }}</td>
    </tr>
    @endif
    @if($purchase_order->type_of_tax != 'non')
    <tr>
        <td colspan="5" class="text-right">Tax Base</td>
        <td class="text-right">{{ number_format_quantity($purchase_order->tax_base) }}</td>
    </tr>
    <tr>
        <td colspan="5" class="text-right">Tax ({{ ucwords($purchase_order->type_of_tax) }})</td>
        <td class="text-right">{{ number_format_quantity($purchase_order->tax) }}</td>
    </tr>
    @endif
    @if($purchase_order->expedition_fee > 0)
    <tr>
        <td colspan="5" class="text-right">Expedition Fee</td>
        <td class="text-right">{{ number_format_quantity($purchase_order->expedition_fee) }}</td>
    </tr>
    @endif
    <tr>
        <td colspan="5" class="text-right">Total</td>
        <td class="text-right">{{ number_format_quantity($purchase_order->total) }}</td>
    </tr>
    </tfoot>
@stop

@section('end-notes')
    {{ get_end_notes('purchase order') }}
@stop

@section('signature')
    <td>
        <div class="signature-date">Disetujui, {{ \DateHelper::formatView($purchase_order->formulir->approval_at) }}</div>
        <div class="signature">___________________________</div>
        <div class="signature-person">({{strtoupper($purchase_order->formulir->approvalTo->name)}})</div>
    </td>
    <td>
        <div class="signature-date">Penerima, {{ \DateHelper::formatView($purchase_order->formulir->form_date) }}</div>
        <div class="signature">___________________________</div>
        <div class="signature-person">({{strtoupper($purchase_order->supplier->name)}})</div>
    </td>
@stop
