<!DOCTYPE html>
<html>
<head>
    <title>Pricing</title>
    <link rel="stylesheet" href="{{asset('core/themes/appui-backend/css/bootstrap.min.css')}}">
</head>
<body>
    <div class="h3 text-center">Point of sales | Pricing</div>
    <div class="text-center"></div>
    <br>
    <table class="table tabble-striped table-bordered" cellpadding="0" cellspacing="0" border="0" >
        <thead>
            <tr>
                <th width="30px" class="text-center">No.</th>
                <th width="125px">Form Date</th>
                <th width="150px">Form Number</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            <?php $i=1;?>
            @foreach($list_pricing as $pricing)
            <tr>
                <td>{{$i}}</td>
                <td>{{ date_format_view($pricing->formulir->form_date) }}</td>
                <td><a href="{{url('sales/point/pos/pricing/'.$pricing->id)}}">{{ $pricing->formulir->form_number }}</a></td>
                <td>{{ $pricing->formulir->notes }}</td>
            </tr>
            @endforeach  
        </tbody> 
    </table>
</body>
</html>
