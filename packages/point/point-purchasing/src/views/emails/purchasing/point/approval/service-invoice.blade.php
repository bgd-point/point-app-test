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
    Hi, you have a request approval purchase service invoice from <strong>{{ $requester }}</strong>. We would like to inform the details as follows :

    <?php $total_to_pay = 0; ?>

    @foreach($list_invoice as $invoice)
        <?php $total_to_pay += $invoice->total; ?>

        <table cellpadding="0" cellspacing="0" style="padding: 20px 0;">
            <tr>
                <td style="width: 20%">
                    Form Number
                </td>
                <td>
                    :
                </td>
                <td>
                    <a href="{{ $url . '/purchasing/point/service/invoice/'.$invoice->id }}">
                        {{ $invoice->formulir->form_number}}
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
                    {{ $invoice->formulir->createdBy->name }}
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
                    {{ \DateHelper::formatView($invoice->formulir->form_date) }}
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
                    {!! get_url_person($invoice->person->id) !!}
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
                    {{ ucfirst($invoice->formulir->notes) }}
                </td>
            </tr>
        </table>

        <table cellpadding="0" cellspacing="0">
            <?php
                $column_discount = false;
                foreach($invoice->services as $service) {
                    if($service->discount > 0) {
                        $column_discount = true;
                        break;
                    }
                }
                if(!$column_discount) {
                    foreach ($invoice->items as $item) {
                        if($item->discount > 0) {
                            $column_discount = true;
                            break;
                        }
                    }
                }
                $total_service = 0;
                $total_item = 0;
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
            
            @foreach($invoice->services as $service)
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
                    <td>{{ ucwords($service->allocation->name) }}</td>
                    <td style="text-align: right;">{{ number_format_quantity($service->price) }}</td>
                    
                    @if($column_discount)
                        <td style="text-align: right;">{{ number_format_quantity($service->discount) }}</td>
                    @endif

                    <td style="text-align: right;">{{ number_format_quantity($subtotal) }}</td>
                </tr>
            @endforeach

            @if(count($invoice->items) > 0)
                <tr class="heading">
                    <td style="text-align: right;" colspan="{{$colspan_needed}}">
                        Total Service
                    </td>
                    <td style="text-align: right;">
                        {{ number_format_quantity($total_service) }}
                    </td>
                </tr>

                <tr class="heading">
                    <td>Item</td>
                    <td>Allocation</td>
                    <td style="text-align: right;">Price</td>

                    @if($column_discount)
                        <td style="text-align: right;">Disc (%)</td>
                    @endif
                    
                    <td style="text-align: right;">Subtotal</td>
                </tr>

                @foreach($invoice->items as $item)
                    <?php
                        $subtotal = ($item->quantity * $item->price);
                        $subtotal -= ($item->quantity * $item->price * $item->discount / 100);
                        $total_item += $subtotal;
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

                        @if($column_discount)
                            <td style="text-align: right;">{{ number_format_quantity($item->discount) }}</td>
                        @endif
                        
                        <td style="text-align: right;">{{ number_format_quantity($subtotal) }}</td>
                    </tr>
                @endforeach

                <tr class="heading">
                    <td style="text-align: right;" colspan="{{$colspan_needed}}">
                        Total Item
                    </td>
                    <td style="text-align: right;">
                        {{ number_format_quantity($total_item) }}
                    </td>
                </tr>
            @endif

            @if($invoice->discount > 0 || $invoice->tax > 0)
                <tr class="heading">
                    <td style="text-align: right;" colspan="{{$colspan_needed}}">
                        Subtotal
                    </td>
                    <td style="text-align: right;">
                        {{ number_format_quantity($invoice->subtotal) }}
                    </td>
                </tr>
            @endif

            @if($invoice->discount > 0)
                <tr class="heading">
                    <td style="text-align: right;" colspan="{{$colspan_needed}}">
                        Discount
                    </td>
                    <td style="text-align: right;">
                        {{ number_format_quantity($invoice->discount) }}
                    </td>
                </tr>
            @endif

            @if($invoice->tax > 0)
                @if($invoice->type_of_tax == "include")
                    <tr class="heading">
                        <td style="text-align: right;" colspan="{{$colspan_needed}}">
                            Tax Base
                        </td>
                        <td style="text-align: right;">
                            {{ number_format_quantity($invoice->tax_base) }}
                        </td>
                    </tr>
                @endif
                
                <tr class="heading">
                    <td style="text-align: right;" colspan="{{$colspan_needed}}">
                        Tax
                    </td>
                    <td style="text-align: right;">
                        {{ number_format_quantity($invoice->tax) }}
                    </td>
                </tr>
            @endif

            <tr class="heading">
                <td style="text-align: right;" colspan="{{$colspan_needed}}">
                    Total Invoice
                </td>
                <td style="text-align: right;">
                    {{ number_format_quantity($invoice->total) }}
                </td>
            </tr>
        </table>

        <table cellpadding="0" cellspacing="0">
            <tr>
                <td colspan="6">
                    <a href="{{ $url . '/formulir/'.$invoice->formulir_id.'/approval/check-status/'.$token }}"><input
                                type="button" class="btn btn-check" value="Check"></a>
                    <a href="{{ $url . '/purchasing/point/service/invoice/'.$invoice->id.'/approve?token='.$token }}"><input
                                type="button" class="btn btn-success" value="Approve"></a>
                    <a href="{{ $url . '/purchasing/point/service/invoice/'.$invoice->id.'/reject?token='.$token }}"><input
                                type="button" class="btn btn-danger" value="Reject"></a>
                </td>
            </tr>
        </table>
    @endforeach

    @if($list_invoice->count() > 1)
        <div style="text-align: right;">
            <h2>TOTAL TO PAY : {{ number_format_quantity($total_to_pay) }}</h2>
        </div>

        <a href="{{ $url . '/purchasing/point/service/invoice/approve-all/?formulir_id='.$array_formulir_id.'&token='.$token }}">
            <input type="button" class="btn btn-primary" value="Approve All">
        </a>
        <a href="{{ $url . '/purchasing/point/service/invoice/reject-all/?formulir_id='.$array_formulir_id.'&token='.$token }}">
            <input type="button" class="btn btn-warning" value="Reject All">
        </a>
    @endif
</body>
</html>