<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Approval Request</title>
    
    <style>
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

    a {
        text-decoration: none;
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
        <p align="justify">
            Hi, you have an approval request for Cut Off Payable from <strong>{{ $requester }}</strong>. <br>
            We would like to inform the details as follows :
        </p>
        <?php
            $list_coa = Point\Framework\Models\Master\Coa::active()->where('coa_category_id', 8)->get();
        ?>
        @foreach($list_data as $cut_off)
            <table cellpadding="0" cellspacing="0" style="padding: 20px 0;">
                <tr>
                    <td style="width: 20%">Form Number</td>
                    <td>:</td>
                    <td>
                        <a href="{{ url('accounting/point/cut-off/payable/'.$cut_off->id) }}">
                            {{ $cut_off->formulir->form_number }}
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="width: 20%">Created By</td>
                    <td>:</td>
                    <td>{{ $cut_off->formulir->createdBy->name }}</td>
                </tr>
                <tr>
                    <td style="width: 20%">Form Date</td>
                    <td>:</td>
                    <td>{{ \DateHelper::formatView($cut_off->formulir->form_date) }}</td>
                </tr>
                
            </table>

            <table cellpadding="0" cellspacing="0">
                <?php 
                    $i = 0;
                    $total_amount = 0;
                ?>
                @foreach($list_coa as $coa)
                    <?php
                        $cut_off_receivable_detail = Point\PointAccounting\Models\CutOffPayableDetail::where('coa_id', $coa->id)->where('cut_off_payable_id', $cut_off->id)->get();
                        $amount = Point\PointAccounting\Models\CutOffPayableDetail::where('coa_id', $coa->id)->where('cut_off_payable_id', $cut_off->id)->sum('amount');
                        $total_amount += $amount;

                        if (!$cut_off_receivable_detail->count()) {
                            continue;
                        }
                    ?>
                    <tr class="heading">
                        <td align="left"><strong>{{ $coa->name }}</strong></td>
                        <td align="left">Notes</td>
                        <td align="right">Amount</td>
                    </tr>
                    @foreach($cut_off_receivable_detail as $account)
                    <tr class="item">
                        <td align="left">{{ $account->person->codeName }}</td>
                        <td align="left">{{ $account->notes }}</td>
                        <td align="right">{{number_format_quantity($account->amount)}}</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td align="right" colspan="3">
                            <strong>{{number_format_quantity($amount)}}</strong>
                        </td>
                    </tr>
                    <?php $i++;?>
                @endforeach
                <tr style="border-top:1px solid #eee; margin-top:10px">
                    <td align="right" colspan="2"><strong>Total</strong></td>
                    <td align="right"><strong>{{number_format_quantity($total_amount)}}</strong></td>
                </tr>
                <tr>
                    <td colspan="3">
                       <a href="{{ $url . '/formulir/'.$cut_off->formulir_id.'/approval/check-status/'.$token }}"> 
                            <input type="button" class="btn btn-check" value="CHECK">
                       </a>
                       <a href="{{ $url . '/accounting/point/cut-off/payable/'.$cut_off->id.'/approve?token='.$token }}"> 
                            <input type="button" class="btn btn-success" value="APPROVE">
                       </a>
                       <a href="{{ $url . '/accounting/point/cut-off/payable/'.$cut_off->id.'/reject?token='.$token }}">
                            <input type="button" class="btn btn-danger" value="REJECT">
                       </a>
                    </td>
                </tr>

            </table>
            @endforeach
        
    </div>

</body>
</html>