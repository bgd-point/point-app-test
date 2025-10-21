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
        <?php
            $balance = 0;
            $opening_balance = 0;
            if(\Input::get('subledger_id')) {
                $opening_balance = \JournalHelper::coaOpeningBalanceSubledger($coa_id, $date_from, \Input::get('subledger_id'));
                if (request()->get('database_name') == 'p_test') {
                    // dd($coa_id, $date_from, \Input::get('subledger_id'));
                }
                $balance = $opening_balance;
            } else {
                $opening_balance = \JournalHelper::coaOpeningBalance($coa_id, $date_from);
                $balance = $opening_balance;
            }
        ?>
        <tr>
            <td>{{ date_format_view($date_from) }}</td>
            <td></td>
            <td>OPENING BALANCE</td>
            <td></td>
            <td></td>
            @if(\Input::get('subledger_id'))
            <td>{{ number_format_accounting($opening_balance) }}</td>
            @else
            <td>{{ number_format_accounting($opening_balance) }}</td>
            @endif
        </tr>
        
        @if($journals)
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
        ?>
        
        <tr>
            <td>{{ date_format_view($journal->form_date) }}</td>
            <td>{{ $journal->formulir->form_number }}</td>
            <td>{{ $journal->description }}</td>
            <td>{{ number_format_accounting($journal->debit) }}</td>
            <td>{{ number_format_accounting($journal->credit) }}</td>
            <td>{{ number_format_accounting($balance) }}</td>
        </tr>
        @endforeach
        @endif
    </tbody>
</table>
