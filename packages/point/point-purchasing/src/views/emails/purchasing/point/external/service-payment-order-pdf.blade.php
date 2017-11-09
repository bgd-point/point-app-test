@extends('core::pdf.layout')

@section('header')
    <tr>
        <td width="25%">No. Pembayaran</td>
        <td width="10px">:</td>
        <td>{{ $payment_collection->formulir->form_number }}</td>
    </tr>
    <tr>
        <td>Tanggal</td>
        <td>:</td>
        <td>{{ \DateHelper::formatView($payment_collection->formulir->form_date) }}</td>
    </tr>
    <tr>
        <td>Supplier</td>
        <td>:</td>
        <td>{{ ucwords($payment_collection->person->name) }}</td>
    </tr>
@stop

@section('content')
    <thead>
    <tr>
        <th width="10px">No</th>
        <th>Reference Number</th>
        <th>Notes</th>
        <th class="text-right">Amount</th>
    </tr>
    </thead>
    <tbody>
    <?php $no = 1;?>
    @foreach($payment_collection->details as $payment_collection_detail)
        <tr>
            <td>{{$no}}</td>
            <td>{{\Point\Framework\Helpers\ReferHelper::getReferBy(get_class($payment_collection_detail),$payment_collection_detail->id,get_class($payment_collection),$payment_collection->id)->formulir->form_number}}</td>
            <td>{{$payment_collection_detail->detail_notes}}</td>
            <td class="text-right">{{number_format_quantity($payment_collection_detail->amount)}}</td>
        </tr>
        <?php $no++;?>
    @endforeach
    @if(count($payment_collection->others) > 0)
        <tr>
            <th width="10px">No</th>
            <th>Account</th>
            <th>Notes</th>
            <th class="text-right">Amount</th>
        </tr>
    <?php $no = 1; ?>
    @foreach($payment_collection->others as $payment_collection_other)
        <tr>
            <td>{{$no}}</td>
            <td>{{ $payment_collection_other->coa->account }}</td>
            <td>{{ $payment_collection_other->other_notes }}</td>
            <td class="text-right">{{number_format_quantity($payment_collection_other->amount)}}</td>
        </tr>
        <?php $no++;?>
    @endforeach
    @endif
    </tbody>
    <tfoot>
    <tr>
        <td colspan="3" class="text-right">Total</td>
        <td class="text-right">{{ number_format_quantity($payment_collection->total_payment) }}</td>
    </tfoot>
@stop

@section('end-notes')
    {{ get_end_notes('purchase service payment order') }}
@stop

@section('signature')
    <td>
        Disetujui,
        <div class="signature-date">{{ \DateHelper::formatView($payment_collection->formulir->approval_at) }}</div>
        <div class="signature">____________________</div>
        <div class="signature-person">({{strtoupper($payment_collection->formulir->approvalTo->name)}})</div>
    </td>
    <td>
        Peminta,
        <div class="signature-date">{{ \DateHelper::formatView($payment_collection->formulir->form_date) }}</div>
        <div class="signature">____________________</div>
        <div class="signature-person">({{strtoupper($payment_collection->person->name)}})</div>
    </td>
@stop
