@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <a href="{{url('accounting')}}" class="pull-right">
            <i class="fa fa-arrow-circle-left push-bit"></i> Back
        </a>
        <h2 class="sub-header">Balance Sheet</h2>

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{ url('#' . rand(0, 999999)) }}" method="get" class="form-horizontal">
                    <div class="form-group">
                        <div class="col-sm-2">
                            <select name="month_to" data-placeholder="Choose one.." class="selectize-standard">
                                @for($i=1;$i<=12;$i++)
                                    <option value="{{$i}}" @if(app('request')->input('month_to') == $i) selected @endif>{{$month[$i-1]}}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <select name="year_to" data-placeholder="Choose one.." class="selectize-standard">
                                @for($i = date('Y'); $i >= date('Y') - 4; $i--)
                                    <option value="{{ $i }}" @if(app('request')->input('year_to') == $i) selected @endif>{{ $i }}</option>
                                @endfor
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
                    @include('framework::app.accounting.balance-sheet._data')
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
        var url = '{{url()}}/accounting/balance-sheet/export/?date_from='+date_from+'&date_to='+date_to;
        location.href = url;
    }

</script>
@stop
