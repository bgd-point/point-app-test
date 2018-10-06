<a href="{{$url}}" class="btn btn-primary btn-effect-ripple">Export to Excel</a>
<a href="{{$url_pdf}}" class="btn btn-primary btn-effect-ripple">Export to PDF</a>
<br><br>
<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
        <tr>
            <th>&nbsp;</th>
            <th>form date</th>
            <th>form number</th>
            <th>person</th>
            <th>Account</th>
            <th>notes</th>
            <th class="text-right">received</th>
            <th class="text-right">disbursed</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $total_received = 0;
        $total_disbursed = 0;
        ?>
        @foreach($list_report as $report)
            @foreach($report->detail as $report_detail)
                <tr @if($report->formulir->form_status == -1) style="background:red;color:white" @endif>
                    <td>
                        <a href="#" onclick="pagePrint('/finance/point/{{$type}}/print/{{$report->id}}');" data-toggle="tooltip" title="Print" class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-print"></i> Print</a>
                    </td>
                    <td>{{ date_format_view($report->formulir->form_date) }}</td>
                    <td>
                        <a href="{{ url('finance/point/'.$type.'/'. $report->payment_flow .'/'.$report->id) }}" @if($report->formulir->form_status == -1) style="background:red;color:white !important" @endif>{{ $report->formulir->form_number}}</a>
                    </td>
                    <td>{{ strtoupper($report->person->codeName) }}</td>
                    <td>{{ \Point\Framework\Models\Master\Coa::find($report_detail->coa_id)->account }}</td>
                    <td>{{ strtoupper($report_detail->notes_detail) }}</td>
                    <td class="text-right">
                        @if($report->payment_flow == 'in')
                            <b>{{ number_format_price($report_detail->amount) }}</b>
                            <?php $total_received += $report_detail->amount; ?>
                        @else
                            0.00
                        @endif
                    </td>
                    <td class="text-right">
                        @if($report->payment_flow == 'out')
                            <b>{{ number_format_price($report_detail->amount) }}</b>
                            <?php $total_disbursed += $report_detail->amount * -1; ?>
                        @else
                            0.00
                        @endif
                    </td>
                </tr>
            @endforeach
        @endforeach
        <tr>
            <td colspan="6" class="text-right">Total</td>
            <td class="text-right"><strong>{{ number_format_price($total_received) }}</strong></td>
            <td class="text-right"><strong>{{ number_format_price($total_disbursed) }}</strong></td>
        </tr>
        <tr>
            <td colspan="6" class="text-right">Opening Balance</td>
            <td class="text-right"><strong>{{ number_format_price($opening_balance) }}</strong></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="6" class="text-right">Ending Balance</td>
            <td></td>
            <td class="text-right"><strong>{{ number_format_price($opening_balance + $total_received + $total_disbursed) }}</strong></td>
        </tr>
        <tr>
            <td colspan="6"></td>
            <td colspan="2" style="background: black"></td>
        </tr>
        <tr>
            <td colspan="6" class="text-right">Cash Advance</td>
            <td></td>
            <td class="text-right"><strong>{{ number_format_price($total_cash_advance_remaining) }}</strong></td>
        </tr>
        <tr>
            <td colspan="6" class="text-right">Total Cash</td>
            <td></td>
            <td class="text-right"><strong>{{ number_format_price($opening_balance + $total_received + $total_disbursed - ($total_cash_advance_remaining)) }}</strong></td>
        </tr>
        </tbody>
    </table>
</div>

<script>
  function pagePrint(url){
    var printWindow = window.open( url, 'Print', 'left=75, top=0, width=1200, height=1000, toolbar=0, resizable=0');
    printWindow.addEventListener('load', function(){
      printWindow.print();

    }, true);
  }
</script>
