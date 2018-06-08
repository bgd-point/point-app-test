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
    Hi, you have a request approval of formulir cancellation from {{ $username }}. We would like to inform the
    details as follows :

    <table cellpadding="0" cellspacing="0" style="padding: 20px 0;">
        <tr>
            <td style="width: 20%">
                Form Number
            </td>
            <td>
                :
            </td>
            <td>
                {{ $formulir->form_number }}</a>
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
                {{ \DateHelper::formatView($formulir->form_date) }}
            </td>
        </tr>
        <tr>
            <td style="width: 20%">
                Requested At 
            </td>
            <td>
                :
            </td>
            <td>
                {{ \DateHelper::formatView($formulir->cancel_requested_at) }}
            </td>
        </tr>
    </table>
    <table cellpadding="0" cellspacing="0">
        <tr>
            <td colspan="6" >
                <a href="{{ $url . '/formulir/'.$formulir->id.'/cancel/status/'.$token }}">
                	<input type="button" class="btn btn-check" value="Check">
                </a>
                <a href="{{ $url . '/formulir/'.$formulir->id.'/cancel/approve/'.$token }}">
                	<input type="button" class="btn btn-success" value="Approve">
                </a>
                <a href="{{ $url . '/formulir/'.$formulir->id.'/cancel/reject/'.$token }}">
                	<input type="button" class="btn btn-danger" value="Reject">
                </a>
            </td>
        </tr>
    </table>
</div>
</body>
</html>