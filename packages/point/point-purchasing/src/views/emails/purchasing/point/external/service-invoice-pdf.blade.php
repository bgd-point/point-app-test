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
        <td>Supplier</td>
        <td>:</td>
        <td>{{ ucwords($invoice->person->name) }}</td>
    </tr>
@stop

@section('content')
    <thead>
    <tr>
        <th width="10px">No</th>
        <th>Service</th>
        <th class="text-right">Quantity</th>
        <th class="text-right">Price</th>
        <th class="text-right">Discount</th>
        <th class="text-right">Total</th>
    </tr>
    </thead>
    <tbody>
    <?php $no = 1;?>
    @foreach($invoice->services as $invoice_service)
        <tr>
            <td>{{$no}}</td>
            <td>{{ucwords($invoice_service->service->name)}}</td>
            <td class="text-right">{{number_format_quantity($invoice_service->quantity, 0)}}</td>
            <td class="text-right">{{number_format_quantity($invoice_service->price)}}</td>
            <td class="text-right">{{number_format_quantity($invoice_service->discount)}}</td>
            <td class="text-right">{{number_format_quantity($invoice_service->quantity * $invoice_service->price - ($invoice_service->quantity * $invoice_service->discount) )}}</td>
        </tr>
        <?php $no++;?>
    @endforeach
    @if($invoice->items->count())
        <tr>
            <th width="10px">No</th>
            <th width="150">Item</th>
            <th class="text-right">Quantity</th>
            <th class="text-right">Price</th>
            <th class="text-right">Discount</th>
            <th class="text-right">Total</th>
        </tr>
    <?php $no = 1;?>
    @foreach($invoice->items as $invoice_item)
        <tr>
            <td>{{$no}}</td>
            <td>{{ $invoice_item->item->name }}</td>
            <td class="text-right">{{ number_format_quantity($invoice_item->quantity) }} {{ $invoice_item->unit }}</td>
            <td class="text-right">{{ number_format_quantity($invoice_item->price) }}</td>
            <td class="text-right">{{ number_format_quantity($invoice_item->discount) }}</td>
            <td class="text-right">{{ number_format_quantity(($invoice_item->quantity * $invoice_item->price) - ($invoice_item->quantity * $invoice_item->price * $invoice_item->discount / 100)) }}</td>
        </tr>
    <?php $no++;?>
    @endforeach
    @endif
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
        <td colspan="5" class="text-right">Total</td>
        <td class="text-right">{{ number_format_quantity($invoice->total) }}</td>
    </tr>
    </tfoot>
@stop

@section('end-notes')
    {{ get_end_notes('purchase service invoice') }}
@stop

@section('signature')
    <td>
        <div class="signature-date">Disetujui,<br/>{{ \DateHelper::formatView($invoice->formulir->approval_at) }}</div>
        <div class="signature">____________________</div>
        <div class="signature-person">({{strtoupper($invoice->formulir->approvalTo->name)}})</div>
    </td>
    <td>
        <div class="signature-date">Penerima,<br/>{{ \DateHelper::formatView($invoice->formulir->form_date) }}</div>
        <div class="signature">____________________</div>
        <div class="signature-person">({{strtoupper($invoice->person->name)}})</div>
    </td>
@stop
