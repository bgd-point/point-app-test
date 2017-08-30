@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-sales::app.sales.point.service._breadcrumb')
            <li>Report</li>
        </ul>
        <h2 class="sub-header">Report</h2>

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{ url('sales/point/service/report') }}" method="get" class="form-horizontal">
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
                        <div class="col-sm-8">
                            <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary">
                            <i class="fa fa-search"></i> Search
                            </button>
                            @if(access_is_allowed_to_view('export.point.sales.service.report'))
                            <a class="btn btn-effect-ripple btn-effect-ripple btn-info button-export" onclick="exportExcel()">Export to excel</a>
                            @endif
                        </div>
                    </div>
                </form>

                <br/>

                <div class="table-responsive">
                    {!! $list_service->appends(['date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Service</th>
                            <th class="text-right">Total Quantity</th>
                            <th class="text-right">Total Amount</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $total_price = 0; $total_quantity = 0; ?>
                        @foreach($list_service as $service)
                            <?php
                            $date_from = \Input::get('date_from') ? date_format_db(\Input::get('date_from'), 'start') : '';
                            $date_to = \Input::get('date_to') ? date_format_db(\Input::get('date_to'), 'end') : '';
                            
                            $data = Point\PointSales\Helpers\ServiceReportHelper::detailByService($service->id, $date_from, $date_to);
                            if ($data) {
                                $total_price += $data->price;
                                $total_quantity += $data->quantity;
                            }
                            ?>
                            <tr>
                                <td><a href="{{url('sales/point/service/report/'.$service->id.'?date_from='.$date_from.'&date_to='.$date_to)}}" title="show detail"> {{ $service->name}} </a></td>
                                <td class="text-right">{{ number_format_quantity($data->quantity, 0)}}</td>
                                <td class="text-right">{{ number_format_quantity($data->price)}}</td>
                            </tr>
                            
                        @endforeach
                        <tr>
                            <td class="text-left"><h4><strong>Total</strong></h4></td>
                            <td class="text-right"><h4><strong>{{number_format_quantity($total_quantity, 0)}}</strong></h4></td>
                            <td colspan="3" class="text-right"><h4><strong>{{number_format_quantity($total_price)}}</strong></h4></td>
                        </tr>
                        </tbody>
                    </table>
                    {!! $list_service->appends(['date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
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
        $(".button-export").html(spinner);
        $(".button-export").addClass('disabled');
        $.ajax({
            url: '{{url("sales/point/service/report/export")}}',
            data: {
                date_from: date_from,
                date_to: date_to
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
