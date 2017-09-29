<!DOCTYPE html>
<html>
<head>
    <title>Purchase Report</title>
    <link rel="stylesheet" href="{{asset('core/themes/appui-backend/css/bootstrap.min.css')}}">
</head>
<body>
    <div class="h3 text-center">Purchase Report</div>
    <div class="text-center"></div>
    <br>
    <table class="table tabble-striped table-bordered" cellpadding="0" cellspacing="0" border="0" >
    <thead>
        <tr>
            <th width="30px" class="text-center">No.</th>
            <th width="100px">Form Date</th>
            <th width="125px">Form Number</th>
            <th>Supplier</th>
            <th>Item</th>
            <th class="text-center">Quantity</th>
            <th class="text-right">Price</th>
            <th class="text-right">Total</th>
        </tr>
    </thead>
    <tbody>
        <?php $total_value = 0; $i=1;?>
        @foreach($list_report as $report)
            <?php
            $total = $report->price * $report->quantity;
            $total_value += $total;
            ?>
            <tr>    
                <td>{{$i}}</td>
                <td>{{date_format_view($report->invoice->formulir->form_date)}}</td>
                <td>{{ $report->invoice->formulir->form_number}}</td>
                <td>{{ $report->invoice->supplier->codeName }}</td>
                <td>{{$report->item->codeName}}</td>
                <td class="text-center">{{number_format_quantity($report->quantity, 0) . ' ' . $report->unit}}</td>
                <td class="text-right">{{number_format_quantity($report->price)}}</td>
                <td class="text-right">{{number_format_quantity($total)}}</td>
            </tr>
        @endforeach
        <tr>
            <td class="text-right" colspan="8"><h4><strong>{{number_format_quantity($total_value)}}</strong></h4></td>
        </tr>
    </tbody> 
    </table>
</body>
</html>
