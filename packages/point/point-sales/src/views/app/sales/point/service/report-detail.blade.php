@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-sales::app.sales.point.service._breadcrumb')
            <li><a href="{{url('sales/point/service/report')}}">Report</a></li>
            <li>{{$service->name}}</li>
        </ul>
        <h2 class="sub-header">Report "{{$service->name}}"</h2>

        <div class="panel panel-default">
            <div class="panel-body">
                <div class="table-responsive">
                    {!! $list_report->appends(['date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Form Date</th>
                            <th>Form Number</th>
                            <th>Allocation</th>
                            <th>Notes</th>
                            <th class="text-right">Quantity</th>
                            <th class="text-right">Price</th>
                            <th class="text-right">Discount</th>
                            <th class="text-right">Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $total_price = 0; $i=0?>
                        @foreach($list_report as $report)
                            <?php 
                            $total_price = ($report->quantity * $report->price) - ($report->quantity * $report->price * $report->dicount / 100);
                            ++$i;
                            ?>
                            <tr>
                                <td>{{date_format_view($report->invoice->formulir->form_date)}}</td>
                                <td><a href="{{url('sales/point/service/invoice/'.$report->invoice->id)}}">{{$report->invoice->formulir->form_number}}</a></td>
                                <td>{{$report->allocation->name}}</td>
                                <td>{{$report->service_notes}}</td>
                                <td class="text-right">{{ number_format_quantity($report->quantity, 0)}}</td>
                                <td class="text-right">{{ number_format_quantity($report->price)}}</td>
                                <td class="text-right">{{ number_format_quantity($report->dicount, 0)}}</td>
                                <td class="text-right">{{ number_format_quantity($total_price)}}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="7" style="vertical-align:middle" class="text-right align-middle">Total ({{$i}})</td>
                            <td style="vertical-align:middle" class="text-right align-middle"><h4><strong>{{number_format_quantity($total_price)}}</strong></h4></td>
                        </tr>
                        </tbody>
                    </table>
                    {!! $list_report->appends(['date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop
