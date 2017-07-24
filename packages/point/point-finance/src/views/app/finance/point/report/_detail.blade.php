@if(!count($list_report))
<h3 class="">Data not found</h3>
<?php return false; ?>
@endif

<a href="{{$url}}" class="btn btn-primary btn-effect-ripple">Export to Excel</a>
<br><br>
<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>&nbsp;</th>    
                <th>form date</th>
                <th>form number</th>
                <th>person</th>
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
            <tr @if($report->formulir->form_status == -1) style="background:red;color:white" @endif>
                <td>
                    <a href="#" onclick="pagePrint('/finance/point/{{$type}}/print/{{$report->id}}');" data-toggle="tooltip" title="Print" class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-print"></i> Print</a>
                </td>
                <td>{{ date_format_view($report->formulir->form_date) }}
                    <br>
                    <i class="fa fa-caret-down"></i>
                    <a data-toggle="collapse" href="#collapse{{$report->formulir->id}}">
                        <small>Details</small>
                    </a>
                </td>
                <td>
                    <a href="{{ url('finance/point/'.$type.'/'. $report->payment_flow .'/'.$report->id) }}" @if($report->formulir->form_status == -1) style="background:red;color:white !important" @endif>{{ $report->formulir->form_number}}</a>
                </td>
                <td>{{ $report->person->codeName }}</td>
                <td>{{ $report->formulir->notes }}</td>
                <td class="text-right">
                    @if($report->payment_flow == 'in')
                    <b>{{ number_format_price($report->total) }}</b>
                    <?php $total_received += $report->total ; ?>
                    @else
                    0.00
                    @endif
                </td>
                <td class="text-right">
                    @if($report->payment_flow == 'out')
                    <b>{{ number_format_price($report->total) }}</b>
                    <?php $total_disbursed += $report->total ; ?>
                    @else
                    0.00
                    @endif
                </td>
            </tr>
            <tr>
                <td colspan="7" style="border-top: none;">
                    <div id="collapse{{$report->formulir->id}}"
                         class="panel-collapse collapse">
                        <b>Details</b>
                        <ul class="list-group">
                            @foreach($report->detail as $report_detail)
                                <li class="list-group-item">
                                    <small>
                                        [{{ $report_detail->coa->account }}] {{ $report_detail->notes_detail }}
                                        {{ number_format_price($report_detail->amount) }}
                                    </small>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </td>
            </tr>
        @endforeach  
        <tr>
            <td colspan="5" class="text-right">Total</td>
            <td class="text-right"><strong>{{ number_format_price($total_received) }}</strong></td>
            <td class="text-right"><strong>{{ number_format_price($total_disbursed) }}</strong></td>
        </tr>
        <tr>
            <td colspan="5" class="text-right">Opening Balance</td>
            <td class="text-right"><strong>{{ number_format_price($opening_balance) }}</strong></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="5" class="text-right">Ending Balance</td>
            <td></td>
            <td class="text-right"><strong>{{ number_format_price($opening_balance + $total_received + $total_disbursed) }}</strong></td>
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
