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
            <th>Form Reference</th>
            <th>Expedition</th>
            <th>Amount</th>
            <th>Remaining <br> Amount</th>
            <th>Notes</th>
        </tr>
    </thead>
    <tbody>
        <?php $total_sales = 0; $i = 1;?>
        @foreach($list_downpayment as $downpayment)
            <?php
            $downpayment_remaining = \Point\Framework\Helpers\ReferHelper::remaining(get_class($downpayment),
                $downpayment->id, $downpayment->amount);
            $expedition_order = '';
            if ($downpayment->expedition_order_id) {
                $expedition_order = Point\PointExpedition\Models\ExpeditionOrder::find($downpayment->expedition_order_id);
            }
            ?>
                <tr>
                    <td>{{$i}}</td>
                    <td>{{ date_format_view($downpayment->formulir->form_date) }}</td>
                    <td>{{ $downpayment->formulir->form_number}}</td>
                    <td>
                        @if($downpayment->expedition_order_id)
                        {{ $expedition_order->formulir->form_number}}
                        @else
                        -
                        @endif
                    </td>
                    <td> {{ $downpayment->expedition->codeName }}
                    </td>
                    <td>{{ number_format_quantity($downpayment->amount) }}</td>
                    <td>{{ number_format_quantity($downpayment_remaining) }}</td>
                    <td>{{ $downpayment->formulir->notes ? $downpayment->formulir->notes : '-'}}</td>
                </tr>
                <?php $i++;?>
            @endforeach
    </tbody> 
    </table>
</body>
</html>
