<!DOCTYPE html>
<html>
<head>
    <title>Payment Collection</title>
    <link rel="stylesheet" href="{{asset('core/themes/appui-backend/css/bootstrap.min.css')}}">
</head>
<body>
    <div class="h3 text-center">Payment Collection</div>
    <div class="text-center"></div>
    <br>
    <table class="table tabble-striped table-bordered" cellpadding="0" cellspacing="0" border="0" >
    <thead>
        <tr>
            <th width="30px" class="text-center">No.</th>
            <th width="100px">Form Date</th>
            <th width="125px">Form Number</th>
            <th>Customer</th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 1;?>
        @foreach($list_payment_collection as $payment_collection)x
        <tr>
            <td>{{$i}}</td>
            <td>{{ date_format_view($payment_collection->formulir->form_date) }}</td>
            <td>{{ $payment_collection->formulir->form_number}}</td>
            <td>{{ $payment_collection->person->codeName }}</td>
        </tr>
        <?php $i++;?>
        @endforeach
    </tbody> 
    </table>
</body>
</html>
