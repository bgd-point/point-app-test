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
            text-align: left;
            padding: 0;
            border-spacing: 0;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table td {
            padding: 5px;
            vertical-align: top;
            white-space: nowrap; 
        }

        table tr.top table td {
            padding-bottom: 20px;
        }

        table tr.top table td.title {
            font-size: 45px;
            line-height: 45px;
            color: #333;
        }

        table tr.information table td {
            padding-bottom: 40px;
        }

        table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }

        table tr.details td, table tr.empty-row td {
            padding-bottom: 20px;
        }

        table tr.item td {
            /* border-bottom: 1px solid #eee; */
        }

        table tr.item.last td {
            border-bottom: none;
        }

        table tr.total td {
            border-top: 2px solid #ddd;
            font-weight: bold;
        }
        table tr.total.last td {
            border-bottom: 2px solid #ddd;
        }

        .overview {
            padding: 20px 0;
        }
        .overview td:first-child {
            width: 150px;
        }
        .overview td:nth-child(2) {
            width: 20px;
            text-align: right;
        }
        .main td {
            border-left: 1px solid #ddd;
            border-right: 1px solid #ddd;
        }
        .main tr.no-side-border td {
            border-left: 0;
            border-right: 0;
        }
        .main td.payment {
            border-left: 2px solid #ddd;
            border-right: 2px solid #ddd;
            background-color: #F8f8f8;
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
        .allow-wrap {
            white-space: normal;
        }

        /* @media only screen and (max-width: 600px) {
            table tr.top table td {
                width: 100%;
                display: block;
                text-align: center;
            }

            table tr.information table td {
                width: 100%;
                display: block;
                text-align: center;
            }
        } */
    </style>
</head>

<body>
    <table><tr><td class="allow-wrap">
        Hi, you have a request approval purchase payment order from <strong>{{ $requester }}</strong>. We would like to inform the details as follows :
    </td></tr></table>

   @foreach($list_data as $payment_order)
        <table class="overview">
            <tr>
                <td>Form Number</td>
                <td>:</td>
                <td>
                    <a href="{{ $url . '/purchasing/point/service/payment-order/'.$payment_order->id }}">
                        {{ $payment_order->formulir->form_number }}
                    </a>
                </td>
            </tr>
            <tr>
                <td>Created by</td>
                <td>:</td>
                <td>{{ $payment_order->formulir->createdBy->name }}</td>
            </tr>
            <tr>
                <td>Form Date</td>
                <td>:</td>
                <td>{{ \DateHelper::formatView($payment_order->formulir->form_date, true) }}</td>
            </tr>
            <tr>
                <td>Supplier</td>
                <td>:</td>
                <td>{!! get_url_person($payment_order->person_id) !!}</td>
            </tr>
            <tr>
                <td>Notes</td>
                <td>:</td>
                <td>{{ ucfirst($payment_order->notes) }}</td>
            </tr>
        </table>

        <?php
            $total_payment_order = 0;
        ?>

        <table class="main">
            <tr class="heading">
                <td class="allow-wrap">Invoice Number</td>
                <td>Service & Item</td>
                <td>Allocation</td>
                <td class="text-right";>Price</td>
                <td class="text-right";>Discount (%)</td>
                <td class="text-right";>Subtotal</td>
                <td class="text-right">Payment Amount</td>
                <td class="text-right">Remaining</td>
            </tr>
            @foreach($payment_order->details as $payment_order_detail)
                <?php
                    $model = $payment_order_detail->reference->formulirable_type;
                    $reference = $model::find($payment_order_detail->reference->formulirable_id);
                ?>
                @if (get_class($reference) == 'Point\PointPurchasing\Models\Service\Invoice')
                    <?php
                        $invoice = $reference;
                        $total_payment_order += $payment_order_detail->amount;

                        $invoice_remaining = \Point\Framework\Helpers\ReferHelper::remaining(get_class($invoice), $invoice->id, $invoice->total);
                    ?>
                    
                    @foreach($invoice->services as $index=>$service)
                        <?php
                            $subtotal = ($service->quantity * $service->price);
                            $subtotal -= ($service->quantity * $service->price * $service->discount / 100);
                        ?>
                        <tr class="item">
                            <td class="allow-wrap">
                                @if($index === 0)
                                    <a href="{{ $url . '/purchasing/point/service/invoice/'.$invoice->id }}">
                                        {{ $invoice->formulir->form_number }}
                                    </a>
                                @endif
                            </td>
                            <td class="allow-wrap">
                                {{ ucfirst($service->service->name) }}
                                (QTY: {{ number_format_quantity($service->quantity) }})
                                <br>
                                {{ ucfirst($service->service_notes) }}
                            </td>
                            <td>{{ ucwords($service->allocation->name) }}</td>
                            <td class="text-right">{{ number_format_price($service->price) }}</td>
                            <td class="text-right">{{ number_format_quantity($service->discount) }}</td>
                            <td class="text-right">{{ number_format_price($subtotal) }}</td>
                            <td class="payment"></td>
                            <td></td>
                        </tr>
                    @endforeach

                    @foreach($invoice->items as $index=>$item)
                        <?php
                            $subtotal = ($item->quantity * $item->price);
                            $subtotal -= ($item->quantity * $item->price * $item->discount / 100);
                        ?>
                        <tr class="item">
                            <td></td>
                            <td class="allow-wrap">
                                {{ ucfirst($item->item->name) }}
                                (QTY : {{ number_format_quantity($item->quantity) }})
                                <br>
                                {{ ucfirst($item->item_notes) }}
                            </td>
                            <td>{{ ucwords($item->allocation->name) }}</td>
                            <td class="text-right">{{ number_format_price($item->price) }}</td>
                            <td class="text-right">{{ number_format_quantity($item->discount) }}</td>
                            <td class="text-right">{{ number_format_price($subtotal) }}</td>
                            <td class="payment"></td>
                            <td></td>
                        </tr>
                    @endforeach

                    @if($invoice->discount > 0)
                        <tr class="item">
                            <td></td>
                            <td>
                                Discount {{ number_format_quantity($invoice->discount) }}%
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="text-right">
                                -{{ number_format_price($subtotal * $invoice->discount) }}
                            </td>
                            <td class="payment"></td>
                            <td></td>
                        </tr>
                    @endif

                    @if($invoice->tax > 0)
                        <tr class="item">
                            <td></td>
                            <td>
                                Tax ({{ ucfirst($invoice->type_of_tax) }})
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="text-right">
                                {{ number_format_price($invoice->tax) }}
                            </td>
                            <td class="payment"></td>
                            <td></td>
                        </tr>
                    @endif

                    <tr class="total">
                        <td></td>
                        <td class="text-right" colspan="4">
                            Invoice Total
                        </td>
                        <td class="text-right">
                            {{ number_format_price($invoice->total) }}
                        </td>
                        <td class="payment"></td>
                        <td></td>
                    </tr>

                    @if($invoice->total - $invoice_remaining - $payment_order_detail->amount > 0)
                        <tr class="total">
                            <td></td>
                            <td class="text-right" colspan="4">
                                Previously Paid
                            </td>
                            <td class="text-right">
                                {{ number_format_price($invoice->total - $invoice_remaining - $payment_order_detail->amount) }}
                            </td>
                            <td class="payment"></td>
                            <td></td>
                        </tr>
                        <tr class="total">
                            <td></td>
                            <td class="text-right" colspan="4">
                                Invoice Remaining
                            </td>
                            <td class="text-right">
                                {{ number_format_price($invoice_remaining + $payment_order_detail->amount) }}
                            </td>
                            <td class="payment"></td>
                            <td></td>
                        </tr>
                    @endif
                    
                    <tr class="total last">
                        <td></td>
                        <td class="text-right" colspan="4">
                            Payment Amount
                        </td>
                        <td></td>
                        <td class="text-right payment">
                            {{ number_format_price($payment_order_detail->amount) }}
                        </td>
                        <td class="text-right">
                            {{ number_format_price($invoice_remaining) }}
                        </td>
                    </tr>
                @endif
            @endforeach
            <?php
                $downpayment_counter = 0;
            ?>
            @foreach($payment_order->details as $payment_order_detail)
                <?php
                    $model = $payment_order_detail->reference->formulirable_type;
                    $reference = $model::find($payment_order_detail->reference->formulirable_id);
                ?>
                @if(get_class($reference) == "Point\PointPurchasing\Models\Service\Downpayment")
                    @if($downpayment_counter === 0)
                        <tr class="empty-row">
                            <td colspan="8"></td>
                        </tr>
                        <tr class="heading">
                            <td></td>
                            <td>Date</td>
                            <td colspan="3">Note</td>
                            <td class="text-right">Amount</td>
                            <td></td>
                            <td></td>
                        </tr>
                        <?php $downpayment_counter++ ?>
                    @endif
                    <tr class="item" style="border-bottom: 2px solid #ddd">
                        <td class="allow-wrap">
                            <a href="{{ $url . '/purchasing/point/service/downpayment/'.$reference->id }}">
                                {{ $reference->formulir->form_number }}
                            </a>
                        </td>
                        <td>{{ \DateHelper::formatView($reference->formulir->form_date, true) }}</td>
                        <td colspan="3">{{ $reference->notes }}</td>
                        <?php
                            $dp_remaining = \Point\Framework\Helpers\ReferHelper::remaining(get_class($reference), $reference->id, $reference->amount);
                        ?>
                        <td class="text-right">
                            {{ number_format_price($dp_remaining-$payment_order_detail->amount) }}
                        </td>
                        <td class="text-right payment">
                            {{ number_format_price($payment_order_detail->amount) }}
                        </td>
                        <td class="text-right">
                            {{ number_format_price($dp_remaining) }}
                        </td>
                    </tr>
                @endif
            @endforeach

						@if(count($payment_order->others) > 0)
								<tr class="empty-row" style="border-bottom: 2px solid #ddd">
										<td colspan="8"></td>
								</tr>
								<tr class="heading" style="border-bottom: 2px solid #ddd">
										<td colspan="8">Others</td>
								</tr>
								@foreach($payment_order->others as $other)
										<tr class="item" style="border-bottom: 2px solid #ddd">
											<td colspan="3">{{ $other->other_notes }}</td>
											<td>{{ $other->allocation->name }}</td>
											<td colspan="2">{{ $other->coa->name }}</td>
												<td class="text-right">{{ number_format_price($other->amount) }}</td>
												<td></td>
										</tr>
								@endforeach
						@endif

            <tr class="empty-row">
                <td colspan="8"></td>
            </tr>
            <tr class="total last">
                <td class="text-right" colspan="6">Amount to be paid</td>
                <td class="text-right payment">
                    {{ number_format_quantity($payment_order->total_payment)}}</td>
                <td></td>
            </tr>

            <tr class="no-side-border empty-row">
                <td colspan="8"></td>
            </tr>
            <tr class="no-side-border">
                <td class="text-right" colspan="8" style="padding: 0;">
                <a href="{{ $url . '/formulir/'.$payment_order->formulir_id.'/approval/check-status/'.$token }}"
                    class="btn btn-check"> Check </a>
                <a href="{{ $url . '/purchasing/point/service/payment-order/'.$payment_order->id.'/approve?token='.$token }}"
                    class="btn btn-success"> Approve </a>
                <a href="{{ $url . '/purchasing/point/service/payment-order/'.$payment_order->id.'/reject?token='.$token }}"
                    class="btn btn-danger"> Reject </a>
                </td>
            </tr>
        </table>
    @endforeach

    @if(count($list_data) > 1)
    <table><tr><td style="padding: 0;">
        <a href="{{ $url . '/purchasing/point/service/payment-order/approve-all/?formulir_id='.$array_formulir_id.'&token='.$token }}">
            <input type="button" class="btn btn-primary" value="Approve All">
        </a>
        <a href="{{ $url . '/purchasing/point/service/payment-order/reject-all/?formulir_id='.$array_formulir_id.'&token='.$token }}">
            <input type="button" class="btn btn-warning" value="Reject All">
        </a>
    </td></tr></table>
    @endif
</body>
</html>
