@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li>Report</li>
        </ul>
        <h2 class="sub-header">Report</h2>

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{ url('purchasing/point/report') }}" method="get" class="form-horizontal">
                    <div class="form-group">
                        <div class="col-sm-4">
                            <div class="input-group input-daterange" data-date-format="{{date_format_masking()}}">
                                <input type="text" name="date_from" id="date-from" class="form-control date input-datepicker"
                                        placeholder="From"
                                        value="{{\Input::get('date_from') ? \Input::get('date_from') : ''}}">
                                <span class="input-group-addon"><i class="fa fa-chevron-right"></i></span>
                                <input type="text" name="date_to" id="date-to" class="form-control date input-datepicker"
                                        placeholder="To"
                                        value="{{\Input::get('date_to') ? \Input::get('date_to') : ''}}">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <input type="text" class="form-control" name="search" id="search" placeholder="search supplier name / formulir number .." value="{{\Input::get('search') ? : ''}}">
                        </div>
                        <div class="col-sm-5">
                            <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary">
                            <i class="fa fa-search"></i> Search
                            </button>
                            @if(access_is_allowed_to_view('export.point.purchasing.report'))
                            <a class="btn btn-effect-ripple btn-effect-ripple btn-info button-export" onclick="exportExcel()">Export to excel</a>
                            @endif
                            @if(auth()->user()->may('read.point.purchasing.report'))
                                <a class="btn btn-effect-ripple btn-effect-ripple btn-info button-export" id="btn-pdf" href="{{url('purchasing/point/report/pdf?date_from='.\Input::get('date_from').'&date_to='.\Input::get('date_to').'&search='.\Input::get('search').'&order_by='.\Input::get('order_by').'&order_type='.\Input::get('order_type').'&status='.\Input::get('status'))}}"> export to PDF</a>
                            @endif
                        </div>
                    </div>
                </form>

                <br/>

                <div class="table-responsive">
                    {!! $list_report->appends(['date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to'), 'date_to'=>app('request')->get('search') ])->render() !!}
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Form Date</th>
                            <th>Form Number</th>
                            <th>Supplier</th>
                            <th>Item</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-right">Price</th>
                            <th class="text-right">Discount</th>
                            <th class="text-right">Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $total_value = 0; ?>
                        @foreach($list_report as $report)
                            <?php
                            $total = $report->price * $report->quantity;
                            $total = $total - ($total * $report->discount / 100);
                            $total_value += $total;
                            ?>
                            <tr>
                                <td>{{date_format_view($report->invoice->formulir->form_date)}}</td>
                                <td><a href="{{url('purchasing/point/invoice/'.$report->invoice->id)}}">{{ $report->invoice->formulir->form_number}} </a></td>
                                <td>{!! get_url_person($report->invoice->supplier->id) !!}</td>
                                <td><a href="{{url('master/item/'.$report->item_id)}}"> {{$report->item->codeName}}</a></td>
                                <td class="text-center">{{$report->quantity}}</td>
                                <td class="text-right">{{$report->price}}</td>
                                <td class="text-right">{{$report->discount}} %</td>
                                <td class="text-right">{{$total}}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td class="text-right" colspan="8"><h4><strong>{{$total_value}}</strong></h4></td>
                        </tr>
                        </tbody>
                    </table>
                    {!! $list_report->appends(['date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to'), 'date_to'=>app('request')->get('search') ])->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
<script type="text/javascript">
    function exportExcel() {
        var spinner = ' <i class="fa fa-spinner fa-spin" style="font-size:16px;"></i>';
        var date_from = $("#date-from").val();
        var date_to = $("#date-to").val();
        var search = $('#search').val();
        $(".button-export").html(spinner);
        $(".button-export").addClass('disabled');
        $.ajax({
            url: '{{url("purchasing/point/report/export")}}',
            data: {
                date_from: date_from,
                date_to: date_to,
                search: search
            },
            success: function(result) {
                console.log(result);
                $(".button-export").removeClass('disabled');
                $(".button-export").html('Export to excel');
                notification('export data success, please check your email in a few moments');
            }, error:  function (result) {
                $(".button-export").removeClass('disabled');
                $(".button-export").html('Export to excel');
                notification('export data failed, please try again');
            }

        });
    }
</script>
@stop
