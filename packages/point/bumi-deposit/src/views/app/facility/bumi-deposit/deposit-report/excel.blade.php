<div>
    <table>
        <thead>
        <tr>
            <th>DEPOSIT REPORT</th>
        </tr>
        <tr>
            <th>FORM NUMBER</th>
            <th>OWNER</th>
            <th>FORM DATE</th>
            <th>DUE DATE</th>
            <th>BANK</th>
            <th>BANK ACCOUNT</th>
            <th>NO BILYET</th>
            <th>BANK INTEREST</th>
            <th>BANK INTEREST VALUE</th>
            <th>BANK INTEREST</th>
            <th>TAX INTEREST VALUE</th>
            <th>TOTAL INTEREST</th>
            <th style="text-align: right">DEPOSIT VALUE</th>
        </tr>
        </thead>
        <tbody>
        @foreach($selected_group as $group)
            @if($group->deposits(\Input::get('deposit_owner_id'), \Input::get('deposit_bank_id'), \Input::get('status'))->count())
                <tr>
                    <td colspan="8">{{ $group->name }}</td>
                </tr>
                <?php
                $total_deposit = 0;
                $total_bank_interest = 0;
                $total_tax_interest = 0;
                ?>
                @foreach($group->deposits(\Input::get('deposit_owner_id'), \Input::get('deposit_bank_id'), \Input::get('status'))->get() as $deposit)
                    <?php
                    $bank_interest = 0;
                    $tax_interest = 0;
                    $total_interest = 0;
                    if ($deposit->total_days_in_year > 0) {
                        $bank_interest = ($deposit->deposit_time * $deposit->original_amount * $deposit->interest_percent) / ($deposit->total_days_in_year * 100);
                        $tax_interest = $bank_interest - $deposit->total_interest;
                        $total_bank_interest += $bank_interest;
                        $total_tax_interest += $tax_interest;
                    }
                    $total_deposit += $deposit->original_amount;
                    $total_interest += $deposit->total_interest;
                    ?>
                    <tr>
                        <td>{{ $deposit->formulir->form_number }}</td>
                        <td>{{ $deposit->owner->name }}</td>
                        <td>{{ date('Y-m-d', strtotime($deposit->formulir->form_date)) }}</td>
                        <td>{{ date('Y-m-d', strtotime($deposit->due_date)) }}</td>
                        <td>{{ $deposit->bank->name }}</td>
                        <td>{{ $deposit->bankAccount->account_number }} a/n {{ $deposit->bankAccount->account_name }}</td>
                        <td>{{ $deposit->deposit_number }}</td>
                        <td>{{ number_format_quantity($deposit->interest_percent) }} %</td>
                        <td class="text-right">
                            @if($deposit->total_days_in_year > 0)
                                ({{ number_format_quantity(($deposit->deposit_time * $deposit->original_amount * $deposit->interest_percent) / ($deposit->total_days_in_year * 100)) }})
                            @endif
                        </td>
                        <td>{{ number_format_quantity($deposit->tax_percent) }} %</td>
                        <td class="text-right">
                            @if($deposit->total_days_in_year > 0)
                                ({{ number_format_quantity((($deposit->deposit_time * $deposit->original_amount * $deposit->interest_percent) / ($deposit->total_days_in_year * 100)) - $deposit->total_interest) }})
                            @endif
                        </td>
                        <td class="text-right">{{ number_format_quantity($deposit->total_interest) }}</td>
                        <td style="text-align: right">{{ number_format_quantity($deposit->original_amount) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="7"></td>
                    <td></td>
                    <td class="text-right"><b>{{ number_format_quantity($total_bank_interest) }}</b></td>
                    <td></td>
                    <td class="text-right"><b>{{ number_format_quantity($total_tax_interest) }}</b></td>
                    <td class="text-right"><b>{{ number_format_quantity($total_interest) }}</b></td>
                    <td style="text-align: right"><b>{{ number_format_quantity($total_deposit) }}</b></td>
                </tr>
            @endif
        @endforeach
        </tbody>
    </table>
</div>
