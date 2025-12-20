<tr>
    <td>{{ $allocation->name }}</td>
</tr>
<table class="table table-striped table-bordered">
    <thead>
    <tr>
        <th>Reference</th>
        <th>Date</th>
        <th>Description</th>
        <th>In</th>
        <th>Out</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $total_in = 0;
    $total_out = 0;
    ?>
    @foreach($list_report as $report)
        <?php
        if ($report->amount > 0) {
            $total_in += $report->amount;
        } else {
            $total_out += abs($report->amount);
        }
        ?>
        <tr>
            <td>{!! formulir_url($report->formulir) !!}</td>
            <td>{{ date_format_view($report->formulir->form_date) }}</td>
            <td>{{ $report->notes }}</td>
            <td class="text-right">{{ $report->amount >= 0 ? $report->amount : ''}}</td>
            <td class="text-right">{{ $report->amount < 0 ? abs($report->amount) : ''}}</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td class="text-right"><b>{{ number_format_price($total_in, 0) }}</b></td>
        <td class="text-right"><b>{{ number_format_price($total_out, 0) }}</b></td>
    </tr>
    </tfoot>
</table>
