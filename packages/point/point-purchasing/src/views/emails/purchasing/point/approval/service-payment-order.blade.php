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
    Hi, you have a request approval purchase payment order from {{ $username }}. We would like to inform the
    details as follows :
    
    <?php
        $total_all_payment_order = 0;
    ?>

   @foreach($list_data as $payment_order)
        <table cellpadding="0" cellspacing="0" style="padding: 20px 0;">
            <tr>
                <td style="width: 20%;">
                    Form Number
                </td>
                <td>
                    :
                </td>
                <td>
                    <a href="{{ url('purchasing/point/service/payment-order/'.$payment_order->id) }}">
                        {{ $payment_order->formulir->form_number }}
                    </a>
                </td>
            </tr>
            <tr>
                <td style="width: 20%;">
                    Form Date
                </td>
                <td>
                    :
                </td>
                <td>
                    {{ \DateHelper::formatView($payment_order->formulir->form_date, true) }}
                </td>
            </tr>
            <tr>
                <td style="width: 20%;">
                    Supplier
                </td>
                <td>
                    :
                </td>
                <td>
                    {!! get_url_person($payment_order->person_id) !!}
                </td>
            </tr>
            <tr>
                <td style="width: 20%;">
                    Notes
                </td>
                <td>
                    :
                </td>
                <td>
                    {{ ucfirst($payment_order->notes) }}
                </td>
            </tr>
        </table>

        <?php
            $total_payment_order = 0;
        ?>

        <table cellpadding="0" cellspacing="0">
            @foreach($payment_order->details as $payment_order_detail)
                <?php
                    $model = $payment_order_detail->reference->formulirable_type;
                    $reference = $model::find($payment_order_detail->reference->formulirable_id);
                ?>
                @if (get_class($reference) == 'Point\PointPurchasing\Models\Service\Invoice')
                    <?php
                        $invoice = $reference;
                        $total_payment_order += $payment_order_detail->amount;
                        $total_all_payment_order += $total_payment_order;

                        $invoice_remaining = \Point\Framework\Helpers\ReferHelper::remaining(get_class($invoice), $invoice->id, $invoice->total);
                    ?>

                    <tr class="heading">
                        <td colspan="6">
                            INVOICE :
                            <a href="{{url('purchasing/point/service/invoice/'.$invoice->formulir_id)}}">
                                {{ $invoice->formulir->form_number }}
                            </a>
                        </td>
                    </tr>
                        
                    <tr class="heading">
                        <td>Service & Item</td>
                        <td>Allocation</td>
                        <td style="text-align: right;">Price</td>
                        <td style="text-align: right;">Disc (%)</td>
                        <td style="text-align: right;">Subtotal</td>
                        <td style="text-align: right;">Payment</td>
                    </tr>
                    
                    @foreach($invoice->services as $service)
                        <?php
                            $subtotal = ($service->quantity * $service->price);
                            $subtotal -= ($service->quantity * $service->price * $service->discount / 100);
                        ?>
                        <tr class="item">
                            <td>
                                {{ ucfirst($service->service->name) }}
                                (QTY: {{ number_format_quantity($service->quantity) }})
                                <br>
                                {{ ucfirst($service->service_notes) }}
                            </td>
                            <td>{{ ucwords($service->allocation->name) }}</td>
                            <td style="text-align: right; ">{{ number_format_quantity($service->price) }}</td>
                            <td style="text-align: right;">{{ number_format_quantity($service->discount) }}</td>
                            <td style="text-align: right;">{{ number_format_quantity($subtotal) }}</td>
                            <td></td>
                        </tr>
                    @endforeach

                    @foreach($invoice->items as $item)
                        <?php
                            $subtotal = ($item->quantity * $item->price);
                            $subtotal -= ($item->quantity * $item->price * $item->discount / 100);
                        ?>
                        <tr class="item">
                            <td>
                                {{ ucfirst($item->item->name) }}
                                (QTY : {{ number_format_quantity($item->quantity) }})
                                <br>
                                {{ ucfirst($item->item_notes) }}
                            </td>
                            <td>{{ ucwords($item->allocation->name) }}</td>
                            <td style="text-align: right;">{{ number_format_quantity($item->price) }}</td>
                            <td style="text-align: right;">{{ number_format_quantity($item->discount) }}</td>
                            <td style="text-align: right;">{{ number_format_quantity($subtotal) }}</td>
                            <td></td>
                        </tr>
                    @endforeach

                    <tr class="heading">
                        <td style="text-align: right;" colspan="4">
                            Invoice Subtotal
                        </td>
                        <td style="text-align: right;">
                            {{ number_format_quantity($invoice->subtotal) }}
                        </td>
                        <td></td>
                    </tr>

                    @if($invoice->discount > 0)
                        <tr class="heading">
                            <td style="text-align: right;" colspan="4">
                                Invoice Discount
                            </td>
                            <td style="text-align: right;">
                                {{ number_format_quantity($invoice->discount) }}
                            </td>
                            <td></td>
                        </tr>
                    @endif

                    @if($invoice->tax > 0)
                        <tr class="heading">
                            <td style="text-align: right;" colspan="4">
                                Tax ({{ ucfirst($invoice->type_of_tax) }})
                            </td>
                            <td style="text-align: right;">
                                {{ number_format_quantity($invoice->tax) }}
                            </td>
                            <td></td>
                        </tr>
                    @endif

                    <tr class="heading">
                        <td style="text-align: right;" colspan="4">
                            Invoice Total
                        </td>
                        <td style="text-align: right;">
                            {{ number_format_quantity($invoice->total) }}
                        </td>
                        <td style="text-align: right;">
                            @if($invoice->total - $invoice_remaining - $payment_order_detail->amount == 0)
                                {{ number_format_quantity($payment_order_detail->amount) }}
                            @endif
                        </td>
                    </tr>
                    @if($invoice->total - $invoice_remaining - $payment_order_detail->amount > 0)
                        <tr class="heading">
                            <td style="text-align: right;" colspan="4">
                                Previously Paid
                            </td>
                            <td style="text-align: right;">
                                {{ number_format_quantity($invoice->total - $invoice_remaining - $payment_order_detail->amount) }}
                            </td>
                            <td></td>
                        </tr>
                        <tr class="heading">
                            <td style="text-align: right;" colspan="4">
                                Invoice Remaining
                            </td>
                            <td style="text-align: right;">
                                {{ number_format_quantity($invoice_remaining + $payment_order_detail->amount) }}
                            </td>
                            <td style="text-align: right;">
                                {{ number_format_quantity($payment_order_detail->amount) }}
                            </td>
                        </tr>
                    @endif
                @elseif(get_class($reference) == "Point\PointPurchasing\Models\Service\Downpayment")
                    <tr class="heading">
                        <td style="text-align: right;" colspan="5">
                            Downpayment
                            <a href="{{url('purchasing/point/service/downpayment/'.$reference->formulir_id)}}">
                                {{ $reference->formulir->form_number }}
                            </a>
                        </td>
                        <td style="text-align: right;">
                            -{{ number_format_quantity($reference->amount)}}
                        </td>
                    </tr>
                @endif
            @endforeach
        </table>

        <div style="text-align: right;">
            <h2> Payment Order Total: {{ number_format_quantity($payment_order->total_payment)}}</h2>
            <a href="{{ $url . '/formulir/'.$payment_order->formulir_id.'/approval/check-status/'.$token }}"
                class="btn btn-check"> Check </a>
            <a href="{{ $url . '/purchasing/point/service/payment-order/'.$payment_order->id.'/approve?token='.$token }}"
                class="btn btn-success"> Approve </a>
            <a href="{{ $url . '/purchasing/point/service/payment-order/'.$payment_order->id.'/reject?token='.$token }}"
                class="btn btn-danger"> Reject </a>
         </div>
    @endforeach

    @if(count($list_data) > 1)
        <a href="{{ $url . '/purchasing/point/service/payment-order/approve-all/?formulir_id='.$array_formulir_id.'&token='.$token }}">
            <input type="button" class="btn btn-primary" value="Approve All">
        </a>
        <a href="{{ $url . '/purchasing/point/service/payment-order/reject-all/?formulir_id='.$array_formulir_id.'&token='.$token }}">
            <input type="button" class="btn btn-warning" value="Reject All">
        </a>
    @endif
</div>
</body>
</html>
