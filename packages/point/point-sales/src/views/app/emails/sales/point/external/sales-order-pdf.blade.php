@extends('core::pdf.layout')

@section('header')
    <tr>
        <td width="25%">No. SO</td>
        <td width="10px">:</td>
        <td>{{ $sales_order->formulir->form_number }}</td>
    </tr>
    <tr>
        <td>Tanggal Order</td>
        <td>:</td>
        <td>{{ \DateHelper::formatView($sales_order->formulir->form_date) }}</td>
    </tr>
    <tr>
        <td>Customer</td>
        <td>:</td>
        <td>{{ ucwords($sales_order->person->codeName) }}</td>
    </tr>
@stop

@section('content')
    <thead>
    <tr>
        <th width="10px">No</th>
        <th width="220px">Item</th>
        <th class="text-right">Quantity</th>
        <th class="text-right">Price</th>
        <th class="text-right">Discount</th>
        <th class="text-right">Total</th>
    </tr>
    </thead>
    <tbody>
    <?php $no = 1;?>
    @foreach($sales_order->items as $sales_order_item)
        <tr>
            <td>{{$no}}</td>
            <td>{{ucwords($sales_order_item->item->codeName)}}</td>
            <td class="text-right">{{number_format_quantity($sales_order_item->quantity, 0). ' ' .$sales_order_item->unit}}</td>
            <td class="text-right">{{number_format_quantity($sales_order_item->price)}}</td>
            <td class="text-right">{{number_format_quantity($sales_order_item->discount)}}</td>
            <td class="text-right">{{number_format_quantity($sales_order_item->quantity * $sales_order_item->price - ($sales_order_item->quantity * $sales_order_item->discount) )}}</td>
        </tr>
        <?php $no++;?>
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <td colspan="5" class="text-right">Subtotal</td>
        <td class="text-right">{{ number_format_quantity($sales_order->subtotal) }}</td>
    </tr>
    <tr>
        <td colspan="5" class="text-right">Discount (%)</td>
        <td class="text-right">{{ number_format_quantity($sales_order->discount) }}</td>
    </tr>
    <tr>
        <td colspan="5" class="text-right">Tax Base</td>
        <td class="text-right">{{ number_format_quantity($sales_order->tax_base) }}</td>
    </tr>
    @if($sales_order->type_of_tax != 'non')
        <tr>
            <td colspan="5" class="text-right">Tax ({{ ucwords($sales_order->type_of_tax) }})</td>
            <td class="text-right">{{ number_format_quantity($sales_order->tax) }}</td>
        </tr>
    @endif
    <tr>
        <td colspan="5" class="text-right">Expedition Fee</td>
        <td class="text-right">{{ number_format_quantity($sales_order->expedition_fee) }}</td>
    </tr>
    <tr>
        <td colspan="5" class="text-right">Total</td>
        <td class="text-right">{{ number_format_quantity($sales_order->total) }}</td>
    </tr>
    </tfoot>
@stop

@section('end-notes')
    {{ get_end_notes('sales order') }}
@stop

@section('signature')
    <td>
        Disetujui,
        <div class="signature-date">{{ \DateHelper::formatView($sales_order->formulir->approval_at) }}</div>
        <div class="signature">____________________</div>
        <div class="signature-person">({{strtoupper($sales_order->formulir->approvalTo->name)}})</div>
    </td>
    <td>
        Peminta,
        <div class="signature-date">{{ \DateHelper::formatView($sales_order->formulir->form_date) }}</div>
        <div class="signature">____________________</div>
        <div class="signature-person">({{strtoupper($sales_order->person->name)}})</div>
    </td>
@stop
