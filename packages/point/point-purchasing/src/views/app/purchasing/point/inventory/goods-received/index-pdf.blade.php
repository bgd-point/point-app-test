<!DOCTYPE html>
<html>
<head>
    <title>Goods Received</title>
    <link rel="stylesheet" href="{{asset('core/themes/appui-backend/css/bootstrap.min.css')}}">
</head>
<body>
    <div class="h3 text-center">Goods Received</div>
    <div class="text-center"></div>
    <br>
    <table class="table tabble-striped table-bordered" cellpadding="0" cellspacing="0" border="0" >
    <thead>
        <tr>
            <th width="30px" class="text-center">No.</th>
            <th width="100px">Form Date</th>
            <th width="125px">Form Number</th>
            <th>Supplier</th>
            <th>Warehouse</th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 1;?>
        @foreach($list_goods_received as $goods_received)
        <tr>
            <td>{{$i}}</td>
            <td>{{ date_format_view($goods_received->formulir->form_date) }}</td>
            <td>{{ $goods_received->formulir->form_number}}
            </td>
            <td>
                {{ $goods_received->supplier->codeName }}
            </td>
            <td>{{ $goods_received->warehouse->codeName}}
            </td>
        </tr>
        <?php $i++;?>
        @endforeach
    </tbody> 
    </table>
</body>
</html>
