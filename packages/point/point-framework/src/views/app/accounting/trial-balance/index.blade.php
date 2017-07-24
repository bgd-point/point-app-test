
@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <a href="{{url('accounting')}}" class="pull-right">
            <i class="fa fa-arrow-circle-left push-bit"></i> Back
        </a>
        <h2 class="sub-header">Trial Balance</h2>

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
                        </div>
                        <div class="col-sm-6">
                            <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search</button>
                            <a class="btn btn-effect-ripple btn-effect-ripple btn-info" onclick="exportExcel()"> Export to excel</a>
                        </div>
                    </div>
                </form>

                <br/>

                <div class="table-responsive">
                    @include('framework::app.accounting.trial-balance._data')
                </div>
            </div>
        </div>
    </div>
@stop
@section('scripts')
<script type="text/javascript">
    function exportExcel() {
        var date_from = $("#date-from").val();
        var date_to = $("#date-to").val();
        var url = '{{url()}}/accounting/trial-balance/export/?date_from='+date_from+'&date_to='+date_to;
        location.href = url;
    }

</script>
@stop