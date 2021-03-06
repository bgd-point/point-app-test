@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li><a href="{{ url('purchasing/point/payment-order') }}">Payment Order</a></li>
            <li>Report</li>
        </ul>
        <h2 class="sub-header">PAYMENT ORDER</h2>
        @include('point-purchasing::app.purchasing.point.inventory.payment-order._menu')

        <div class="panel panel-default">
            <div class="panel-body">

                <form method="get" class="form-horizontal">
                    <div class="form-group">
                        <div class="col-sm-4">
                            <select class="selectize" name="status" id="status" onchange="selectData('form_date', 'asc')">
                                <option value="0" @if(\Input::get('status') == 0) selected @endif>open</option>
                                <option value="1" @if(\Input::get('status') == 1) selected @endif>closed</option>
                                <option value="-1" @if(\Input::get('status') == -1) selected @endif>canceled</option>
                                <option value="all" @if(\Input::get('status') == 'all') selected @endif>all</option>
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
                        <div class="col-sm-4">
                            <input type="text" name="search" id="search" class="form-control"
                                   placeholder="Search Form Number / Supplier..." value="{{\Input::get('search')}}"
                                   value="{{\Input::get('search') ? \Input::get('search') : ''}}">
                        </div>
                        <div class="col-sm-12">
                            <input type="hidden" name="order_by" value="{{\Input::get('order_by') ? \Input::get('order_by') : 'form_date'}}">
                            <input type="hidden" name="order_type" value="{{\Input::get('order_type') ? \Input::get('order_type') : 'desc'}}">
                            <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search </button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <?php 
                        $order_by = \Input::get('order_by') ? : "form_date";
                        $order_type = \Input::get('order_type') ? : "asc";
                    ?>
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th style="cursor:pointer;"
                                    onclick="selectData('form_date', @if($order_by == 'form_date' && $order_type == 'asc') 'desc' @elseif($order_by == 'form_date' && $order_type == 'desc') 'asc' @else 'desc' @endif)">
                                    form date
                                    <span class="pull-right">
                                        <i class="fa fa-fw @if($order_by == 'form_date' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'form_date' && $order_type == 'desc') fa-sort-desc @endif"></i>
                                    </span>
                                </th>
                                <th style="cursor:pointer;"
                                    onclick="selectData('form_number', @if($order_by == 'form_number' && $order_type == 'asc') 'desc' @elseif($order_by == 'form_number' && $order_type == 'desc') 'asc' @else 'desc' @endif)">
                                    from invoice
                                    <span class="pull-right">
                                        <i class="fa fa-fw @if($order_by == 'form_number' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'form_number' && $order_type == 'desc') fa-sort-desc @endif"></i>
                                    </span>
                                </th>
                                <th style="cursor:pointer;"
                                    onclick="selectData('supplier_id', @if($order_by == 'supplier_id' && $order_type == 'asc') 'desc' @elseif($order_by == 'supplier_id' && $order_type == 'desc') 'asc' @else 'desc' @endif)">
                                    supplier
                                    <span class="pull-right">
                                        <i class="fa fa-fw @if($order_by == 'supplier_id' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'supplier_id' && $order_type == 'desc') fa-sort-desc @endif"></i>
                                    </span>
                                </th>
                                <th style="cursor:pointer;"
                                    onclick="selectData('status', @if($order_by == 'status' && $order_type == 'asc') 'desc' @elseif($order_by == 'status' && $order_type == 'desc') 'asc' @else 'desc' @endif)">
                                    status
                                    <span class="pull-right">
                                        <i class="fa fa-fw @if($order_by == 'status' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'status' && $order_type == 'desc') fa-sort-desc @endif"></i>
                                    </span>
                                </th>
                                <th style="cursor:pointer; text-align: right;"
                                    onclick="selectData('invoice_remaining', @if($order_by == 'invoice_remaining' && $order_type == 'asc') 'desc' @elseif($order_by == 'invoice_remaining' && $order_type == 'desc') 'asc' @else 'desc' @endif)">
                                    remaining
                                    <span class="pull-right">
                                        <i class="fa fa-fw @if($order_by == 'invoice_remaining' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'invoice_remaining' && $order_type == 'desc') fa-sort-desc @endif"></i>
                                    </span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reports as $report)
                                <tr>
                                    <td>
                                        {{ date_Format_view($report->form_date) }}
                                    </td>
                                    <td>
                                        <a href="{{ url('purchasing/point/invoice/'.$report->invoice_id) }}">
                                            {{ $report->form_number }}
                                        </a>
                                    </td>
                                    <td>
                                        {!! get_url_person($report->supplier_id) !!}
                                    </td>
                                    <td>
                                        @include('framework::app.include._approval_status_label', ['approval_status' => $report->approval_status])
                                        @include('framework::app.include._form_status_label', ['form_status' => $report->form_status])
                                    </td>
                                    <td style="text-align: right;">
                                        {{ number_format_price($report->invoice_remaining) }}
                                    </td>
                                </tr>
                            @endforeach                            
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop


@section('scripts')
    <script type="text/javascript">
        function selectData(order_by, order_type) {
            var status = $("#status option:selected").val();
            var date_from = $("#date-from").val();
            var date_to = $("#date-to").val();
            var search = $("#search").val();
            var url = '{{url()}}/purchasing/point/payment-order/report?order_by='+order_by+'&order_type='+order_type+'&status='+status+'&date_from='+date_from+'&date_to='+date_to+'&search='+search;
            location.href = url;
        }
    </script>
@stop
