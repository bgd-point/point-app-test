<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Approval Request</title>

    <style>
        a {
            text-decoration: none;
        }

        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, .15);
            font-size: 16px;
            line-height: 24px;
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #555;
        }

        table {
            width: 100%;
            line-height: inherit;
        }

        table.info {
            padding: 20px 0;
        }
        table.info td:nth-child(1) {
            width: 20%;
        }
        table.info td:nth-child(2) {
            text-align: right;
        }

        td {
            padding: 5px;
            vertical-align: top;
        }

        tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }

        tr.item td {
            border-bottom: 1px solid #eee;
        }

        .btn {
            display: inline-block;
            font-weight: 300;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            cursor: pointer;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            border: 1px solid transparent;
            padding: .5rem 1rem;
            font-size: 1rem;
            border-radius: .25rem;
        }

        .btn-check {
            color: #fff;
            background-color: #000;
            border-color: #000;
        }

        .btn-success {
            color: #fff;
            background-color: #5cb85c;
            border-color: #5cb85c;
        }

        .btn-danger {
            color: #fff;
            background-color: #d9534f;
            border-color: #d9534f;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>
<div class="invoice-box">
    Hi, you have a request approval purchase order from {{ $username }}. We would like to inform the details as follows :

   @foreach($list_data as $purchase_order)
        <table class="info">
            <tr>
                <td>Form Number</td>
                <td>:</td>
                <td>
                    <a href="{{ url('purchasing/point/purchase-order/'.$purchase_order->id) }}">
                        {{ $purchase_order->formulir->form_number}}
                    </a>
                </td>
            </tr>
            <tr>
                <td>Form Date</td>
                <td>:</td>
                <td>{{ \DateHelper::formatView($purchase_order->formulir->form_date) }}</td>
            </tr>
            <tr>
                <td>Supplier</td>
                <td>:</td>
                <td>{{ $purchase_order->supplier->codeName }}</td>
            </tr>
            <tr>
                <td>Cash Purchase</td>
                <td>:</td>
                <td>{{ $purchase_order->is_cash ? 'Yes' : 'No' }}</td>
            </tr>
            <tr>
                <td>Include Expedition</td>
                <td>:</td>
                <td>{{ $purchase_order->include_expedition ? 'Yes' : 'No' }}</td>
            </tr>
        </table>

        <table class="details" cellpadding="0" cellspacing="0">
            <tr class="heading">
                <td>Item</td>
                <td class="text-right">Quantity</td>
                <td class="text-right">Price</td>
                <td class="text-right">Disc (%)</td>
                <td>Allocation</td>
                <td class="text-right">Total</td>
            </tr>

           @foreach($purchase_order->items as $purchase_order_item)
                <tr class="item">
                    <td>
                        {{$purchase_order_item->item->codeName}}
                    </td>
                    <td class="text-right">
                        {{number_format_quantity($purchase_order_item->quantity). ' ' .$purchase_order_item->unit}}
                    </td>
                    <td class="text-right">
                        {{number_format_quantity($purchase_order_item->price)}}
                    </td>
                    <td class="text-right">
                        {{number_format_quantity($purchase_order_item->discount)}}
                    </td>
                    <td>
                        {{$purchase_order_item->allocation->name}}
                    </td>
                    <td class="text-right">
                        {{number_format_quantity($purchase_order_item->quantity * $purchase_order_item->price - ($purchase_order_item->quantity * $purchase_order_item->price * $purchase_order_item->discount / 100))}}
                    </td>
                </tr>
            @endforeach


            @if($purchase_order->tax > 0)
                <tr class="heading">
                    <td colspan="5" class="text-right">
                        Tax ({{$purchase_order->type_of_tax}})
                    </td>
                    <td class="text-right">
                        {{number_format_price($purchase_order->tax)}}
                    </td>
                </tr>
            @endif
            
            <tr class="heading">
                <td colspan="5" class="text-right">
                    Total
                </td>
                <td class="text-right">
                    {{number_format_price($purchase_order->total)}}
                </td>
            </tr>
            <tr>
                <td colspan="6" >
                    <a href="{{ $url . '/formulir/'.$purchase_order->formulir_id.'/approval/check-status/'.$token }}">
                        <input type="button" class="btn btn-check" value="Check">
                    </a>
                    <a href="{{ $url . '/purchasing/point/purchase-order/'.$purchase_order->id.'/approve?token='.$token }}">
                        <input type="button" class="btn btn-success" value="Approve">
                    </a>
                    <a href="{{ $url . '/purchasing/point/purchase-order/'.$purchase_order->id.'/reject?token='.$token }}">
                        <input type="button" class="btn btn-danger" value="Reject">
                    </a>
                </td>
            </tr>
        </table>
    @endforeach
    @if($list_data->count() > 1)
    <br>
    <a href="{{ $url . '/purchasing/point/purchase-order/approve-all/?formulir_id='.$array_formulir_id.'&token='.$token }}">
        <input type="button" class="btn btn-primary" value="Approve All">
    </a>
    <a href="{{ $url . '/purchasing/point/purchase-order/reject-all/?formulir_id='.$array_formulir_id.'&token='.$token }}">
        <input type="button" class="btn btn-warning" value="Reject All">
    </a>
    @endif
</div>
</body>
</html>
