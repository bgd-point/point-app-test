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
                        <div class="col-sm-3">
                            <select class="selectize" name="allocation_id" id="allocation_id" onchange="selectData()">
                                <option value="0" @if(\Input::get('allocation_id') == 0) selected @endif>Choose one...</option>
                                @foreach ($list_allocation as $allocation)
                                    <option value="{{ $allocation->id }}" @if(\Input::get('allocation_id') == $allocation->id) selected @endif>{{ $allocation->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>

                <h1>Allocation : L112P</h1>

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
                                $total_out += abs($report->amount);
                            } else {
                                $total_in += $report->amount;
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
                                <td></td>
                                <td></td>
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
        var allocation_id = $("#status option:selected").val();
        var url = '{{url()}}/finance/point/allocation-report/?allocation_id='+allocation_id;
        location.href = url;
      }
    </script>
@stop
