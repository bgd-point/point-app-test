@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            <li><a href="{{ url('finance') }}">Finance</a></li>
            <li>Allocation Report</li>
        </ul>
        <h2 class="sub-header">Allocation Report</h2>

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{ url('finance/point/allocation-report') }}" method="get" class="form-horizontal">
                    <div class="form-group">
                        <div class="col-sm-6">
                            <div class="input-group input-daterange" data-date-format="{{date_format_masking()}}">
                                <input type="text" name="date_from" id="date-from" class="form-control date input-datepicker" placeholder="From"  value="{{\Input::get('date_from') ? \Input::get('date_from') : date(date_format_get(), strtotime($date_from))}}">
                                <span class="input-group-addon"><i class="fa fa-chevron-right"></i></span>
                                <input type="text" name="date_to" id="date-to" class="form-control date input-datepicker" placeholder="To" value="{{\Input::get('date_to') ? \Input::get('date_to') : date(date_format_get(), strtotime($date_to))}}">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <select class="selectize" name="allocation_id" id="allocation_id" onchange="selectData()">
                                <option value="0" @if(\Input::get('allocation_id') == 0) selected @endif>Choose one...</option>
                                @foreach ($list_allocation as $allocation)
                                    <option value="{{ $allocation->id }}" @if(\Input::get('allocation_id') == $allocation->id) selected @endif>{{ $allocation->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-3">
                            <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search</button>
                            @if(app('request')->get('allocation_id') > 0)
                                <a class="btn btn-effect-ripple btn-effect-ripple btn-info" onclick="exportExcel()"> Export to excel</a>
                            @endif
                        </div>
                    </div>
                </form>

                <h1>Allocation : {{ $allocation_name }}</h1>

                <br/>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-vcenter">
                        <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Date</th>
                            <th>Description</th>
                            <th>In</th>
                            <th>Out</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $total_in = 0;
                        $total_out = 0;
                        ?>
                        @foreach($list_report as $report)
                            <?php

                            if ($report->amount > 0) {
                                $total_in += $report->amount;
                            } else {
                                $total_out += abs($report->amount);
                            }
                            ?>
                            <tr>
                                <td>{!! formulir_url($report->formulir) !!}</td>
                                <td>{{ date_format_view($report->formulir->form_date) }}</td>
                                <td>{{ $report->notes }}</td>
                                <td class="text-right">{{ $report->amount >= 0 ? number_format_price($report->amount, 0) : ''}}</td>
                                <td class="text-right">{{ $report->amount < 0 ? number_format_price(abs($report->amount), 0) : ''}}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-right"><b>{{ number_format_price($total_in, 0) }}</b></td>
                                <td class="text-right"><b>{{ number_format_price($total_out, 0) }}</b></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
@section('scripts')
    <script>
      function selectData() {
        {{--var allocation_id = $("#allocation_id option:selected").val();--}}
        {{--var url = '{{url()}}/finance/point/allocation-report/?allocation_id='+allocation_id;--}}
        {{--location.href = url;--}}
      }

      function exportExcel() {
        var allocation_id = $("#allocation_id").val();
        var date_from = $("#date-from").val();
        var date_to = $("#date-to").val();
        var url = '{{url()}}/finance/point/allocation-report/export?date_from='+date_from+'&date_to='+date_to+'&allocation_id='+ allocation_id;
        location.href = url;
      }
    </script>
@stop
