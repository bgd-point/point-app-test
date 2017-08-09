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
        $total_opening_balance = \JournalHelper::totalBalance($date_from);
        $total_ending_balance = \JournalHelper::totalBalance($date_to);
        $total_debit = 0;
        $total_credit = 0;
    ?>
    @foreach($list_coa as $coa)
    <?php 
        $opening_balance = \JournalHelper::coaOpeningBalance($coa->id, $date_from);
        $debit = \JournalHelper::coaDebit($coa->id, $date_from, $date_to);
        $total_debit += $debit;
        $credit = \JournalHelper::coaCredit($coa->id, $date_from, $date_to);
        $total_credit += $credit;
        $ending_balance = \JournalHelper::coaEndingBalance($coa->id, $date_to);
    ?>
    <tr>
        <td>{{ $coa->account }}</td>
        <td class="text-right">{{ $export ? $opening_balance : number_format_quantity($opening_balance) }}</td>
        <td class="text-right">{{ $export ? $debit : number_format_quantity($debit) }}</td>
        <td class="text-right">{{ $export ? $credit : number_format_quantity($credit) }}</td>
        <td class="text-right">{{ $export ? $ending_balance : number_format_quantity($ending_balance) }}</td>
    </tr>
    @endforeach
    <tr>
        <td><strong>Total</strong></td>
        <td class="text-right">{{ $export ? $total_opening_balance : number_format_quantity($total_opening_balance) }}</td>
        <td class="text-right">{{ $export ? $total_debit : number_format_quantity($total_debit) }}</td>
        <td class="text-right">{{ $export ? $total_credit : number_format_quantity($total_credit) }}</td>
        <td class="text-right">{{ $export ? $total_ending_balance : number_format_quantity($total_ending_balance) }}</td>
    </tr>
    </tbody>
</table>
