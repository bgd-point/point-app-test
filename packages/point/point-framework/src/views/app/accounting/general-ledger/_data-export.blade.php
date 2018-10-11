@if($coa)
<h1>{{ $coa->account }}</h1>
@endif
<table class="table table-striped table-bordered">
    <thead>
    <tr>
        <th>Date</th>
        <th>Reference</th>
        <th>Description</th>
        <th>Debit</th>
        <th>Credit</th>
        <th>Balance</th>
    </tr>
    </thead>
    <tbody>
    <?php $balance = Point\Framework\Helpers\AccountingHelper::coaOpeningBalance($coa_id, $date_from); ?>
    <tr>
        <td>{{ date_format_view($date_from) }}</td>
        <td></td>
        <td>OPENING BALANCE</td>
        <td></td>
        <td></td>
        <td>{{$balance}}</td>
    </tr>

    @foreach($journals as $journal)
        <?php
        $as_debit = $journal->coa->category->position->debit;
        if ($as_debit) {
            $balance += $journal->debit;
            $balance -= $journal->credit;
        } else {
            $balance -= $journal->debit;
            $balance += $journal->credit;
        }

        $class = $journal->formulir->formulirable_type;
        ?>
        <tr>
            <td>{{ date_format_view($journal->form_date) }}</td>
            <td><a href="{{ $class::showUrl($journal->formulir->formulirable_id) }}">{{ $journal->formulir->form_number }}</a></td>
            <td>{{ $journal->description }}</td>
            <td>{{ $journal->debit }}</td>
            <td>{{ $journal->credit }}</td>
            <td>{{ $balance }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
