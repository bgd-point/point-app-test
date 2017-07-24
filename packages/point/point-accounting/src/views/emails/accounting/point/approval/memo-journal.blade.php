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
            Hi, you have an approval request for Memo Journal from {{ $username  }}.
            We would like to inform the details as follows :
            </p>

           @foreach($list_data as $memo_journal)
            <table cellpadding="0" cellspacing="0" style="padding: 20px 0;">
                <tr>
                    <td style="width: 20%">Form Number</td>
                    <td>:</td>
                    <td><a href="{{ url('accounting/point/memo-journal/'.$memo_journal->id) }}">{{ $memo_journal->formulir->form_number }}</a></td>
                </tr>
                <tr>
                    <td style="width: 20%">Form Date</td>
                    <td>:</td>
                    <td>{{date_format_view($memo_journal->formulir->form_date)}}</td>
                </tr>
                <tr>
                    <td style="width: 20%">Balance</td>
                    <td>:</td>
                    <td>{{ number_format_accounting($memo_journal->debit) }}</td>
                </tr>
                 <tr>
                    <td style="width: 20%">Notes</td>
                    <td>:</td>
                    <td>{{ $memo_journal->formulir->notes }}</td>
                </tr>
            </table>

            <table cellpadding="0" cellspacing="0">
                <tr class="heading">
                    <td align="center">COA</td>
                    <td align="center">Master Refference</td>
                    <td align="center">From Refference</td>
                    <td align="center">Description</td>
                    <td align="right">Debit</td>
                    <td align="right">Credit</td>
                </tr>
            
                @foreach($memo_journal->memoJournalDetails as $detail)
                    <tr class="item">
                        <td align="center">{{ $detail->coaModel->name }}</td>
                        <td align="center">@if($detail->subledger_id > 0){{ $master->name }}@else - @endif </td>
                        <td align="center"> @if($detail->form_reference_id > 0){{ $form->form_number }}@else - @endif </td>
                        <td align="center">{{ $detail->description }}</td>
                        <td align="right">{{ number_format_accounting($detail->debit) }}</td>
                        <td align="right">{{ number_format_accounting($detail->credit) }}</td>
                    </tr>
                @endforeach
            
                <tr>
                    <td colspan="4">
                       <a href="{{ $url . '/formulir/'.$memo_journal->formulir_id.'/approval/check-status/'.$token }}"> 
                       <input type="button" class="btn btn-check" value="CHECK">
                       </a>
                       <a href="{{ $url . '/accounting/point/memo-journal/'.$memo_journal->id.'/approve?token='.$token }}"> 
                       <input type="button" class="btn btn-success" value="APPROVE">
                       </a>
                       <a href="{{ $url . '/accounting/point/memo-journal/'.$memo_journal->id.'/reject?token='.$token }}">
                       <input type="button" class="btn btn-danger" value="REJECT">
                       </a>
                    </td>
                </tr>
            </table>
        @endforeach
    </div>
</body>
</html>