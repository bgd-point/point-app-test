@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <a href="{{url('accounting')}}" class="pull-right">
            <i class="fa fa-arrow-circle-left push-bit"></i> Back
        </a>
        <h2 class="sub-header">Profit And Loss</h2>

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{ url('#') }}" method="get" class="form-horizontal">
                    <div class="form-group">
                        <div class="col-sm-12">
                            <label class="col-md-3 control-label">From</label>
                            <div class="col-sm-2">
                                <select name="month_from" data-placeholder="Choose one.." class="selectize-standard">
                                    @for($i=1;$i<=12;$i++)
                                        <option value="{{$i}}" @if(app('request')->input('month_from') == $i) selected @endif>{{$month[$i-1]}}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <select name="year_from" data-placeholder="Choose one.." class="selectize-standard">
                                    @for($i = date('Y'); $i >= date('Y') - 4; $i--)
                                        <option value="{{ $i }}" @if(app('request')->input('year_from') == $i) selected @endif>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <hr>
                        <div class="col-sm-12">
                            <label class="col-md-3 control-label">To</label>
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
                        </div>
                        <div class="col-sm-12">
                            <br/>
                            <label class="col-md-3 control-label"></label>
                            <div class="col-sm-2">
                                <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search</button>
                            </div>
                        </div>
                    </div>
                </form>

                <br/>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th>Account</th>
                            @for($i = 0; $i <= $total_month; $i++)
                                <th class="text-right">{{ date("M Y", strtotime("+" . $i . " month", strtotime($date_from))) }}</th>
                            @endfor
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="4"><h4><b>Revenue</b></h4></td>
                        </tr>
                        <?php $total_revenue = []; ?>
                        @foreach($list_coa_revenue as $coa)
                            <tr>
                                <td>{{ $coa->account }}</td>
                                @for($i = 0; $i <= $total_month; $i++)
                                @if(! array_key_exists($i, $total_revenue))
                                <?php $total_revenue[$i] = 0; ?>
                                @endif
                                <?php $revenue = $coa->value(date("Y-m-d", strtotime("+" . $i . " month", strtotime($date_from)))); ?>
                                <?php $total_revenue[$i] += $revenue; ?>
                                <td class="text-right">{{ number_format_accounting($revenue) }}</td>
                                @endfor
                            </tr>
                        @endforeach
                        <tr>
                            <td><h4>Total Revenue</h4></td>
                            @for($i = 0; $i <= $total_month; $i++)
                                <td class="text-right">{{number_format_accounting($total_revenue[$i])}}</td>
                            @endfor
                        </tr>
                        <tr>
                            <td colspan="4"></td>
                        </tr>
                        <tr>
                            <td colspan="4"><h4><b>Expense</b></h4></td>
                        </tr>
                        <?php $total_expense = []; ?>
                        @foreach($list_coa_expense as $coa)
                            <tr>
                                <td>{{ $coa->account }}</td>
                                @for($i = 0; $i <= $total_month; $i++)
                                    @if(! array_key_exists($i, $total_expense))
                                        <?php $total_expense[$i] = 0; ?>
                                    @endif
                                    <?php $expense = $coa->value(date("Y-m-d", strtotime("+" . $i . " month", strtotime($date_from)))); ?>
                                    <?php $total_expense[$i] += $expense; ?>
                                    <td class="text-right">{{ number_format_accounting($expense) }}</td>
                                @endfor
                            </tr>
                        @endforeach
                        <tr>
                            <td><h4>Total Expense</h4></td>
                            @for($i = 0; $i <= $total_month; $i++)
                                <td class="text-right">{{number_format_accounting($total_expense[$i])}}</td>
                            @endfor
                        </tr>

                        <tr>
                            <td><h4><b>Total Profit or Loss</b></h4></td>
                            @for($i = 0; $i <= $total_month; $i++)
                            <td class="text-right">{{number_format_accounting($total_revenue[$i] - $total_expense[$i])}}</td>
                            @endfor
                        </tr>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
