<div>
    <table>
        <thead>
        <tr>
            <th>DEPOSIT REPORT</th>
        </tr>
        <tr>
            <th>FORM NUMBER</th>
            <th>OWNER</th>
            <th>FORM DATE - DUE DATE</th>
            <th>BANK</th>
            <th>BANK ACCOUNT</th>
            <th>NO BILYET</th>
            <th>BANK INTEREST</th>
            <th style="text-align: right">DEPOSIT VALUE</th>
        </tr>
        </thead>
        <tbody>
        @foreach($selected_group as $group)
            @if($group->deposits(\Input::get('deposit_owner_id'), \Input::get('deposit_bank_id'), \Input::get('status'))->count())
                <tr>
                    <td colspan="8">{{ $group->name }}</td>
                </tr>
                <?php $total_deposit = 0; ?>
                @foreach($group->deposits(\Input::get('deposit_owner_id'), \Input::get('deposit_bank_id'), \Input::get('status'))->get() as $deposit)
                    <?php $total_deposit += $deposit->original_amount; ?>
                    <tr>
                        <td>{{ $deposit->formulir->form_number }}</td>
                        <td>{{ $deposit->owner->name }}</td>
                        <td>{{ date_format_view($deposit->formulir->form_date) }} - {{ date_format_view($deposit->due_date) }}</td>
                        <td>{{ $deposit->bank->name }}</td>
                        <td>{{ $deposit->bankAccount->account_number }} a/n {{ $deposit->bankAccount->account_name }}</td>
                        <td>{{ $deposit->deposit_number }}</td>
                        <td>{{ $deposit->interest_percent }} %</td>
                        <td style="text-align: right">{{ number_format_quantity($deposit->original_amount) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="7"></td>
                    <td style="text-align: right"><b>{{ number_format_quantity($total_deposit) }}</b></td>
                </tr>
            @endif
        @endforeach
        </tbody>
    </table>
</div>
