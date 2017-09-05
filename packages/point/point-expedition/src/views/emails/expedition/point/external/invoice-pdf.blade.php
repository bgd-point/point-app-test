@extends('core::pdf.layout')

@section('header')
    <tr>
        <td width="25%">No. Invoice</td>
        <td width="10px">:</td>
        <td>{{ $invoice->formulir->form_number }}</td>
    </tr>
    <tr>
        <td>Tanggal</td>
        <td>:</td>
        <td>{{ \DateHelper::formatView($invoice->formulir->form_date) }}</td>
    </tr>
    <tr>
        <td>Tanggal Jatuh Tempo</td>
        <td>:</td>
        <td>{{ \DateHelper::formatView($invoice->due_date) }}</td>
    </tr>
    <tr>
        <td>Expedition</td>
        <td>:</td>
        <td>{{ ucwords($invoice->expedition->codeName) }}</td>
    </tr>
@stop

@section('content')
    @if($invoice->items->count())
    <thead>
    <tr>
        <th width="10px">No</th>
        <th width="220px">Item</th>
        <th class="text-right">Quantity</th>
    </tr>
    </thead>
    <tbody>
    <?php $no = 1;?>
    @foreach($invoice->items as $invoice_item)
        <tr>
            <td>{{$no}}</td>
            <td>{{ucwords($invoice_item->item->codeName)}}</td>
            <td class="text-right">{{number_format_quantity($invoice_item->quantity, 0). ' ' .$invoice_item->unit}}</td>
        </tr>
        <?php $no++;?>
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <td colspan="5" class="text-right">Subtotal</td>
        <td class="text-right">{{ number_format_quantity($invoice->subtotal) }}</td>
    </tr>
    <tr>
        <td colspan="5" class="text-right">Discount (%)</td>
        <td class="text-right">{{ number_format_quantity($invoice->discount) }}</td>
    </tr>
    <tr>
        <td colspan="5" class="text-right">Tax Base</td>
        <td class="text-right">{{ number_format_quantity($invoice->tax_base) }}</td>
    </tr>
    @if($invoice->type_of_tax != 'non')
        <tr>
            <td colspan="5" class="text-right">Tax ({{ ucwords($invoice->type_of_tax) }})</td>
            <td class="text-right">{{ number_format_quantity($invoice->tax) }}</td>
        </tr>
    @endif
    <tr>
        <td colspan="5" class="text-right">Expedition Fee</td>
        <td class="text-right">{{ number_format_quantity($invoice->expedition_fee) }}</td>
    </tr>
    <tr>
        <td colspan="5" class="text-right">Total</td>
        <td class="text-right">{{ number_format_quantity($invoice->total) }}</td>
    </tr>
    </tfoot>
    @endif
@stop

@section('end-notes')
    {{ get_end_notes('expedition invoice') }}
@stop

@section('signature')
    <td>
        Disetujui,
        <div class="signature-date">{{ \DateHelper::formatView($invoice->formulir->approval_at) }}</div>
        <div class="signature">____________________</div>
        <div class="signature-person">({{strtoupper($invoice->formulir->approvalTo->name)}})</div>
    </td>
    <td>
        Peminta,
        <div class="signature-date">{{ \DateHelper::formatView($invoice->formulir->form_date) }}</div>
        <div class="signature">____________________</div>
        <div class="signature-person">({{strtoupper($invoice->expedition->name)}})</div>
    </td>
@stop
