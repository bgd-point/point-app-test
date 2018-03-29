<!DOCTYPE html>
<html>
<head>
    <title>{{ucfirst($type)}} Report</title>
    <link rel="stylesheet" href="{{asset('core/themes/appui-backend/css/bootstrap.min.css')}}">
</head>
<body>
    <div class="h3 text-center">{{ucfirst($type)}} Report</div>
    <div class="text-center"></div>
    <br>
    <table class="table tabble-striped table-bordered" cellpadding="0" cellspacing="0" border="0" >
        <thead>
            <tr>
                <th>No. </th>    
                <th>form date</th>
                <th>form number</th>
                <th>person</th>
                <th>notes</th>
                <th class="text-right">received</th>
                <th class="text-right">disbursed</th>
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
                <td>{{ $report->formulir->form_number}}</td>
                <td>{{ strtoupper($report->person->codeName) }}</td>
                <td>{{ strtoupper($report_detail->notes_detail) }}</td>
                <td class="text-right">
                    @if($report->payment_flow == 'in')
                    <b>{{ number_format_price($report_detail->amount) }}</b>
                    <?php $total_received += $report_detail->amount; ?>
                    @else
                    0.00
                    @endif
                </td>
                <td class="text-right">
                    @if($report->payment_flow == 'out')
                    <b>{{ number_format_price($report_detail->amount) }}</b>
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
            <td colspan="5" class="text-right">Total</td>
            <td class="text-right"><strong>{{ number_format_price($total_received) }}</strong></td>
            <td class="text-right"><strong>{{ number_format_price($total_disbursed) }}</strong></td>
        </tr>
        <tr>
            <td colspan="5" class="text-right">Opening Balance</td>
            <td class="text-right"><strong>{{ number_format_price($opening_balance) }}</strong></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="5" class="text-right">Ending Balance</td>
            <td></td>
            <td class="text-right"><strong>{{ number_format_price($opening_balance + $total_received + $total_disbursed) }}</strong></td>
        </tr>
        </tbody> 
    </table>
</body>
</html>
