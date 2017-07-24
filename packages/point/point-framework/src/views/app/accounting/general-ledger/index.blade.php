@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <a href="{{url('accounting')}}" class="pull-right">
            <i class="fa fa-arrow-circle-left push-bit"></i> Back
        </a>
        <h2 class="sub-header">General Ledger</h2>

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{ url('#') }}" method="get" class="form-horizontal">
                    <div class="form-group">
                        <div class="col-sm-6">
                            <div class="input-group input-daterange" data-date-format="{{date_format_masking()}}">
                                <input type="text" name="date_from" id="date-from" class="form-control date input-datepicker" placeholder="From"  value="{{\Input::get('date_from') ? \Input::get('date_from') : date(date_format_get(), strtotime($date_from))}}">
                                <span class="input-group-addon"><i class="fa fa-chevron-right"></i></span>
                                <input type="text" name="date_to" id="date-to" class="form-control date input-datepicker" placeholder="To" value="{{\Input::get('date_to') ? \Input::get('date_to') : date(date_format_get(), strtotime($date_to))}}">
                            </div>

                            <select name="coa_filter" id="coa-filter" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                <option></option>
                                @foreach($list_coa as $coa)
                                    <option value="{{$coa->id}}" {{ $coa->id != $coa_id ? : 'selected' }}>{{$coa->account}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search</button>
                            <a class="btn btn-effect-ripple btn-effect-ripple btn-info" onclick="exportExcel()"> Export to excel</a>
                        </div>
                    </div>
                </form>

                <br/>

                <div class="table-responsive">
                    @include('framework::app.accounting.general-ledger._data')
                </div>
            </div>
        </div>
    </div>
@stop
@section('scripts')
<script type="text/javascript">
    function exportExcel() {
        var coa_filter = $("#coa-filter option:selected").val();
        var date_from = $("#date-from").val();
        var date_to = $("#date-to").val();
        var url = '{{url()}}/accounting/general-ledger/export/?date_from='+date_from+'&date_to='+date_to+'&coa_filter='+coa_filter;
        location.href = url;
    }

</script>
@stop