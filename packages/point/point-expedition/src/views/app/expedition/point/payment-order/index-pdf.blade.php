<!DOCTYPE html>
<html>
<head>
    <title>Payment Order</title>
    <link rel="stylesheet" href="{{asset('core/themes/appui-backend/css/bootstrap.min.css')}}">
</head>
<body>
    <div class="h3 text-center">Payment Order</div>
    <div class="text-center"></div>
    <br>
    <table class="table tabble-striped table-bordered" cellpadding="0" cellspacing="0" border="0" >
    <thead>
        <tr>
            <th width="30px" class="text-center">No.</th>
            <th width="100px">Form Date</th>
            <th width="125px">Form Number</th>
            <th>Expedition</th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 1;?>
        @foreach($list_payment_order as $payment_order)
        <tr>
            <td>{{$i}}</td>
            <td>{{ date_format_view($payment_order->formulir->form_date) }}</td>
            <td>{{ $payment_order->formulir->form_number}}</td>
            <td>{{ $payment_order->expedition->codeName }}</td>
        </tr>
        <?php $i++;?>
        @endforeach
    </tbody> 
    </table>
</body>
</html>
