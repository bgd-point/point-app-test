@extends('core::pdf.layout')

@section('header')
    <tr>
        <td width="20%">No. Invoice</td>
        <td width="10px">:</td>
        <td>{{ $invoice->formulir->form_number }}</td>
    </tr>
    <tr>
        <td>Tanggal</td>
        <td>:</td>
        <td>{{ \DateHelper::formatView($invoice->formulir->form_date) }}</td>
    </tr>
    <tr>
        <td>Jatuh Tempo</td>
        <td>:</td>
        <td>{{ \DateHelper::formatView($invoice->due_date) }}</td>
    </tr>
    @if($invoice->lockingForm)
    <tr>
        <td>Delivery Order</td>
        <td>:</td>
        <td>
            @foreach($invoice->lockingForm as $lockingForm)
                {!! formulir_url($lockingForm->lockedForm) !!}
            @endforeach
        </td>
    </tr>
    @endif
    <tr>
        <td>Customer</td>
        <td>:</td>
        <td>{{ ucwords($invoice->person->name) }}</td>
    </tr>
    @if($invoice->person->address)
    <tr>
        <td>Address</td>
        <td>:</td>
        <td>{{ ucwords($invoice->person->address) }}</td>
    </tr>
    @endif
@stop

@section('content')
    <thead>
    <tr>
        <td width="15px">No</td>
        <td width="70%">Item</td>
        <td class="text-right">Quantity</td>
        <td class="text-right">Price</td>
        <td class="text-right">Discount</td>
        <td class="text-right">Total</td>
    </tr>
    </thead>
    <tbody>
    <?php $no = 1;?>
    @foreach($invoice->items as $invoice_item)
        <tr>
            <td>{{$no}}</td>
            <td>{{ $invoice_item->item->name }}</td>
            <td class="text-right">{{number_format_quantity($invoice_item->quantity, 0). ' ' .$invoice_item->unit}}</td>
            <td class="text-right">{{number_format_quantity($invoice_item->price)}}</td>
            <td class="text-right">{{number_format_quantity($invoice_item->discount)}}</td>
            <td class="text-right">{{number_format_quantity($invoice_item->quantity * $invoice_item->price - ($invoice_item->quantity * $invoice_item->discount) )}}</td>
        </tr>
        <?php $no++;?>
    @endforeach
    </tbody>
    <tfoot>
    @if($invoice->subtotal != $invoice->total)
    <tr>
        <td colspan="5" class="text-right">Subtotal</td>
        <td class="text-right">{{ number_format_quantity($invoice->subtotal) }}</td>
    </tr>
    @endif
    @if($invoice->discount > 0)
    <tr>
        <td colspan="5" class="text-right">Discount (%)</td>
        <td class="text-right">{{ number_format_quantity($invoice->discount) }}</td>
    </tr>
    @endif
    @if($invoice->type_of_tax != 'non')
        <tr>
            <td colspan="5" class="text-right">Tax ({{ ucwords($invoice->type_of_tax) }})</td>
            <td class="text-right">{{ number_format_quantity($invoice->tax) }}</td>
        </tr>
    @endif
    @if($invoice->expedition_fee > 0)
    <tr>
        <td colspan="5" class="text-right">Expedition Fee</td>
        <td class="text-right">{{ number_format_quantity($invoice->expedition_fee) }}</td>
    </tr>
    @endif
    <tr>
        <td colspan="5" class="text-right">Total</td>
        <td class="text-right">{{ number_format_quantity($invoice->total) }}</td>
    </tr>
    </tfoot>
@stop

@section('end-notes')
    @if(get_end_notes('sales invoice'))
        {!! get_end_notes('sales invoice') !!}
        <br/><br/>
    @endif
@stop

@section('signature')
    <td style="padding-left:20px">
        <div class="signature-date">Tanda terima,</div>
        {{--<div class="signature">__________________________</div>--}}
        <div class="signature-person"></div>
    </td>
    <td>

        <div class="signature-date">Hormat kami,<br/>{{ \DateHelper::formatView($invoice->formulir->form_date) }}</div>
        {{--<div class="signature">__________________________</div>--}}
        <div class="signature-person"></div>
    </td>
@stop
