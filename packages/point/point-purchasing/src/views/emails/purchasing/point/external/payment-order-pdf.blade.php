@extends('core::pdf.layout')

@section('header')
    <tr>
        <td width="25%">No. Pembayaran</td>
        <td width="10px">:</td>
        <td>{{ $payment_order->formulir->form_number }}</td>
    </tr>
    <tr>
        <td>Tanggal</td>
        <td>:</td>
        <td>{{ \DateHelper::formatView($payment_order->formulir->form_date) }}</td>
    </tr>
    <tr>
        <td>Supplier</td>
        <td>:</td>
        <td>{{ ucwords($payment_order->supplier->name) }}</td>
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
    @foreach($payment_order->details as $payment_collection_detail)
        <tr>
            <td>{{$no}}</td>
            <td>{{\Point\Framework\Helpers\ReferHelper::getReferBy(get_class($payment_collection_detail),$payment_collection_detail->id,get_class($payment_order),$payment_order->id)->formulir->form_number}}</td>
            <td>{{$payment_collection_detail->detail_notes}}</td>
            <td class="text-right">{{number_format_quantity($payment_collection_detail->amount)}}</td>
        </tr>
        <?php $no++;?>
    @endforeach
    @if(count($payment_order->others) > 0)
        <tr>
            <th width="10px">No</th>
            <th>Account</th>
            <th>Notes</th>
            <th class="text-right">Amount</th>
        </tr>
    <?php $no = 1; ?>
    @foreach($payment_order->others as $payment_collection_other)
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
        <td class="text-right">{{ number_format_quantity($payment_order->total_payment) }}</td>
    </tfoot>
@stop

@section('end-notes')
    {{ get_end_notes('purchase payment order') }}
@stop

@section('signature')
    <td>
        Disetujui,
        <div class="signature-date">{{ \DateHelper::formatView($payment_order->formulir->approval_at) }}</div>
        <div class="signature">____________________</div>
        <div class="signature-person">({{strtoupper($payment_order->formulir->approvalTo->name)}})</div>
    </td>
    <td>
        Penerima,
        <div class="signature-date">{{ \DateHelper::formatView($payment_order->formulir->form_date) }}</div>
        <div class="signature">____________________</div>
        <div class="signature-person">({{strtoupper($payment_order->supplier->name)}})</div>
    </td>
@stop
