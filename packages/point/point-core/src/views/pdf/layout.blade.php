<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * {
            font-size: 10px;
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
            border:0.2px solid black;
            padding: 2px;
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
            margin-top: 35px;
        }

        .signature-person {
            width: 150px;
            text-align: center;
        }

        .signature-date {
            width: 150px;
            padding-left: 10px;
            text-align: left;
        }
    </style>
</head>

<body>
<table class="header-table" style="float:right; text-align: right;">
    <tr>
        @if(url_logo())
            <td>
                <img src="{{ public_path('app/'.app('request')->project->url.'/logo/logo.png') }}" style="background-color:transparent;width:auto;height:50px;">
                <div>
                    <span style="text-transform: uppercase; font-weight: bold">{{$warehouse->store_name ? : ''}}</span>
                    <br/>
                    {{$warehouse->address ? : ''}}
                    <br>
                    {{$warehouse->phone ? : ''}}
                </div>
            </td>
        @else
            <td>
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

    <table class="detail-table" @if(url_logo()) style="margin-top:45px" @endif>
        <!-- SAMPLE CONTENT

        -->
        @yield('content')
    </table>

    <table class="footer-table">
        <tr>
            <td colspan="2">
                <!-- END NOTES -->
                @yield('end-notes')
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
