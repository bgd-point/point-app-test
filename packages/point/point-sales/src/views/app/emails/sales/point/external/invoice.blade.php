<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Email Invoice</title>

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
        Hi, {{ $invoice->person->name }}. You have an email invoice from <br>
        @if($warehouse->store_name)
        <strong style="font-size:18px; text-transform: uppercase;">{{$warehouse->store_name}}</strong> <br/>
        <font style="font-size:12px;text-transform: capitalize;">
            {{$warehouse->address}} <br/>
            {{$warehouse->phone}} 
        </font>
        @else
            Store Name <br/>
            Addess......... <br/>
            Phone Number 
        @endif
        <br/> <br/>

        <table cellpadding="0" cellspacing="0" style="padding: 20px 0;">
            <tr>
                <td style="width: 20%">Form Number</td>
                <td>:</td>
                <td>{{ $invoice->formulir->form_number }}</td>
            </tr>
            <tr>
                <td style="width: 20%">Form Date</td>
                <td>:</td>
                <td>{{ \DateHelper::formatView($invoice->formulir->form_date) }}</td>
            </tr>
            <tr>
                <td style="width: 20%">Due Date</td>
                <td>:</td>
                <td>{{ \DateHelper::formatView($invoice->due_date) }}</td>
            </tr>
            <tr>
                <td style="width: 20%">Customer</td>
                <td>:</td>
                <td>{{ $invoice->person->codeName }}</td>
            </tr>
            <tr>
                <td style="width: 20%">Notes</td>
                <td>:</td>
                <td>{{ $invoice->formulir->notes }}</td>
            </tr>
        </table>

        <table cellpadding="0" cellspacing="0">
            <tr class="heading">
                <td>Item</td>
                <td>Quantity</td>
                <td>Unit</td>
                <td align="right">Price</td>
                <td align="right">Discount</td>
                <td align="right">Total</td>
            </tr>

            @foreach($invoice->items as $items)
                <tr class="item">
                    <td>{{ $items->item->name }}</td>
                    <td>{{ number_format_quantity($items->quantity) }}</td>
                    <td>{{ $items->unit}}</td>
                    <td align="right">{{ number_format_price($items->price) }}</td>
                    <td align="right">{{ number_format_price($items->discount) }}</td>
                    <td align="right">
                        {{ number_format_price($items->quantity * $items->price - ($items->quantity * $items->price * $items->discount/100)) }}
                    </td>
                </tr>
            @endforeach
            <tr>
                <td colspan="5" align="right">Subtotal</td>
                <td align="right">{{ number_format_quantity($invoice->subtotal) }}</td>
            </tr>
            <tr>
                <td colspan="5" align="right">Discount (%)</td>
                <td align="right">{{ number_format_quantity($invoice->discount) }}</td>
            </tr>
            <tr>
                <td colspan="5" align="right">Tax Base</td>
                <td align="right">{{ number_format_quantity($invoice->tax_base) }}</td>
            </tr>
            @if($invoice->type_of_tax != 'non')
            <tr>
                <td colspan="5" align="right">Tax ({{ $invoice->type_of_tax }})
                </td>
                <td align="right">{{ number_format_quantity($invoice->tax) }}</td>
            </tr>
            @endif
            <tr>
                <td colspan="5" align="right">Expedition Fee</td>
                <td align="right">{{ number_format_quantity($invoice->expedition_fee) }}</td>
            </tr>
            <tr>
                <td colspan="5" align="right">Total</td>
                <td align="right">{{ number_format_quantity($invoice->total) }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
