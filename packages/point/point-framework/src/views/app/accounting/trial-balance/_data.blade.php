<table class="table table-striped table-bordered">
    <thead>
    <tr>
        <th rowspan="2" class="text-center" valign="middle">Account</th>
        <th>Opening Balance</th>
        <th>Debit</th>
        <th>Credit</th>
        <th>Ending Balance</th>
    </tr>
    <tr>
        @if($export)<th></th>@endif
        <th> <span style="font-size:12px">({{date_format_view($date_from)}})</span></th>
        <th> <span style="font-size:12px">({{date_format_view($date_from)}}) - ({{date_format_view($date_to)}})</span></th>
        <th> <span style="font-size:12px">({{date_format_view($date_from)}}) - ({{date_format_view($date_to)}})</span></th>
        <th> <span style="font-size:12px">({{date_format_view($date_to)}})</span></th>
    </tr>
    </thead>
    <tbody>
    <?php 
        $total_debit = 0;
        $total_credit = 0;
        $total_opening_balance = 0;
        $total_ending_balance  = 0;
    ?>

    @foreach($list_coa as $coa)
    <?php 
        $opening_balance = \JournalHelper::coaOpeningBalance($coa->id, $date_from);
        $debit           = \JournalHelper::coaDebit($coa->id, $date_from, $date_to);
        $credit          = \JournalHelper::coaCredit($coa->id, $date_from, $date_to);
        $ending_balance  = \JournalHelper::coaEndingBalance($coa->id, $date_to);

        if (!$coa->category->position->debit) {
            $opening_balance *= -1;
            $ending_balance  *= -1;
        }
        $total_credit          += $credit;
        $total_debit           += $debit;
        $total_opening_balance += $opening_balance;
        $total_ending_balance  += $ending_balance;
    ?>
    <tr>
        <td>{{ $coa->account }}</td>
        <td class="text-right">{{ $export ? $opening_balance : number_format_accounting($opening_balance) }}</td>
        <td class="text-right">{{ $export ? $debit           : number_format_accounting($debit) }}</td>
        <td class="text-right">{{ $export ? $credit          : number_format_accounting($credit) }}</td>
        <td class="text-right">{{ $export ? $ending_balance  : number_format_accounting($ending_balance) }}</td>
    </tr>
    @endforeach
    <tr>
        <td><strong>Total</strong></td>
        <td class="text-right">{{ $export ? round($total_opening_balance, 2) : number_format_accounting($total_opening_balance) }}</td>
        <td class="text-right">{{ $export ? round($total_debit, 2)           : number_format_accounting($total_debit) }}</td>
        <td class="text-right">{{ $export ? round($total_credit, 2)          : number_format_accounting($total_credit) }}</td>
        <td class="text-right">{{ $export ? round($total_ending_balance, 2)  : number_format_accounting($total_ending_balance) }}</td>
    </tr>
    </tbody>
</table>
