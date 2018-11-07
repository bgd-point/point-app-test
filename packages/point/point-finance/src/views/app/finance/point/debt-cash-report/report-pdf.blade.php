<!DOCTYPE html>
<html>
<head>
    <title>{{strtoupper($type)}} REPORT</title>
    <link rel="stylesheet" href="{{asset('core/themes/appui-backend/css/bootstrap.min.css')}}">
</head>
<body>
    <div class="h3 text-center">{{strtoupper($type)}} REPORT</div>
    <div class="text-center"></div>
    <br>
    <table class="table tabble-striped table-bordered" cellpadding="0" cellspacing="0" border="0" >
        <thead>
            <tr>
                <th>NO. </th>
                <th>FORM DATE</th>
                <th>FORM NUMBER <br/> VENDOR</th>
                <th>NOTES</th>
                <th class="text-right">RECEIVED</th>
                <th class="text-right">DISBURSED</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $total_received = 0;
        $total_disbursed = 0;
        $i=1;
        ?>
        @foreach($list_report as $report)
            @foreach($report->detail as $report_detail)
            <tr>
                <td>
                    {{$i}}
                </td>
                <td>{{ date_format_view($report->formulir->form_date) }}</td>
                <td>{{ $report->formulir->form_number}} <br/> <b>{{ strtoupper($report->person->name) }}</b></td>
                <td>{{ strtoupper($report_detail->notes_detail) }}</td>
                <td class="text-right">
                    @if($report->payment_flow == 'in')
                    <b>{{ number_format_price($report_detail->amount, 0) }}</b>
                    <?php $total_received += $report_detail->amount; ?>
                    @else
                    0.00
                    @endif
                </td>
                <td class="text-right">
                    @if($report->payment_flow == 'out')
                    <b>{{ number_format_price($report_detail->amount, 0) }}</b>
                    <?php $total_disbursed += $report_detail->amount * -1; ?>
                    @else
                    0.00
                    @endif
                </td>
            </tr>
            <?php $i++;?>
            @endforeach
        @endforeach  
        <tr>
            <td colspan="4" class="text-right">Total</td>
            <td class="text-right"><strong>{{ number_format_price($total_received, 0) }}</strong></td>
            <td class="text-right"><strong>{{ number_format_price($total_disbursed, 0) }}</strong></td>
        </tr>
        <tr>
            <td colspan="4" class="text-right">Opening Balance</td>
            <td class="text-right"><strong>{{ number_format_price($opening_balance, 0) }}</strong></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="4" class="text-right">Ending Balance</td>
            <td></td>
            <td class="text-right"><strong>{{ number_format_price($opening_balance + $total_received + $total_disbursed, 0) }}</strong></td>
        </tr>
        </tbody> 
    </table>
</body>
</html>
