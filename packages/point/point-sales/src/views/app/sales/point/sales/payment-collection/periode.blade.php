@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <table class="table table-striped">
            <tr>
                <th>ID</th>
                <th>FORM</th>
                <th>DATE</th>
                <th>CUSTOMER</th>
                <th>INVOICE</th>
                <th>REMAINING</th>
            </tr>
            @foreach ($invoices as $key => $invoice)
            <?php
                $total = $invoice->total;
                $debit = \Point\Framework\Models\Journal::where('form_reference_id', $invoice->formulir_id)
                    ->where('form_date', '<' , '2020-01-01 00:00:00')->where('coa_id', 4)->sum('debit');
                $credit = \Point\Framework\Models\Journal::where('form_reference_id', $invoice->formulir_id)
                    ->where('form_date', '<' , '2020-01-01 00:00:00')->where('coa_id', 4)->sum('credit');
                $piutang = $total - ($debit + $credit);
            ?>

            @if ($piutang > 0)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $invoice->formulir->form_number }}</td>
                    <td>{{ date('d F Y', strtotime($invoice->formulir->form_date)) }}</td>
                    <td>{{ $invoice->person->name }}</td>
                    <td>{{ number_format_price($invoice->total * 1) }}</td>
                    <td>{{ number_format_price($piutang) }}</td>
                </tr>
            @endif
            @endforeach
        </table>
    </div>
@stop
