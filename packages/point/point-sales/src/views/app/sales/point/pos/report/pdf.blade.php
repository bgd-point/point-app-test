<!DOCTYPE html>
<html>
<head>
    <title></title>
    <link rel="stylesheet" href="{{asset('core/themes/appui-backend/css/bootstrap.min.css')}}">
</head>
<body>
    <div class="h3 text-center">Sales Report</div>
    <div class="text-center">Period : {{ $period }}</div>
    <br>
    <table class="table tabble-striped table-bordered" cellpadding="0" cellspacing="0" border="0" >
        <thead>
        <tr>
            <th width="30px" class="text-center">No.</th>
            <th width="100px">Form Number</th>
            <th width="125px">Form Date</th>
            <th>Customer</th>
            <th>Sales</th>
            <th class="text-right">Total</th>
        </tr>
        </thead>
        <tbody>
        <?php $total_sales = 0; $i=0?>
        @foreach($list_sales as $sales)
            <tr id="list-{{$sales->id}}" @if($sales->formulir->form_status == -1) style="text-decoration: line-through;" @endif>
                <td class="text-center">{{++$i}}</td>
                <td>{{ $sales->formulir->form_number }}</td>
                <td>{{ date_format_view($sales->formulir->form_date, true) }}</td>
                <td>{{ $sales->customer->codeName }}</td>
                <td>{{ $sales->formulir->createdBy->name }}</td>
                <td class="text-right">{{ number_format_accounting($sales->total) }}</td>
            </tr>
            @if($sales->formulir->form_status != -1)
                <?php $total_sales += $sales->total;?>
            @endif
        @endforeach
        </tbody>
        <tfoot>
        <tr>
            <td colspan="5" class="text-right"><strong>Total</strong></td>
            <td class="text-right"><strong>{{ number_format_accounting($total_sales) }}</strong></td>
        </tr>
        </tfoot>
    </table>
</body>
</html>
