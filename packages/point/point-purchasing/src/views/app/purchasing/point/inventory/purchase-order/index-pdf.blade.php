<!DOCTYPE html>
<html>
<head>
    <title>Purchase Order</title>
    <link rel="stylesheet" href="{{asset('core/themes/appui-backend/css/bootstrap.min.css')}}">
</head>
<body>
    <div class="h3 text-center">Purchase Order</div>
    <div class="text-center"></div>
    <br>
    <table class="table tabble-striped table-bordered" cellpadding="0" cellspacing="0" border="0" >
    <thead>
        <tr>
            <th width="30px" class="text-center">No.</th>
            <th width="100px">Form Date</th>
            <th width="125px">Form Number</th>
            <th>Supplier</th>
            <th>Total</th>
            <th>Total Remaining Downpayment</th>
            <th>Total Downpayment</th>
        </tr>
    </thead>
    <tbody>
        <?php $total_sales = 0; $i = 1;?>
        @foreach($list_purchase_order as $purchase_order)
        <tr>
            <td>{{$i}}</td>
            <td>{{ date_format_view($purchase_order->formulir->form_date) }}</td>
            <td>{{ $purchase_order->formulir->form_number}}</td>
            <td>{{ $purchase_order->supplier->codeName }}</td>
            <td class="text-right">{{number_format_price($purchase_order->total)}}</td>
            <td class="text-right">{{ number_format_price($purchase_order->getTotalRemainingDownpayment(($purchase_order->id))) }}</td>
            <td class="text-right">{{ number_format_price($purchase_order->getTotalDownpayment(($purchase_order->id))) }}</td>
            
        </tr>
        <?php $i++;?>
        @endforeach
    </tbody> 
    </table>
</body>
</html>
