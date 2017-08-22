@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-sales::app.sales.point.service._breadcrumb')
            <li>Report</li>
        </ul>
        <h2 class="sub-header">Report</h2>
        @include('point-sales::app.sales.point.service.invoice._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{ url('sales/point/service/report/value') }}" method="get" class="form-horizontal">
                    <div class="form-group">
                        <div class="col-sm-3">
                            <select class="selectize" name="status" id="status" onchange="selectData()">
                                <option value="0" @if(\Input::get('status') == 0) selected @endif>open</option>
                                <option value="1" @if(\Input::get('status') == 1) selected @endif>closed</option>
                                <option value="-1" @if(\Input::get('status') == 'all') selected @endif>all</option>
                            </select>
                        </div>
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
                        <div class="col-sm-1">
                            <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary">
                            <i class="fa fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </form>

                <br/>

                <div class="table-responsive">
                    {!! $list_service->render() !!}
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Service</th>
                            <th class="text-right">Total Quantity</th>
                            <th class="text-right">Total Amount</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $total_price = 0; ?>
                        @foreach($list_service as $service)
                            <?php
                            $status = \Input::get('status');
                            $data = Point\PointSales\Models\Service\InvoiceService::joinInvoice()
                                ->joinFormulir()
                                ->whereNotNull('formulir.form_number')
                                ->where(function($query) use ($status) {
                                    $query->whereIn('formulir.form_status', [0, 1]);
                                    if ($status && $status != 'all') {
                                        $query->where('formulir.form_status', $status);
                                    }
                                })
                                ->where('point_sales_service_invoice_service.service_id', $service->id)
                                ->selectRaw('sum(point_sales_service_invoice_service.quantity) as quantity, sum(point_sales_service_invoice_service.price) as price')
                                ->first();

                            if ($data) {
                                $total_price += $data->price;
                            }
                            ?>
                            <tr>
                                <td>{{ $service->name}}</td>
                                <td class="text-right">{{ number_format_quantity($data->quantity, 0)}}</td>
                                <td class="text-right">{{ number_format_quantity($data->price)}}</td>
                            </tr>
                            
                        @endforeach
                        <tr>
                            <td colspan="3" class="text-right"><h4><strong>{{number_format_quantity($total_price)}}</strong></h4></td>
                        </tr>
                        </tbody>
                    </table>
                    {!! $list_service->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop
