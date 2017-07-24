@extends('core::pdf.layout')

@section('header')
    <tr>
        <td width="25%">No. PR</td>
        <td width="10px">:</td>
        <td>{{ $purchase_requisition->formulir->form_number }}</td>
    </tr>
    <tr>
        <td>Tanggal</td>
        <td>:</td>
        <td>{{ \DateHelper::formatView($purchase_requisition->formulir->form_date) }}</td>
    </tr>
    <tr>
        <td>Tanggal Jatuh Tempo</td>
        <td>:</td>
        <td>{{ \DateHelper::formatView($purchase_requisition->required_date) }}</td>
    </tr>
    <tr>
        <td>Supplier</td>
        <td>:</td>
        <td>{{ ucwords($purchase_requisition->supplier->codeName) }}</td>
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
    @foreach($purchase_requisition->items as $purchase_requisition_item)
        <tr>
            <td>{{$no}}</td>
            <td>{{ucwords($purchase_requisition_item->item->codeName)}}</td>
            <td class="text-right">{{number_format_quantity($purchase_requisition_item->quantity, 0). ' ' .$purchase_requisition_item->unit}}</td>
        </tr>
        <?php $no++;?>
    @endforeach
@stop

@section('end-notes')
    {{ get_end_notes('purchase requisition') }}
@stop

@section('signature')
    <td>
        Disetujui,
        <div class="signature-date">{{ \DateHelper::formatView($purchase_requisition->formulir->approval_at) }}</div>
        <div class="signature">____________________</div>
        <div class="signature-person">({{strtoupper($purchase_requisition->formulir->approvalTo->name)}})</div>
    </td>
    <td>
        Peminta,
        <div class="signature-date">{{ \DateHelper::formatView($purchase_requisition->formulir->form_date) }}</div>
        <div class="signature">____________________</div>
        <div class="signature-person">({{strtoupper($purchase_requisition->supplier->name)}})</div>
    </td>
@stop

