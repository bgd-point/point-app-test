<style>
    @media print {
        * {
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }
        tr, th, td {
            border: 1px solid #000 !important;
            border-collapse: collapse;
            border-spacing: 0;
            padding: 3px 5px;
        }
        .text-right {
            text-align: right;
        }
    }
</style>

<div class="page">
    <h3>This stock report printed at {{ date('d F Y') }}</h3>
    <table>
        <?php
        $groups = $list_stock_shares->groupBy('shares_id')->get();
        ?>
        <tbody>
        @foreach($groups as $report_group)
            <?php
            $acc_remaining_quantity=0;
            $acc_subtotal=0;
            $acc_total=0;
            ?>

            <tr>
                <td class="text-center" colspan="10">Shares "{{ $report_group->shares->name }}"</td>
            </tr>
            <tr>
                <td style="font-weight: bold">Form Date</td>
                <td style="font-weight: bold">Group</td>
                <td style="font-weight: bold">Owner</td>
                <td style="font-weight: bold">Broker</td>
                <td class="text-right" style="font-weight: bold">Fee</td>
                <td class="text-right" style="font-weight: bold">Quantity</td>
                <td class="text-right" style="font-weight: bold">Price</td>
                <td class="text-right" style="font-weight: bold">Ex Sale</td>
                <td class="text-right" style="font-weight: bold">Total</td>
                <td class="text-right" style="font-weight: bold">Total + Fee</td>
            </tr>
            @foreach(\Point\BumiShares\Models\Stock::where(function($q) use ($report_group, $group) {
                $q->where('shares_id','=',$report_group->shares_id);
                if($group) {
                    $q->where('owner_group_id','=',$group->id);
                }
            })->get() as $stock)

                <?php
                $subtotal = $stock->remaining_quantity * $stock->price;
                $total = $stock->remaining_quantity * $stock->price + ($stock->remaining_quantity * $stock->price * $stock->fee / 100);
                $acc_remaining_quantity += $stock->remaining_quantity; // total remaining quantity
                $acc_subtotal += $subtotal; // subtotal is price * quantity
                $acc_total += $total; // total is subtotal + fee
                ?>
                <tr>
                    <td>{{ date_format_view($stock->date) }}</td>
                    <td>{{ $stock->ownerGroup->name }}</td>
                    <td>{{ $stock->owner->name }}</td>
                    <td>{{ $stock->broker->name }}</td>
                    <td class="text-right">{{ number_format_quantity($stock->reference($stock->formulir_id)->fee) }}</td>
                    <td class="text-right">{{ number_format_quantity($stock->remaining_quantity) }}</td>
                    <td class="text-right">{{ number_format_quantity($stock->price) }}</td>
                    <td class="text-right">{{ number_format_quantity($stock->average_price) }}</td>
                    <td class="text-right">{{ number_format_quantity($subtotal) }}</td>
                    <td class="text-right">{{ number_format_quantity($total) }}</td>
                </tr>
            @endforeach
            <?php $average_price = $acc_total / $acc_remaining_quantity; ?>
            <tr>
                <td colspan="5"></td>
                <td class="text-right"><b>{{ number_format_quantity($acc_remaining_quantity) }}</b></td>
                <td class="text-right"></td>
                <td></td>
                <td class="text-right"><b>{{ number_format_quantity($acc_subtotal) }}</b></td>
                <td class="text-right"><b>{{ number_format_quantity($acc_total) }}</b></td>
            </tr>
            <tr>
                <td colspan="10">
                    <?php
                    $total_quantity += $acc_remaining_quantity;
                    $total_value += $acc_total;
                    $estimate_price = 0;
                    $estimate = Point\BumiShares\Models\SellingPrice::where('shares_id', '=', $stock->shares_id)->first();

                    if ($estimate) {
                        $estimate_price = $estimate->price;
                    }
                    $total_sales = $acc_remaining_quantity * $estimate_price;
                    $profit_and_loss = $total_sales - $acc_total;
                    $estimation_of_selling_value += $total_sales;
                    $estimation_of_profit_and_loss += $profit_and_loss;
                    ?>

                    <b>Estimation of Profit and Loss</b> <br/>
                    Estimation of Selling Price : {{ number_format_quantity($estimate_price) }} <br/>
                    Estimation of Sales : {{ number_format_quantity($total_sales) }} <br/>
                    Profit & Loss : {{ number_format_quantity($profit_and_loss) }}

                </td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr>
            <td colspan="5" class="text-right" style="font-size: 20px;font-weight: bold;">Total Quantity</td>
            <td class="text-right" style="font-size: 20px;font-weight: bold;">{{ number_format_quantity($total_quantity) }}</td>
            <td colspan="3" class="text-right" style="font-size: 20px;font-weight: bold;">Total + Fee</td>
            <td class="text-right" style="font-size: 20px;font-weight: bold;">{{ number_format_quantity($total_value) }}</td>
        </tr>
        <tr>
            <td colspan="9" class="text-right">Total Estimation of Sales</td>
            <td class="text-right">{{ number_format_quantity($estimation_of_selling_value) }}</td>
        </tr>
        <tr>
            <td colspan="9" class="text-right">Profit And Loss</td>
            <td class="text-right">{{ number_format_quantity($estimation_of_profit_and_loss) }}</td>
        </tr>
        </tfoot>
    </table>

</div>
