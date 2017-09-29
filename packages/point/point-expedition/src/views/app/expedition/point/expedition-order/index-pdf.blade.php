<!DOCTYPE html>
<html>
<head>
    <title>Expedition Order</title>
    <link rel="stylesheet" href="{{asset('core/themes/appui-backend/css/bootstrap.min.css')}}">
</head>
<body>
    <div class="h3 text-center">Expedition Order</div>
    <div class="text-center"></div>
    <br>
    <table class="table tabble-striped table-bordered" cellpadding="0" cellspacing="0" border="0" >
    <thead>
        <tr>
            <th width="30px" class="text-center">No.</th>
            <th width="100px">Form Date</th>
            <th width="125px">Form Number</th>
            <th>Expedition</th>
            <th>Fee</th>
            <th>Reference</th>
            <th>Notes</th>
        </tr>
    </thead>
    <tbody>
        <?php $total_sales = 0; $i = 1;?>
        @foreach($list_expedition_order as $expedition_order)
        <tr>
            <td>{{$i}}</td>
            <td>{{ date_format_view($expedition_order->formulir->form_date) }}</td>
            <td>{{ $expedition_order->formulir->form_number}}</td>
            <td>{{$expedition_order->expedition->codeName }}</td>
            <td>{{ number_format_quantity($expedition_order->expedition_fee, 2) }}</td>
            <td>{{ $expedition_order->reference()->formulir->form_number }}</td>
            <td>{{ $expedition_order->formulir->notes ? $expedition_order->formulir->notes : '-'}}</td>
        </tr>
        <?php $i++;?>
        @endforeach
    </tbody> 
    </table>
</body>
</html>
