<!DOCTYPE html>
<html>
<head>
    <title>Purchase Requisition</title>
    <link rel="stylesheet" href="{{asset('core/themes/appui-backend/css/bootstrap.min.css')}}">
</head>
<body>
    <div class="h3 text-center">Purchase Requisition</div>
    <div class="text-center"></div>
    <br>
    <table class="table tabble-striped table-bordered" cellpadding="0" cellspacing="0" border="0" >
    <thead>
        <tr>
            <th width="30px" class="text-center">No.</th>
            <th width="100px">Form Date</th>
            <th width="125px">Form Number</th>
            <th>Supplier</th>
        </tr>
    </thead>
    <tbody>
        <?php $total_sales = 0; $i = 1;?>
        @foreach($list_purchase_requisition as $purchase_requisition)
        <tr>
            <td>{{$i}}</td>
            <td>{{ date_format_view($purchase_requisition->formulir->form_date) }}</td>
            <td>{{ $purchase_requisition->formulir->form_number}}</td>
            <td>{{ $purchase_requisition->employee->codeName }}</td>
        </tr>
        <?php $i++;?>
        @endforeach
    </tbody> 
    </table>
</body>
</html>
