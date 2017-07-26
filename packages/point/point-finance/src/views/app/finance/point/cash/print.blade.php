<html>
<head>
    <style type="text/css">
        body {
            background: rgb(204,204,204);
        }
        page {
            background: white;
            display: block;
            margin: 0 auto;
            margin-bottom: 0.5cm;
            box-shadow: 0 0 0.5cm rgba(0,0,0,0.5);
        }
        page[size="A5"][layout="landscape"] {
            width: 21cm;
            height: 14.8cm;
        }
        @media print {
            body, page {
                margin: 0;
                box-shadow: 0;
            }
        }
        .table{
            border-collapse: collapse;
            font-size:14px;
            page-break-after:always;
        }
        .column{
            border: 1px solid black;
            height: 0px;
        }
        #text-left {
            text-align: left;
        }

        #text-right {
            text-align: right;
        }

        #text-center {
            text-align: center;
        }
        #noneborder{
            border: none;
        }
    </style>
</head>
<body>
<page size="A5" layout="landscape">
    <?php
    $list_data = $cash->detail;
    $max_row = 9;
    $number_of_table = (int) ceil(count($list_data) / $max_row);
    $limit = $max_row;
    ?>
    @for($i=0;$i<$number_of_table;$i++)
        <?php $reference = null ?>
        <?php $count = 0;?>
        <br>
        <table id="noneborder">
            <tr>
                <td>
                    <img src="{{url_logo()}}" style="background-color:transparent;width:auto;height:50px;">
                </td>
                <td valign="top">
                    <strong style="font-size:14px; text-transform: uppercase;">{{$warehouse_profiles->store_name}}</strong> <br/><font style="font-size:12px;text-transform: capitalize;">
                        {{$warehouse_profiles->address}}<br/>{{$warehouse_profiles->phone}}</font>
                </td>
            </tr>
        </table>

        <table width="100%" class="table">
            <thead>
            <tr>
                <th id="text-right" colspan="4"></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td colspan="2" class="column" id="text-center">BUKTI KAS {{$cash->payment_flow == 'out' ? 'KELUAR' : 'MASUK'}}</td>
                <td colspan="1" class="column" style="border-right:0px" class="text-left">Tanggal</td>
                <td class="column" style="border-left:0px;">: {{ date_format_view($cash->formulir->form_date) }}</td>
            </tr>
            <tr>
                <td rowspan="2" class="column" colspan="2">{{$cash->payment_flow == 'out' ? 'dibayar kepada' : 'diterima dari'}} : {{ $cash->person->name }}</td>
                <td colspan="1" class="column" style="border-right:0px" class="text-left">Nomor</td>
                <td class="column" style="border-left:0px;">: {{ $cash->formulir->form_number }}</td>
            </tr>
            <tr>
                <td class="column">&nbsp;</td>
                <td class="column">&nbsp;</td>
            </tr>

            <tr>
                <td colspan="3" class="column" id="text-center">URAIAN</td>
                <td colspan="1" class="column">&nbsp;</td>
            </tr>
            @for($j=$i*$max_row;$j<count($list_data);$j++)
                <?php $reference = $list_data[$j]->reference;?>
                <?php $count++; ?>
                @if($count <= $limit)
                    <?php
                    $word = $list_data[$j]->notes_detail ;
                    $count_word = 80;
                    $note_detail_1 = substr($word, 0, $count_word);
                    ?>
                    @if($count_word < 85)

                        <tr>
                            <td colspan="3" class="column" id="text-left">{{ $note_detail_1 }}</td>
                            <td id="text-right" class="column">{{ number_format_price($list_data[$j]->amount, 0) }}</td>
                        </tr>
                        <?php
                        $note_detail_2 = substr($word, $count_word, $count_word);
                        ?>
                        @if($note_detail_2)
                            <tr>
                                <td colspan="1" class="column">&nbsp;</td>
                                <td colspan="2" class="column" id="text-left">{{ $note_detail_2 }}</td>
                                <td id="text-right" class="column">&nbsp;</td>
                            </tr>
                        @else
                        @endif

                    @endif
                @endif

            @endfor
            @for($j=$i-$max_row;$j<count($list_data);$j++)
                <?php $count++; ?>
                @if($count <= $limit)
                    <tr>
                        <td colspan="3" class="column" id="text-left">&nbsp;</td>
                        <td id="text-right" class="column">&nbsp;</td>
                    </tr>
                @endif
            @endfor

            <tr>
                <td colspan="3" id="text-left" class="column">Terbilang :<b>{{number_to_text(abs($cash->total))}} Rupiah</b></td>
                <td id="text-right" class="column"><b>{{ number_format_price(abs($cash->total), 0) }}</b></td>
            </tr>

            <tr>
                <td id="text-center" class="column" style="min-width:140px;">Disetujui</td>
                <td id="text-center" class="column" style="min-width:140px;">Dibayar</td>
                <td id="text-center" class="column" style="min-width:140px;">Diterima</td>
                <td id="text-center" class="column" style="min-width:140px;">Dibukukan</td>
            </tr>

            <tr>
                <td id="text-center" class="column">{{ $reference ? date_format_view($reference->form_date) : '' }} <br><br><br><br> {{$reference ?  $reference->approvalTo->name : '(______________________)'}}  </td>
                <td id="text-center" class="column">&nbsp;</td>
                <td id="text-center" class="column">&nbsp;</td>
                <td id="text-center" class="column">&nbsp;</td>
            </tr>
            </tbody>
        </table>
    @endfor
</page>
</body>
</html>
