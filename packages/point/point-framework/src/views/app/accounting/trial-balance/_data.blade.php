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
    ?>
    @foreach($list_coa as $coa)
    <?php 
        $opening_balance = \JournalHelper::coaOpeningBalance($coa->id, $date_from);
        $debit = \JournalHelper::coaDebit($coa->id, $date_from, $date_to);
        $credit = \JournalHelper::coaCredit($coa->id, $date_from, $date_to);
        $ending_balance = \JournalHelper::coaEndingBalance($coa->id, $date_to);
    ?>
    <tr>
        <td>{{ $coa->name }}</td>
        <td>{{ number_format_quantity($opening_balance) }}</td>
        <td>{{ number_format_quantity($debit) }}</td>
        <td>{{ number_format_quantity($credit) }}</td>
        <td>{{ number_format_quantity($ending_balance) }}</td>
    </tr>
    @endforeach
    
    </tbody>
</table>
