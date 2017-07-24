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
                <form action="{{ url('sales/point/service/report') }}" method="get" class="form-horizontal">
                    <div class="form-group">
                        <div class="col-sm-3">
                            <select class="selectize" name="status" id="status" onchange="selectData()">
                                <option value="0" @if(\Input::get('status') == 0) selected @endif>open</option>
                                <option value="1" @if(\Input::get('status') == 1) selected @endif>closed</option>
                                <option value="-1" @if(\Input::get('status') == -1) selected @endif>canceled</option>
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
                        <div class="col-sm-3">
                            <input type="text" name="search" id="search" class="form-control"
                                    placeholder="Search Form Number / Customer..." value="{{\Input::get('search')}}"
                                    value="{{\Input::get('search') ? \Input::get('search') : ''}}">
                        </div>
                        <div class="col-sm-1">
                            <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i
                                        class="fa fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </form>

                <br/>

                <div class="table-responsive">
                    {!! $list_invoice->render() !!}
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Form Number</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $total = 0; ?>
                        @foreach($list_invoice as $invoice)
                            <?php $total += $invoice->total; ?>
                            <tr id="list-{{$invoice->formulir_id}}">
                                <td>{{ date_format_view($invoice->formulir->form_date) }}
                                    <br>
                                    <i class="fa fa-caret-down"></i>
                                    <a data-toggle="collapse" href="#collapse{{$invoice->formulir_id}}">
                                        <small>Details</small>
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ url('sales/point/service/invoice/'.$invoice->id) }}">{{ $invoice->formulir->form_number}}</a>
                                </td>
                                <td>
                                    <a href="{{ url('master/contact/'. $invoice->person->type->name . '/' . $invoice->person_id) }}">{{ $invoice->person->codeName}}</a>
                                </td>
                                <td>
                                    @include('framework::app.include._approval_status_label', ['approval_status' => $invoice->formulir->approval_status])
                                    @include('framework::app.include._form_status_label', ['form_status' => $invoice->formulir->form_status])
                                </td>
                                <td class="text-right">{{number_format_quantity($invoice->total, 0)}}</td>
                            </tr>
                            <tr>
                                <td colspan="5" style="border-top: none;">
                                    <div id="collapse{{$invoice->formulir_id}}"
                                         class="panel-collapse collapse">
                                        <b>Details</b>
                                        <ul class="list-group">
                                            <?php
                                            $invoice_service = Point\PointSales\Models\Service\InvoiceService::where('point_sales_service_invoice_id', $invoice->point_sales_service_invoice_id)->get();
                                            ?>
                                            @foreach($invoice_service as $service)
                                                <li class="list-group-item">
                                                    <small>{{ $service->service->name }}
                                                        # {{ number_format_quantity($service->quantity, 0) }}
                                                        <span class="pull-right">{{ number_format_quantity($service->price * $service->quantity, 0) }}</span>
                                                    </small>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="4" class="text-right"><h4><strong>Total of Sales ({{count($list_invoice)}})</strong></h4></td>
                            <td class="text-right"><h4><strong>{{number_format_quantity($total)}}</strong></h4></td>
                        </tr>
                        </tbody>
                    </table>
                    {!! $list_invoice->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop
