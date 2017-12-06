@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-finance::app.finance.point.cash-advance._breadcrumb')
            <li>Cash Advance</li>
        </ul>
        <h2 class="sub-header">Cash Advance</h2>
        @include('point-finance::app.finance.point.cash-advance._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{ url('finance/point/cash-advance') }}" method="get" class="form-horizontal">
                    <div class="form-group">
                        <div class="col-sm-3">
                            <select class="selectize" name="status" id="status" onchange="selectData('form_date', 'desc')">
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
                        <div class="col-sm-3">
                            <input type="text" name="search" id="search" class="form-control" placeholder="Search..."
                                    value="{{\Input::get('search')}}"
                                    value="{{\Input::get('search') ? \Input::get('search') : ''}}">
                        </div>
                        <div class="col-sm-1">
                            <input type="hidden" name="order_by" value="{{\Input::get('order_by') ? \Input::get('order_by') : 'form_date'}}">
                            <input type="hidden" name="order_type" value="{{\Input::get('order_type') ? \Input::get('order_type') : 'desc'}}">
                            <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </form>

                <br/>

                <div class="table-responsive">
                    <?php
                    $order_by = \Input::get('order_by') ? : 0;
                    $order_type = \Input::get('order_type') ? : 0;
                    ?>

                    {!! $list_cash_advance->appends(['order_by'=>app('request')->get('order_by'), 'order_type'=>app('request')->get('order_type'), 'status'=>app('request')->get('status'), 'search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
                    <table class="table table-striped table-bordered table-vcenter">
                        <thead>
                        <tr>
                            <th style="cursor:pointer" onclick="selectData('form_date', @if($order_by == 'form_date' && $order_type == 'asc') 'desc' @elseif($order_by == 'form_date' && $order_type == 'desc') 'asc' @else 'desc' @endif)">Form Date <span class="pull-right"><i class="fa @if($order_by == 'form_date' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'form_date' && $order_type == 'desc') fa-sort-desc @else fa-sort-asc @endif fa-fw"></i></span></th>
                            <th style="cursor:pointer" onclick="selectData('form_number', @if($order_by == 'form_number' && $order_type == 'asc') 'desc' @elseif($order_by == 'form_number' && $order_type == 'desc') 'asc' @else 'desc' @endif)">Form Number <span class="pull-right"><i class="fa @if($order_by == 'form_number' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'form_number' && $order_type == 'desc') fa-sort-desc @else fa-sort-asc @endif fa-fw"></i></span></th>
                            <th style="cursor:pointer" onclick="selectData('person.name', @if($order_by == 'person.name' && $order_type == 'asc') 'desc' @elseif($order_by == 'person.name' && $order_type == 'desc') 'asc' @else 'desc' @endif)">Employee <span class="pull-right"><i class="fa @if($order_by == 'person.name' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'person.name' && $order_type == 'desc') fa-sort-desc @else fa-sort-asc @endif fa-fw"></i></span></th>
                            <th>Account</th>
                            <th>Amount</th>
                            <th>Remaining Amount</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($list_cash_advance as $cash_advance)
                            <tr>
                                <td>{{ date_format_view($cash_advance->formulir->form_date) }}</td>
                                <td>
                                    <a href="{{ url('finance/point/cash-advance/'.$cash_advance->id) }}">{{ $cash_advance->formulir->form_number}}</a>
                                </td>
                                <td>
                                    {!! get_url_person($cash_advance->employee->id) !!}
                                </td>
                                <td>{{ $cash_advance->coa->account }}</td>
                                <td>{{ number_format_quantity($cash_advance->amount) }}</td>
                                <td>{{ number_format_quantity($cash_advance->remaining_amount) }}</td>
                                <td>
                                    @include('framework::app.include._approval_status_label', ['approval_status' => $cash_advance->formulir->approval_status])
                                    @include('framework::app.include._form_status_label', ['form_status' => $cash_advance->formulir->form_status])
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $list_cash_advance->appends(['order_by'=>app('request')->get('order_by'), 'order_type'=>app('request')->get('order_type'), 'status'=>app('request')->get('status'), 'search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop
@section('scripts')
    <script>
        function selectData(order_by, order_type) {
            var status = $("#status option:selected").val();
            var date_from = $("#date-from").val();
            var date_to = $("#date-to").val();
            var search = $("#search").val();
            var url = '{{url()}}/finance/point/cash-advance/?order_by='+order_by+'&order_type='+order_type+'&status='+status+'&date_from='+date_from+'&date_to='+date_to+'&search='+search;
            location.href = url;
        }
    </script>
@stop
