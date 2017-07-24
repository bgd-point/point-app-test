@extends('core::pdf.layout')

@section('header')
    <tr>
        <td width="25%">No. Order</td>
        <td width="10px">:</td>
        <td>{{ $expedition_order->formulir->form_number }}</td>
    </tr>
    <tr>
        <td>Tanggal Order</td>
        <td>:</td>
        <td>{{ \DateHelper::formatView($expedition_order->formulir->form_date) }}</td>
    </tr>
    <tr>
        <td>Expedition</td>
        <td>:</td>
        <td>{{ ucwords($expedition_order->expedition->codeName) }}</td>
    </tr>
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
    @foreach($expedition_order->items as $expedition_order_item)
        <tr>
            <td>{{$no}}</td>
            <td>{{ucwords($expedition_order_item->item->codeName)}}</td>
            <td class="text-right">{{number_format_quantity($expedition_order_item->quantity, 0). ' ' .$expedition_order_item->unit}}</td>
        </tr>
        <?php $no++;?>
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <td colspan="5" class="text-right">Subtotal</td>
        <td class="text-right">{{ number_format_quantity($expedition_order->expedition_fee) }}</td>
    </tr>
    <tr>
        <td colspan="5" class="text-right">Discount (%)</td>
        <td class="text-right">{{ number_format_quantity($expedition_order->discount) }}</td>
    </tr>
    <tr>
        <td colspan="5" class="text-right">Tax Base</td>
        <td class="text-right">{{ number_format_quantity($expedition_order->tax_base) }}</td>
    </tr>
    @if($expedition_order->type_of_tax != 'non')
        <tr>
            <td colspan="5" class="text-right">Tax ({{ ucwords($expedition_order->type_of_tax) }})</td>
            <td class="text-right">{{ number_format_quantity($expedition_order->tax) }}</td>
        </tr>
    @endif
    <tr>
        <td colspan="5" class="text-right">Expedition Fee</td>
        <td class="text-right">{{ number_format_quantity($expedition_order->expedition_fee) }}</td>
    </tr>
    <tr>
        <td colspan="5" class="text-right">Total</td>
        <td class="text-right">{{ number_format_quantity($expedition_order->total) }}</td>
    </tr>
    </tfoot>
@stop

@section('end-notes')
    {{ get_end_notes('expedition order') }}
@stop

@section('signature')
    <td>
        Disetujui,
        <div class="signature-date">{{ \DateHelper::formatView($expedition_order->formulir->approval_at) }}</div>
        <div class="signature">____________________</div>
        <div class="signature-person">({{strtoupper($expedition_order->formulir->approvalTo->name)}})</div>
    </td>
    <td>
        Peminta,
        <div class="signature-date">{{ \DateHelper::formatView($expedition_order->formulir->form_date) }}</div>
        <div class="signature">____________________</div>
        <div class="signature-person">({{strtoupper($expedition_order->expedition->name)}})</div>
    </td>
@stop
