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
    Hi, you have a purchase order request approval from <strong>{{ $requester }}</strong>. We would like to inform the details as follows :

   @foreach($list_purchase_order as $purchase_order)
        <table cellpadding="0" cellspacing="0" style="padding: 20px 0;">
            <tr>
                <td style="width: 20%">
                    Form Number
                </td>
                <td>
                    :
                </td>
                <td>
                    <a href="{{ $url . '/purchasing/point/service/purchase-order/' . $purchase_order->id }}">
                        {{ $purchase_order->formulir->form_number }}
                    </a>
                </td>
            </tr>
            <tr>
                <td style="width: 20%">
                    Created By
                </td>
                <td>
                    :
                </td>
                <td>
                    {{ $purchase_order->formulir->createdBy->name }}
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
                    {{ \DateHelper::formatView($purchase_order->formulir->form_date) }}
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
                    {{ $purchase_order->person->codeName }}
                </td>
            </tr>
            <tr>
                <td style="width: 20%">
                    Total
                </td>
                <td>
                    :
                </td>
                <td>
                    {{ number_format_price($purchase_order->total) }}
                </td>
            </tr>
            <tr>
                <td style="width: 20%">
                    Notes
                </td>
                <td>
                    :
                </td>
                <td>
                    {{ ucfirst($purchase_order->formulir->notes) }}
                </td>
            </tr>
				</table>
				
				<table cellpadding="0" cellspacing="0">
            <?php
                $column_discount = false;
                foreach($purchase_order->services as $service) {
                    if($service->discount > 0) {
                        $column_discount = true;
                        break;
                    }
                }
                $total_service = 0;
                $colspan_needed = $column_discount ? 4 : 3;
            ?>
            <tr class="heading">
                <td>Service</td>
                <td>Allocation</td>
                <td style="text-align: right;">Price</td>

                @if($column_discount)
                    <td style="text-align: right;">Disc (%)</td>
                @endif
                
                <td style="text-align: right;">Subtotal</td>
            </tr>
            
            @foreach($purchase_order->services as $service)
                <?php
                    $subtotal = ($service->quantity * $service->price);
                    $subtotal -= ($service->quantity * $service->price * $service->discount / 100);
                    $total_service += $subtotal;
                ?>
                <tr class="item">
                    <td>
                        {{ ucfirst($service->service->name) }}
                        (QTY : {{ number_format_quantity($service->quantity) }})
                        <br>
                        {{ ucfirst($service->service_notes) }}
                    </td>
                    <td>
											{{ ucwords($service->allocation->name) }}
										</td>
										<td style="text-align: right;">
											{{ number_format_quantity($service->price) }}
										</td>
                    
                    @if($column_discount)
												<td style="text-align: right;">
													{{ number_format_quantity($service->discount) }}
												</td>
                    @endif

										<td style="text-align: right;">
											{{ number_format_quantity($subtotal) }}
										</td>
                </tr>
            @endforeach

            @if($purchase_order->discount > 0 || $purchase_order->tax > 0)
                <tr class="heading">
                    <td style="text-align: right;" colspan="{{$colspan_needed}}">
                        Subtotal
                    </td>
                    <td style="text-align: right;">
                        {{ number_format_quantity($purchase_order->subtotal) }}
                    </td>
                </tr>
            @endif

            @if($purchase_order->discount > 0)
                <tr class="heading">
                    <td style="text-align: right;" colspan="{{$colspan_needed}}">
                        Discount
                    </td>
                    <td style="text-align: right;">
                        {{ number_format_quantity($purchase_order->discount) }}
                    </td>
                </tr>
            @endif

            @if($purchase_order->tax > 0)
                @if($purchase_order->type_of_tax == "include")
                    <tr class="heading">
                        <td style="text-align: right;" colspan="{{$colspan_needed}}">
                            Tax Base
                        </td>
                        <td style="text-align: right;">
                            {{ number_format_quantity($purchase_order->tax_base) }}
                        </td>
                    </tr>
                @endif
                
                <tr class="heading">
                    <td style="text-align: right;" colspan="{{$colspan_needed}}">
                        Tax
                    </td>
                    <td style="text-align: right;">
                        {{ number_format_quantity($purchase_order->tax) }}
                    </td>
                </tr>
            @endif

            <tr class="heading">
                <td style="text-align: right;" colspan="{{$colspan_needed}}">
                    Total
                </td>
                <td style="text-align: right;">
                    {{ number_format_quantity($purchase_order->total) }}
                </td>
            </tr>
        </table>

        <table cellpadding="0" cellspacing="0">
            <tr>
                <td colspan="6" >
										<a href="{{ $url . '/formulir/'.$purchase_order->formulir_id.'/approval/check-status/'.$token }}">
											<input type="button" class="btn btn-check" value="Check">
										</a>
										<a href="{{ $url . '/purchasing/point/service/purchase-order/'.$purchase_order->id.'/approve?token='.$token }}">
											<input type="button" class="btn btn-success" value="Approve">
										</a>
										<a href="{{ $url . '/purchasing/point/service/purchase-order/'.$purchase_order->id.'/reject?token='.$token }}">
											<input type="button" class="btn btn-danger" value="Reject">
										</a>
                </td>
            </tr>
        </table>
    @endforeach
    @if($list_purchase_order->count() > 1)
    <br>
    <a href="{{ $url . '/purchasing/point/service/purchase-order/approve-all/?formulir_id='.$array_formulir_id.'&token='.$token }}">
        <input type="button" class="btn btn-primary" value="Approve All">
    </a>
    <a href="{{ $url . '/purchasing/point/service/purchase-order/reject-all/?formulir_id='.$array_formulir_id.'&token='.$token }}">
        <input type="button" class="btn btn-warning" value="Reject All">
    </a>
    @endif
</div>
</body>
</html>