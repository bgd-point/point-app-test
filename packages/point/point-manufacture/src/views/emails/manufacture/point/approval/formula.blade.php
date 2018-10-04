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
    Hello Mr/Mrs/Ms/ {{ $approver->name }},<br/>You have an approval request for manufacture formula from <strong>{{ $requester }}</strong>. We would like to inform the details as follows :

    @foreach($list_data as $formula)

        <table cellpadding="0" cellspacing="0" style="padding: 20px 0;">
            <tr>
                <td style="width: 20%">
                    Form Number
                </td>
                <td>
                    :
                </td>
                <td>
                    <a href="{{ $url . '/manufacture/point/formula/'.$formula->id }}">{{ $formula->formulir->form_number }}</a>
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
                    {{ $formula->formulir->createdBy->name }}
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
                    {{ \DateHelper::formatView($formula->formulir->form_date) }}
                </td>
            </tr>
            <tr>
                <td style="width: 20%">
                    Process
                </td>
                <td>
                    :
                </td>
                <td>
                    {{ $formula->process->name }}
                </td>
            </tr>
            <tr>
                <td style="width: 20%">
                    Name
                </td>
                <td>
                    :
                </td>
                <td>
                    {{ $formula->name }}
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
                    {{ $formula->notes }}
                </td>
            </tr>
        </table>

        <table cellpadding="0" cellspacing="0">
            <tr class="heading">
                <td colspan="4">Finished Goods</td>
            </tr>
            <tr class="heading">
                <td>ITEM</td>
                <td>QUANTITY</td>
                <td>UNIT</td>
                <td>WAREHOUSE</td>
            </tr>


            @foreach($formula->product as $finished_goods)
                <tr class="item">
                    <td>
                        {{$finished_goods->item->codeName}}
                    </td>
                    <td>
                        {{number_format_quantity($finished_goods->quantity)}}
                    </td>
                    <td>
                        {{$finished_goods->unit}}
                    </td>
                    <td>
                        {{$finished_goods->warehouse->name}}
                    </td>
                </tr>
            @endforeach
            <tr></tr>
            <tr class="heading">
                <td colspan="4">Raw Materials</td>
            </tr>
            <tr class="heading">
                <td>ITEM</td>
                <td>QUANTITY</td>
                <td>UNIT</td>
                <td>WAREHOUSE</td>
            </tr>


            @foreach($formula->material as $material)
                <tr class="item">
                    <td>
                        {{$material->item->codeName}}
                    </td>

                    <td>
                        {{number_format_quantity($material->quantity)}}
                    </td>
                    <td>
                        {{$material->unit}}
                    </td>
                    <td>
                        {{$material->warehouse->name}}
                    </td>
                </tr>
            @endforeach

            <tr>
                <td colspan="2">
                    <a href="{{ $url . '/formulir/'.$formula->formulir_id.'/approval/check-status/'.$token }}">
                        <input type="button" class="btn btn-check" value="Check">
                    </a>
                    <a href="{{ $url . '/manufacture/point/formula/'.$formula->id.'/approve?token='.$token }}">
                        <input type="button" class="btn btn-success" value="Approve">
                    </a>
                    <a href="{{ $url . '/manufacture/point/formula/'.$formula->id.'/reject?token='.$token }}">
                        <input type="button" class="btn btn-danger" value="Reject">
                    </a>
                </td>
            </tr>
        </table>
    @endforeach
    @if($list_data->count() > 1)
        <br>
        <a href="{{ $url . '/manufacture/point/formula/approve-all/?formulir_id='.$array_formulir_id.'&token='.$token }}">
            <input type="button" class="btn btn-primary" value="Approve All">
        </a>
        <a href="{{ $url . '/manufacture/point/formula/reject-all/?formulir_id='.$array_formulir_id.'&token='.$token }}">
            <input type="button" class="btn btn-warning" value="Reject All">
        </a>
    @endif
</div>
</body>
</html>
