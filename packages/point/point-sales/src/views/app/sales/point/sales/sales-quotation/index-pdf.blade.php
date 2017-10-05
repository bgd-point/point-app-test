<!DOCTYPE html>
<html>
<head>
    <title>Sales quotation</title>
    <link rel="stylesheet" href="{{asset('core/themes/appui-backend/css/bootstrap.min.css')}}">
</head>
<body>
    <div class="h3 text-center">Sales quotation</div>
    <div class="text-center"></div>
    <br>
    <table class="table tabble-striped table-bordered" cellpadding="0" cellspacing="0" border="0" >
    <thead>
        <tr>
            <th width="30px" class="text-center">No.</th>
            <th width="100px">Form Date</th>
            <th width="125px">Form Number</th>
            <th>Customer</th>
            <th class="text-right">Total</th>
        </tr>
    </thead>
    <tbody>
        <?php $total_sales = 0; $i = 1;?>
        @foreach($list_sales_quotation as $sales_quotation)
        <tr>
            <td>{{$i}}</td>
            <td>{{ date_format_view($sales_quotation->formulir->form_date) }}</td>
            <td>{{ $sales_quotation->formulir->form_number}}</td>
            <td>{{ $sales_quotation->person->codeName }}</td>
            <td class="text-right">{{number_format_price($sales_quotation->total)}}</td>
        </tr>
        <?php $i++;?>
        @endforeach
    </tbody> 
    </table>
</body>
</html>
