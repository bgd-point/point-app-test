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
    <tr>
        <td>Total</td>
        <td>:</td>
        <td>{{ number_format_quantity($invoice->total) }}</td>
    </tr>
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
