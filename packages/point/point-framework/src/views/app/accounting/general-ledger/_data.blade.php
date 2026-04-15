<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>Date</th>
            <th>Reference</th>
            <th>Master</th>
            <th>Description</th>
            <th style="text-align:right">Debit</th>
            <th style="text-align:right">Credit</th>
            <th style="text-align:right">Balance</th>
        </tr>
    </thead>
    <tbody>
        <?php $balance = Point\Framework\Helpers\AccountingHelper::coaOpeningBalance($coa_id, $date_from); ?>
        <tr>
            <td>{{ date_format_view($date_from) }}</td>
            <td></td>
            <td></td>
            <td>OPENING BALANCE</td>
            <td></td>
            <td></td>
            <td style="text-align:right">{{ number_format_accounting($balance, 4)}}</td>
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

                $classMaster = $journal->subledger_type;
                $name = '';
                if ($classMaster != null && $classMaster != 'RejournalPurchasingAndExpeditionSeeder') {
                    $master = $classMaster::find($journal->subledger_id);
                    $name = '['.$master->code . '] ' . $master->name;
                }
            ?>
            <tr>
                <td>{{ date_format_view($journal->form_date) }}</td>
                <td><a href="{{ $class::showUrl($journal->formulir->formulirable_id) }}">{{ $journal->formulir->form_number }}</a></td>
                <td>{{ $name }}</td>
                @if ($journal->coa->category->name == 'Account Receivable' && $journal->form_reference_id !== null)
                    <td>{{ $journal->reference->form_number }}</td>
                    @else
                    <td>{{ $journal->description }}</td>
                @endif
                <td style="text-align:right">{{ $journal->debit }}</td>
                <td style="text-align:right">{{ $journal->credit }}</td>
                <td style="text-align:right">{{ $balance }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
