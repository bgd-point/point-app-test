@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.fixed-assets._breadcrumb')
            <li>Downpayment</li>
        </ul>
        <h2 class="sub-header">Downpayment | Fixed Assets</h2>
        @include('point-purchasing::app.purchasing.point.fixed-assets.downpayment._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{ url('purchasing/point/fixed-assets/downpayment') }}" method="get" class="form-horizontal">
                    <div class="form-group">
                        <div class="col-sm-6">
                            <div class="input-group input-daterange" data-date-format="{{date_format_masking()}}">
                                <input type="text" name="date_from" class="form-control date input-datepicker"
                                       placeholder="From"
                                       value="{{\Input::get('date_from') ? \Input::get('date_from') : ''}}">
                                <span class="input-group-addon"><i class="fa fa-chevron-right"></i></span>
                                <input type="text" name="date_to" class="form-control date input-datepicker"
                                       placeholder="To"
                                       value="{{\Input::get('date_to') ? \Input::get('date_to') : ''}}">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <input type="text" name="search" class="form-control" placeholder="Search..."
                                   value="{{\Input::get('search')}}"
                                   value="{{\Input::get('search') ? \Input::get('search') : ''}}">
                        </div>
                        <div class="col-sm-3">
                            <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i
                                        class="fa fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </form>

                <br/>

                <div class="table-responsive">
                    {!! $list_downpayment->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th>Form Date</th>
                            <th>Form Number</th>
                            <th>Supplier</th>
                            <th>Amount</th>
                            <th>Remaining Amount</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($list_downpayment as $downpayment)
                            <tr>
                                <td>{{ date_format_view($downpayment->formulir->form_date) }}</td>
                                <td>
                                    <a href="{{ url('purchasing/point/fixed-assets/downpayment/'.$downpayment->id) }}">{{ $downpayment->formulir->form_number}}</a>
                                </td>
                                <td>
                                    <a href="{{ url('master/contact/supplier/'.$downpayment->supplier->id) }}">{{ $downpayment->supplier->codeName}}</a>
                                </td>
                                <td>{{ number_format_quantity($downpayment->amount) }}</td>
                                <td>{{ number_format_quantity($downpayment->remaining_amount) }}</td>
                                <td>
                                    @include('framework::app.include._approval_status_label', ['approval_status' => $downpayment->formulir->approval_status])
                                    @include('framework::app.include._form_status_label', ['form_status' => $downpayment->formulir->form_status])
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $list_downpayment->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop
