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

        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
        }

        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
            
        }

        .invoice-box table tr td:nth-child(2) {
            text-align: right;
        }

        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.top table td.title {
            font-size: 45px;
            line-height: 45px;
            color: #333;
        }

        .invoice-box table tr.information table td {
            padding-bottom: 40px;
        }

        .invoice-box table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }

        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
        }

        .invoice-box table tr.item.last td {
            border-bottom: none;
        }

        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
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

        @media only screen and (max-width: 600px) {
            .invoice-box table tr.top table td {
                width: 100%;
                display: block;
                text-align: center;
            }

            .invoice-box table tr.information table td {
                width: 100%;
                display: block;
                text-align: center;
            }
        }
    </style>
</head>

<body>
<div class="invoice-box">
    Hi, you have an Request approval payment order from {{ $username }}. We would like to inform the
    details as follows :

   @foreach($list_data as $payment_order)

        <table cellpadding="0" cellspacing="0" style="padding: 20px 0;">
            <tr>
                <td style="width: 20%">
                    Form Number
                </td>
                <td>
                    :
                </td>
                <td>
                    {{ $payment_order->formulir->form_number }}</a>
                </td>
            </tr>
            <tr>
                <td style="width: 20%">
                    Form Date
                </td>
                <td>
                    :
                </td>
                <td>
                    {{ \DateHelper::formatView($payment_order->formulir->form_date) }}
                </td>
            </tr>
            <tr>
                <td style="width: 20%">
                    Supplier
                </td>
                <td>
                    :
                </td>
                <td>
                    {{ $payment_order->supplier->codeName }}
                </td>
            </tr>
        </table>

        <table cellpadding="0" cellspacing="0">
            <tr class="heading">
                <td>Date</td>
                <td>
                    Notes
                </td>
                <td>
                    Amount
                </td>
            </tr>

           @foreach($payment_order->details as $payment_order_detail)
                <?php
                $model = $payment_order_detail->reference->formulirable_type;
                $reference = $model::find($payment_order_detail->reference->formulirable_id);
                ?>

                @if (get_class($reference) == 'Point\PointPurchasing\Models\Inventory\Invoice')
                    @foreach($reference->items as $invoice_service)
                        <tr class="item">
                            <td style="text-align: left">
                                {{date_format_view($invoice_service->invoice->formulir->form_date)}}
                            </td>
                            <td style="text-align: left">
                                {{$invoice_service->item->codeName}} (Qty: {{number_format_quantity($invoice_service->quantity)}})
                            </td>
                            <td style="text-align: right">
                                {{number_format_quantity($payment_order_detail->amount)}}
                            </td>
                        </tr>
                    @endforeach
                @endif
            @endforeach
            <tr></tr>
            @if(count($payment_order->others) > 0)
            <tr class="heading">
                <td colspan="2">
                    Notes
                </td>
                <td>
                    Amount
                </td>
            </tr>
            @endif
            @foreach($payment_order->others as $payment_order_other)
            <tr class="item">
                <td colspan="2">
                    {{ $payment_order_other->coa->account }} | {{$payment_order_other->other_notes}}
                </td>
                <td >
                    {{number_format_quantity($payment_order_other->amount)}}
                </td>
            </tr>
            @endforeach
            <tr></tr>
            <tr class="heading">
                <td colspan="2"></td>
                <td>
                    {{number_format_quantity($payment_order->total_payment)}}
                </td>
            </tr>
            <tr>
                <td colspan="6" >
                    <a href="{{ $url . '/formulir/'.$payment_order->formulir_id.'/approval/check-status/'.$token }}"><input
                                type="button" class="btn btn-check" value="Check"></a>
                    <a href="{{ $url . '/purchasing/point/payment-order/'.$payment_order->id.'/approve?token='.$token }}"><input
                                type="button" class="btn btn-success" value="Approve"></a>
                    <a href="{{ $url . '/purchasing/point/payment-order/'.$payment_order->id.'/reject?token='.$token }}"><input
                                type="button" class="btn btn-danger" value="Reject"></a>
                </td>
            </tr>
        </table>
    @endforeach
    @if($list_data->count() > 1)
    <br>
    <a href="{{ $url . '/purchasing/point/payment-order/approve-all/?formulir_id='.$array_formulir_id.'&token='.$token }}">
        <input type="button" class="btn btn-primary" value="Approve All">
    </a>
    <a href="{{ $url . '/purchasing/point/payment-order/reject-all/?formulir_id='.$array_formulir_id.'&token='.$token }}">
        <input type="button" class="btn btn-warning" value="Reject All">
    </a>
    @endif
</div>
</body>
</html>
