@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <a href="{{url('accounting')}}" class="pull-right">
            <i class="fa fa-arrow-circle-left push-bit"></i> Back
        </a>
        <h2 class="sub-header">Sub Ledger</h2>

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{ url('accounting/sub-ledger') }}" method="get" class="form-horizontal">
                    <div class="form-group">
                        <div class="col-sm-6">
                            <div class="input-group input-daterange" data-date-format="{{date_format_masking()}}">
                                <input type="text" name="date_from" id="date-from" class="form-control date input-datepicker" placeholder="From"  value="{{\Input::get('date_from') ? \Input::get('date_from') : date(date_format_get(), strtotime($date_from))}}">
                                <span class="input-group-addon"><i class="fa fa-chevron-right"></i></span>
                                <input type="text" name="date_to" id="date-to" class="form-control date input-datepicker" placeholder="To" value="{{\Input::get('date_to') ? \Input::get('date_to') : date(date_format_get(), strtotime($date_to))}}">
                            </div>

                            <select name="coa_filter" id="coa-filter" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectCoa(this.value)">
                                <option></option>
                                @foreach($list_coa as $coa)
                                    <option value="{{$coa->id}}" {{ $coa->id != $coa_id ? : 'selected' }}>{{$coa->account}}</option>
                                @endforeach
                            </select>
                            <div id="result" style="displya:none">
                                @if(\Input::get('subledger_id'))
                                <?php
                                $list_subleder = Point\Framework\Http\Controllers\Accounting\SubLedgerController::getSubledger(\Input::get('coa_filter'));
                                ?>
                                @if($list_subleder)    
                                <select name="subledger_id" id="subledger-id" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                    <option></option>
                                    <option value="all" {{ \Input::get('subledger_id') == 'all' ? 'selected' : ''}}>All</option>
                                    @foreach($list_subleder as $subledger)
                                        <option value="{{$subledger->id}}" {{ $subledger->id != \Input::get('subledger_id') ? : 'selected' }}>{{$subledger->codeName}}</option>
                                    @endforeach
                                </select>
                                @endif
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search</button>
                            <a class="btn btn-effect-ripple btn-effect-ripple btn-info" onclick="exportExcel()"> Export to excel</a>
                        </div>
                    </div>
                </form>

                <br/>

                <div class="table-responsive">
                    @include('framework::app.accounting.sub-ledger._data')
                </div>
            </div>
        </div>
    </div>
@stop


@section('scripts')
<script type="text/javascript">
    function selectCoa(value) {
        $("#result").fadeOut();
        $.ajax({
            url: '{{url("accounting/sub-ledger/coa")}}',
            data: {coa_id : value},
            success: function(result) {
                $("#result").fadeIn();
                $("#result").html(result);
                initSelectize("#subledger-id");
            }
        });
    }

    function exportExcel() {
        var coa_filter = $("#coa-filter option:selected").val();
        var subledger_id = $("#subledger-id option:selected").val();
        var date_from = $("#date-from").val();
        var date_to = $("#date-to").val();
        var url = '{{url()}}/accounting/sub-ledger/export/?date_from='+date_from+'&date_to='+date_to+'&coa_filter='+coa_filter+'&subledger_id='+subledger_id;
        location.href = url;
    }
</script>
@stop