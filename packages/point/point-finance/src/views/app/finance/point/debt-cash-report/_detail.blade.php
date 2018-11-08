<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
        <tr>
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
            <td colspan="5" class="text-right">Total</td>
            <td class="text-right"><strong>{{ number_format_price($total_received) }}</strong></td>
            <td class="text-right"><strong>{{ number_format_price($total_disbursed) }}</strong></td>
        </tr>
        </tbody>
    </table>
</div>

<div class="form-group">
    <div class="row">
        <label class="col-md-3 control-label">Approval to</label>
        <div class="col-sm-6">
            <select name="approver" id="approver" class="selectize">
            @foreach($list_user as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
            </select>
        </div>
    </div>
    <div class="row">
        <label class="col-md-3"></label>
        <div class="col-sm-9">
            <button class="btn btn-info" type="button" id="btn-send-approval" onclick="sendApproval()">Request Approval</button>
        </div>
    </div>
</div>

<script>
    function pagePrint(url){
        var printWindow = window.open( url, 'Print', 'left=75, top=0, width=1200, height=1000, toolbar=0, resizable=0');
        printWindow.addEventListener('load', function(){
            printWindow.print();

        }, true);
    }
</script>
