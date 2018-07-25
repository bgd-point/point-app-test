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
    Hi, you have a request approval purchase requisition from <strong>{{ $requester }}</strong>. We would like to inform the
    details as follows :

   @foreach($list_data as $purchase_requisition)
        <table cellpadding="0" cellspacing="0" style="padding: 20px 0;">
            <tr>
                <td style="width: 20%">Form Number</td>
                <td>:</td>
                <td>
                    <a href="{{ url('/purchasing/point/purchase-requisition/'.$purchase_requisition->id) }}">
                        {{ $purchase_requisition->formulir->form_number }}
                    </a>
                </td>
            </tr>
            <tr>
                <td style="width: 20%">Created By</td>
                <td>:</td>
                <td>{{ $purchase_requisition->formulir->createdBy->name }}</td>
            </tr>
            <tr>
                <td style="width: 20%">Form Date</td>
                <td>:</td>
                <td>{{ \DateHelper::formatView($purchase_requisition->formulir->form_date) }}</td>
            </tr>
            @if($purchase_requisition->supplier_id)
            <tr>
                <td style="width: 20%">Supplier</td>
                <td>:</td>
                <td>{{ $purchase_requisition->supplier->codeName }}</td>
            </tr>
            @endif
            <tr>
                <td style="width: 20%">Employee</td>
                <td>:</td>
                <td>{{ $purchase_requisition->employee->codeName }}</td>
            </tr>
            <tr>
                <td style="width: 20%">Notes</td>
                <td>:</td>
                <td>{{ $purchase_requisition->notes }}</td>
            </tr>
        </table>

        <table cellpadding="0" cellspacing="0">
            <tr class="heading">
                <td>Item</td>
                <td>Quantity</td>
                <td style="text-align: right;">Price</td>
                <td>Allocation</td>
                <td style="text-align: right;">Total</td>
            </tr>
            <?php
                $purchase_requisition_total = 0;
            ?>
            @foreach($purchase_requisition->items as $purchase_requisition_item)
                <?php
                    $item_total = $purchase_requisition_item->quantity * $purchase_requisition_item->price;
                    $purchase_requisition_total += $item_total;
                ?>
                <tr class="item">
                    <td>{{$purchase_requisition_item->item->codeName}}</td>
                    <td>{{number_format_quantity($purchase_requisition_item->quantity). ' ' .$purchase_requisition_item->unit}}</td>
                    <td style="text-align: right;">{{number_format_quantity($purchase_requisition_item->price)}}</td>
                    <td>{{$purchase_requisition_item->allocation->name}}</td>
                    <td style="text-align: right;">{{number_format_quantity($item_total)}}</td>
                </tr>
            @endforeach
            
            <tr class="heading">
                <td colspan="4" style="text-align: right;">Total</td>
                <td>
                    {{number_format_quantity($purchase_requisition_total)}}
                </td>
            </tr>
            <tr>
                <td colspan="5" >
                    <a href="{{ $url . '/formulir/'.$purchase_requisition->formulir_id.'/approval/check-status/'.$token }}"><input
                                type="button" class="btn btn-check" value="Check"></a>
                    <a href="{{ $url . '/purchasing/point/purchase-requisition/'.$purchase_requisition->id.'/approve?token='.$token }}"><input
                                type="button" class="btn btn-success" value="Approve"></a>
                    <a href="{{ $url . '/purchasing/point/purchase-requisition/'.$purchase_requisition->id.'/reject?token='.$token }}"><input
                                type="button" class="btn btn-danger" value="Reject"></a>
                </td>
            </tr>
        </table>
    @endforeach
    @if($list_data->count() > 1)
    <br>
    <a href="{{ $url . '/purchasing/point/purchase-requisition/approve-all/?formulir_id='.$array_formulir_id.'&token='.$token }}">
        <input type="button" class="btn btn-primary" value="Approve All">
    </a>
    <a href="{{ $url . '/purchasing/point/purchase-requisition/reject-all/?formulir_id='.$array_formulir_id.'&token='.$token }}">
        <input type="button" class="btn btn-warning" value="Reject All">
    </a>
    @endif
</div>
</body>
</html>
