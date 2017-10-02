<!DOCTYPE html>
<html>
<head>
    <title>Delivery Order</title>
    <link rel="stylesheet" href="{{asset('core/themes/appui-backend/css/bootstrap.min.css')}}">
</head>
<body>
    <div class="h3 text-center">Delivery Order</div>
    <div class="text-center"></div>
    <br>
    <table class="table tabble-striped table-bordered" cellpadding="0" cellspacing="0" border="0" >
    <thead>
        <tr>
            <th width="30px" class="text-center">No.</th>
            <th width="100px">Form Date</th>
            <th width="125px">Form Number</th>
            <th>Customer</th>
            <th>Warehouse</th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 1;?>
        @foreach($list_delivery_order as $delivery_order)
        <tr>
            <td>{{$i}}</td>
            <td>{{ date_format_view($delivery_order->formulir->form_date) }}</td>
            <td>{{ $delivery_order->formulir->form_number}}</td>
            <td>
                {{ $delivery_order->person->codeName }}
            </td>
            <td>
                {{ $delivery_order->warehouse->codeName}}
            </td>
        </tr>
        </tr>
        <?php $i++;?>
        @endforeach
    </tbody> 
    </table>
</body>
</html>
