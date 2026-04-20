<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>Date</th>
            <th>Reference</th>
            <th>Description</th>
            <th style="text-align:right">Debit</th>
            <th style="text-align:right">Credit</th>
            <th style="text-align:right">Balance</th>
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
            <td style="text-align:right">{{ number_format_accounting($opening_balance, 4) }}</td>
            @else
            <td style="text-align:right">{{ number_format_accounting($opening_balance, 4) }}</td>
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
            <td>{{ $journal->formulir->form_number }} {{ $journal->id }}</td>
            <td>{{ $journal->description }}</td>
            <td style="text-align:right">{{ number_format_accounting($journal->debit, 4) }}</td>
            <td style="text-align:right">{{ number_format_accounting($journal->credit, 4) }}</td>
            <td style="text-align:right">{{ number_format_accounting($balance, 4) }}</td>
        </tr>
        @endforeach
        @endif
    </tbody>
</table>
