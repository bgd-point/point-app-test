<!DOCTYPE html>
<html>
<head>
    <title>Downpayment</title>
    <link rel="stylesheet" href="{{asset('core/themes/appui-backend/css/bootstrap.min.css')}}">
</head>
<body>
    <div class="h3 text-center">Downpayment</div>
    <div class="text-center"></div>
    <br>
    <table class="table tabble-striped table-bordered" cellpadding="0" cellspacing="0" border="0" >
    <thead>
        <tr>
            <th width="30px" class="text-center">No.</th>
            <th width="100px">Form Date</th>
            <th width="125px">Form Number</th>
            <th>Supplier</th>
            <th>Amount</th>
            <th>Remaining Amount</th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 1;?>
        @foreach($list_downpayment as $downpayment)
            <tr>
                <td>{{$i}}</td>
                <td>{{ date_format_view($downpayment->formulir->form_date) }}</td>
                <td>{{ $downpayment->formulir->form_number}}</td>
                <td>
                    {{ $downpayment->supplier->codeName }}
                </td>
                <td>{{ number_format_quantity($downpayment->amount) }}</td>
                <td>{{ number_format_quantity($downpayment->remaining_amount) }}</td>
            </tr>
        <?php $i++;?>
        @endforeach
    </tbody> 
    </table>
</body>
</html>
