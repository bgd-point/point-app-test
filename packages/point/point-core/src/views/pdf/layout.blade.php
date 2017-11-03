<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * {
            font-size: 13px;
            text-transform: uppercase;
        }

        table {
            width:100%;
        }

        .header-table {
            margin-top: 3px;
        }

        .detail-table {
            margin-top: 20px;
            border-spacing: 0;
        }

        .detail-table tr,
        .detail-table th,
        .detail-table td {
            border:1px solid black;
            padding: 5px;
        }

        .footer-table {
            margin-top: 10px;
        }

        .text-right {
            text-align: right;
        }

        .signature {
            content: " ";
            width: 150px;
            text-align: center;
            margin-top: 70px;
        }

        .signature-person {
            width: 150px;
            text-align: center;
        }
    </style>
</head>

<body>
<table class="header-table">
    <tr>
        @if(url_logo())
            <td>
                <img src="{{ public_path('app/'.app('request')->project->url.'/logo/logo.png') }}" style="float:right;background-color:transparent;width:auto;height:50px;">
                <div>
                    {{$warehouse->address ? : ''}}
                    <br>
                    {{$warehouse->phone ? : ''}}
                </div>
            </td>
        @else
            <td valign="top" style="padding-left:5px">
                <span style="text-transform: uppercase; font-weight: bold">{{$warehouse->store_name ? : ''}}</span>
                <br/>
                <span style="text-transform: capitalize;">
                    {{$warehouse->address ? : ''}}
                    <br/>
                    {{$warehouse->phone ? : ''}}
                </span>
            </td>
        @endif
    </tr>
</table>

<div>
    <table class="header-table">
        <!-- SAMPLE HEADER

        -->
        @yield('header')
    </table>

    <table class="detail-table">
        <!-- SAMPLE CONTENT

        -->
        @yield('content')
    </table>

    <table class="footer-table">
        <tr>
            <td colspan="2">
                <!-- END NOTES -->
                @yield('end-notes')
                <br/><br/>
            </td>
        </tr>
        <tr>
            <!-- SAMPLE SIGNATURE
            <td>
                Disetujui,
                <div class="signature-date">01 January 2017</div>
                <div class="signature">____________________</div>
                <div class="signature-person">(Person Name)</div>
            </td>
            -->
            @yield('signature')
        </tr>
    </table>
</div>
</body>
</html>
