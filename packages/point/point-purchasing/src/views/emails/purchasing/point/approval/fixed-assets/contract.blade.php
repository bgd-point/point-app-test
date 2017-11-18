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
    Hello Mr/Mrs/Ms/ {{ $username }},<br/>You have an Request approval contract form. We would like to inform the
    details as follows :

   @foreach($list_data as $contract)
        <table cellpadding="0" cellspacing="0" style="padding: 20px 0;">
            <tr>
                <td style="width: 20%">
                    Form Number
                </td>
                <td>
                    :
                </td>
                <td>
                    {{ $contract->formulir->form_number }}</a>
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
                    {{ \DateHelper::formatView($contract->formulir->form_date) }}
                </td>
            </tr>
            <tr>
                <td style="width: 20%">Assets Account</td>
                <td>:</td>
                <td>{{$contract->coa->name}}</td>
            </tr>
            <tr>
                <td style="width: 20%">Acquisition date</td>
                <td>:</td>
                <td>{{date_format_view($contract->formulir->form_date)}}</td>
            </tr>
            <tr>
                <td style="width: 20%">Asset name</td>
                <td>:</td>
                <td>{{$contract->codeName}}</td>
            </tr>
            <tr>
                <td style="width: 20%">Useful life</td>
                <td>:</td>
                <td>{{number_format_quantity($contract->useful_life, 0)}} Month</td>
            </tr>
            <tr>
                <td style="width: 20%">Salvage Value</td>
                <td>:</td>
                <td>{{number_format_quantity($contract->salvage_value, 0)}}</td>
            </tr>
            <tr>
                <td style="width: 20%">Date Purchased</td>
                <td>:</td>
                <td>{{date_format_view($contract->date_purchased)}}</td>
            </tr>
            <tr>
                <td style="width: 20%">Supplier</td>
                <td>:</td>
                <td>{{$contract->supplier->codeName}}</td>
            </tr>
            <tr>
                <td style="width: 20%">Quantity</td>
                <td>:</td>
                <td>{{$contract->quantity}} {{$contract->unit}}</td>
            </tr>
            <tr>
                <td style="width: 20%">Price</td>
                <td>:</td>
                <td>{{number_format_quantity($contract->price, 0)}}</td>
            </tr>
            <tr>
                <td style="width: 20%">Total price</td>
                <td>:</td>
                <td>{{number_format_quantity($contract->total_price, 0)}}</td>
            </tr>
            <tr>
                <td style="width: 20%">Total paid</td>
                <td>:</td>
                <td>{{number_format_quantity($contract->total_paid, 0)}}</td>
            </tr>
            <tr>
                <td style="width: 20%">Depreciation</td>
                <td>:</td>
                <td>{{number_format_quantity($contract->depreciation)}} Month</td>
            </tr>
            <tr>
                <td style="width: 20%">Notes</td>
                <td>:</td>
                <td>{{$contract->formulir->notes}}</td>
            </tr>
        </table>
        <table cellpadding="0" cellspacing="0">
            <tr class="heading">
                <td>Description</td>
                <td>Date Purchased</td>
            </tr>
            @foreach($contract->details as $contract_detail)
            <tr class="item">
                <td>{{$contract_detail->reference->formulir->form_number}} {{$contract_detail->reference->formulir->notes}} #{{number_format_quantity($contract_detail->reference->total_price)}}</td>
                <td>{{date_format_view($contract_detail->reference->date_purchased)}}</td>
            </tr>
            @endforeach
        </table>
        <table cellpadding="0" cellspacing="0">
            <tr>
                <td colspan="6" >
                    <a href="{{ $url . '/formulir/'.$contract->formulir_id.'/approval/check-status/'.$token }}"><input
                                type="button" class="btn btn-check" value="Check"></a>
                    <a href="{{ $url . '/purchasing/point/fixed-assets/contract/'.$contract->id.'/approve?token='.$token }}"><input
                                type="button" class="btn btn-success" value="Approve"></a>
                    <a href="{{ $url . '/purchasing/point/fixed-assets/contract/'.$contract->id.'/reject?token='.$token }}"><input
                                type="button" class="btn btn-danger" value="Reject"></a>
                </td>
            </tr>
        </table>
    @endforeach
    @if($list_data->count() > 1)
    <br>
    <a href="{{ $url . '/purchasing/point/fixed-assets/contract/approve-all/?formulir_id='.$array_formulir_id.'&token='.$token }}">
        <input type="button" class="btn btn-primary" value="Approve All">
    </a>
    <a href="{{ $url . '/purchasing/point/fixed-assets/contract/reject-all/?formulir_id='.$array_formulir_id.'&token='.$token }}">
        <input type="button" class="btn btn-warning" value="Reject All">
    </a>
    @endif
</div>
</body>
</html>
