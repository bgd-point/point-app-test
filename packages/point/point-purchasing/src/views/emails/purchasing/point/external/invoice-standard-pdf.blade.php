<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>RED POINT</title>
    <style>
        .clearfix:after {
            content: "";
            display: table;
            clear: both;
        }

        a {
            color: #5D6975;
            text-decoration: underline;
        }

        body {
            position: relative;
            width: 100%;
            height: auto;
            margin: 0 auto;
            color: #001028;
            background: #FFFFFF;
            font-family: Arial, sans-serif;
            font-size: 12px;
            font-family: Arial;
        }

        header {
            padding: 10px 0;
            margin-bottom: 30px;
        }

        #logo {
            margin-bottom: 10px;
        }

        #logo img {
            width: 90px;
        }

        h1 {
            border-top: 1px solid  #5D6975;
            border-bottom: 1px solid  #5D6975;
            color: #5D6975;
            font-size: 2.4em;
            line-height: 1.4em;
            font-weight: normal;
            text-align: center;
            margin: 0 0 20px 0;
            background: url(dimension.png);
        }

        #project {
            display: inline-block;
        }

        #project span {
            color: #5D6975;
            text-align: right;
            width: 52px;
            margin-right: 10px;
        }

        #project div,
        #company div {
            white-space: nowrap;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            margin-bottom: 20px;
        }

        table tr:nth-child(2n-1) td {
            background: #F5F5F5;
        }

        table th,
        table td {
            text-align: center;
        }

        table th {
            padding: 5px 8px;
            color: #5D6975;
            border-bottom: 1px solid #C1CED9;
            white-space: nowrap;
            font-weight: normal;
        }

        table .service,
        table .desc {
            text-align: left;
        }

        table td {
            padding: 8px;
            text-align: right;
        }

        table td.service,
        table td.desc {
            vertical-align: top;
        }

        table td.unit,
        table td.qty,
        table td.total {
            font-size: 1.2em;
        }

        table td.grand {
            border-top: 1px solid #5D6975;;
        }

        #notices .notice {
            color: #5D6975;
            font-size: 1.2em;
        }

        footer {
            color: #5D6975;
            width: 100%;
            height: 30px;
            position: absolute;
            bottom: 0;
            border-top: 1px solid #C1CED9;
            padding: 8px 0;
            text-align: center;
        }
    </style>
</head>
<body>
<header class="clearfix">
    <div class="row">
        @if(url_logo())
            <div id="logo" style="float: left;">
                <img src="{{ public_path('app/'.app('request')->project->url.'/logo/logo.png') }}">
            </div>
        @endif
        <div id="" style="text-align: right">
            <div><span>#</span> {{ $invoice->formulir->form_number }}</div>
            <div><b>Created :</b> {{ \DateHelper::formatView($invoice->formulir->form_date) }}</div>
            <div><b>Due Date :</b> {{ \DateHelper::formatView($invoice->due_date) }}</div>
            <div><b style="color:RED">UNPAID</b></div>
        </div>
    </div>
    <br style="clear: both;">
    <div id="project" style="float:left">
        <div><b>{{strtoupper($warehouse->store_name) ? : ''}}</b></div>
        <div>{{strtoupper($warehouse->address) ? : ''}}</div>
        <div>{{$warehouse->phone ? : ''}}</div>
    </div>
    <div id="" style="text-align: right;margin-left: 50px;">
        <div><b>{{ strtoupper($invoice->person->name) }}</b></div>
        <div>{{ strtoupper($invoice->person->address) }}</div>
        <div>{{ $invoice->person->phone }}</div>
    </div>
</header>
<main>
    <table>
        <thead>
            <tr>
                <th style="text-align: left">ITEM DESCRIPTION</th>
                <th style="text-align: right">PRICE</th>
                <th style="text-align: right">QTY</th>
                <th style="text-align: right">DISCOUNT</th>
                <th style="text-align: right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
        @foreach($invoice->items as $invoice_item)
            <tr>
                <td class="text-left desc">{{ $invoice_item->item->name }} {{ $invoice_item->item_notes }}</td>
                <td class="text-right">{{ number_format_price($invoice_item->price, 0) }}</td>
                <td class="text-right">{{ number_format_price($invoice_item->quantity, 0) }} {{ $invoice_item->unit }}</td>
                <td class="text-right">{{ number_format_price($invoice_item->discount, 0) }}</td>
                <td class="text-right">{{ number_format_price(($invoice_item->quantity * $invoice_item->price) - ($invoice_item->quantity * $invoice_item->price * $invoice_item->discount / 100), 0) }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        @if($invoice->subtotal != $invoice->total)
            <tr>
                <td colspan="4" class="text-right">SUB TOTAL</td>
                <td class="text-right total">{{ number_format_quantity($invoice->subtotal) }}</td>
            </tr>
        @endif
        @if($invoice->discount > 0)
            <tr>
                <td colspan="4" class="text-right">DISCOUNT (%)</td>
                <td class="text-right total">{{ number_format_quantity($invoice->discount) }}</td>
            </tr>
        @endif
        @if($invoice->type_of_tax != 'non')
            <tr>
                <td colspan="4" class="text-right">TAX BASE</td>
                <td class="text-right total">{{ number_format_quantity($invoice->tax_base) }}</td>
            </tr>
            <tr>
                <td colspan="4" class="text-right">GST ({{ ucwords($invoice->type_of_tax) }})</td>
                <td class="text-right total">{{ number_format_quantity($invoice->tax) }}</td>
            </tr>
        @endif
        <tr>
            <td colspan="4" class="text-right">TOTAL</td>
            <td class="text-right total">{{ number_format_quantity($invoice->total, 0) }}</td>
        </tr>
        </tfoot>
    </table>

    <hr style="color:#e4e4e4">

    <div id="notices">
        <div class="notice" style="font-size: 12px;text-align: center">
            <blockquote>{{ (get_end_notes('purchase service invoice')) }}</blockquote>
        </div>
    </div>

</main>
<footer>
    Invoice was created on a computer and is valid without the signature and seal.
</footer>
</body>
</html>
