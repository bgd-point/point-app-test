<!DOCTYPE html>
<html>
<head>
    <title>Point of sales</title>
    <link rel="stylesheet" href="{{asset('core/themes/appui-backend/css/bootstrap.min.css')}}">
</head>
<body>
    <div class="h3 text-center">Point of sales</div>
    <div class="text-center"></div>
    <br>
    <table class="table tabble-striped table-bordered" cellpadding="0" cellspacing="0" border="0" >
    <thead>
        <tr>
            <th width="30px" class="text-center">No.</th>
            <th width="100px">Form Number</th>
            <th width="125px">Form Date</th>
            <th>Customer</th>
            <th class="text-right">Total</th>
        </tr>
    </thead>
    <tbody>
        <?php $total_sales = 0; $i = 1;?>
        @foreach($list_sales as $sales)
        <tr>
            <td class="text-center">{{$i}}</td>
            <td>{{ date_format_view($sales->formulir->form_date, true) }}</td>
            <td>{{ $sales->formulir->form_number }}</td>
            <td>{{ $sales->customer->codeName }}</td>
            <td class="text-right">{{ number_format_accounting($sales->total) }}</td>
        </tr>
        <?php $total_sales += $sales->total;?>
        <?php $i++;?>
        @endforeach
    </tbody> 
    <tfoot>
        <tr>
            <td colspan="4" class="text-right"><strong>Total</strong></td>
            <td class="text-right"><strong>{{ number_format_accounting($total_sales) }}</strong></td>
        </tr>
        </tfoot>
</table>
</body>
</html>
