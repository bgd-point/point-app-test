<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Approval Request</title>
    
    <style>
    a {
        text-decoration: none;
    }

    .invoice-box{
        max-width:800px;
        margin:auto;
        padding:30px;
        border:1px solid #eee;
        box-shadow:0 0 10px rgba(0, 0, 0, .15);
        font-size:16px;
        line-height:24px;
        font-family:'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        color:#555;
    }
    
    .invoice-box table{
        width:100%;
        line-height:inherit;
        text-align:left;
    }
    
    .invoice-box table td{
        padding:5px;
        vertical-align:top;
    }
    
    .invoice-box table tr td:nth-child(2){
        text-align:right;
    }
    
    .invoice-box table tr.top table td{
        padding-bottom:20px;
    }
    
    .invoice-box table tr.top table td.title{
        font-size:45px;
        line-height:45px;
        color:#333;
    }
    
    .invoice-box table tr.information table td{
        padding-bottom:40px;
    }
    
    .invoice-box table tr.heading td{
        background:#eee;
        border-bottom:1px solid #ddd;
        font-weight:bold;
    }
    
    .invoice-box table tr.details td{
        padding-bottom:20px;
    }
    
    .invoice-box table tr.item td{
        border-bottom:1px solid #eee;
    }
    
    .invoice-box table tr.item.last td{
        border-bottom:none;
    }
    
    .invoice-box table tr.total td:nth-child(2){
        border-top:2px solid #eee;
        font-weight:bold;
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
        .invoice-box table tr.top table td{
            width:100%;
            display:block;
            text-align:center;
        }
        
        .invoice-box table tr.information table td{
            width:100%;
            display:block;
            text-align:center;
        }
    }
    </style>
</head>

<body>
    <div class="invoice-box">
        Hi, {{ $username }}. You have an approval request for selling shares. We would like to inform the details as follows :
        @foreach($list_shares_sell as $shares_sell)
            <table cellpadding="0" cellspacing="0" style="padding: 20px 0;">
                <tr>
                    <td style="width: 20%">
                        <b>Date</b>
                    </td>
                    <td>
                        :
                    </td>
                    <td>
                        {{ date_format_view($shares_sell->formulir->form_date) }}
                    </td>
                </tr>
                <tr>
                    <td style="width: 20%">
                        <b>Number</b>
                    </td>
                    <td>
                        :
                    </td>
                    <td>
                        {{ $shares_sell->formulir->form_number }}
                    </td>
                </tr>
                <tr> </tr>
                <tr>
                    <td style="width: 20%">
                        <b>Buy Info</b>
                    </td>
                    <td>
                       
                    </td>
                </tr>
                <tr>
                    <td style="width: 20%">
                        Shares
                    </td>
                    <td>
                        :
                    </td>
                    <td>
                        {{ strtoupper($shares_sell->shares->name) }}
                    </td>
                </tr>
                <tr>
                    <td style="width: 20%">
                        Group
                    </td>
                    <td>
                        :
                    </td>
                    <td>
                        {{ strtoupper($shares_sell->ownerGroup->name) }}
                    </td>
                </tr>
                <tr>
                    <td style="width: 20%">
                        Owner
                    </td>
                    <td>
                        :
                    </td>
                    <td>
                        {{ strtoupper($shares_sell->owner->name) }}
                    </td>
                </tr>
                <tr>
                    <td style="width: 20%">
                        Broker
                    </td>
                    <td>
                        :
                    </td>
                    <td>
                        {{ strtoupper($shares_sell->broker->name) }}
                    </td>
                </tr>
                <tr>
                    <td style="width: 20%">
                        Fee
                    </td>
                    <td>
                        :
                    </td>
                    <td>
                        {{ number_format_quantity($shares_sell->fee) }}
                    </td>
                </tr>
                <tr>
                    <td style="width: 20%">
                        Quantity
                    </td>
                    <td>
                        :
                    </td>
                    <td>
                        {{ number_format_quantity($shares_sell->quantity) }} Sheet
                    </td>
                </tr>
                <tr>
                    <td style="width: 20%">
                        Price
                    </td>
                    <td>
                        :
                    </td>
                    <td>
                        {{ number_format_quantity($shares_sell->price) }}
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
                        {{ number_format_quantity($shares_sell->price * $shares_sell->quantity) }}
                    </td>
                </tr>
            </table>

            <table cellpadding="0" cellspacing="0"> 
                <tr>
                    <td colspan="2">
                       <a href="{{ $url . '/formulir/'.$shares_sell->formulir_id.'/approval/check-status/'.$token }}"><input type="button" class="btn btn-check" value="Check"></a>
                       <a href="{{ $url . '/facility/bumi-shares/sell/'.$shares_sell->id.'/approve?token='.$token }}"><input type="button" class="btn btn-success" value="Approve"></a>
                       <a href="{{ $url . '/facility/bumi-shares/sell/'.$shares_sell->id.'/reject?token='.$token }}"><input type="button" class="btn btn-danger" value="Reject"></a>
                    </td>
                </tr>
            </table>
        @endforeach
    </div>
</body>
</html>
