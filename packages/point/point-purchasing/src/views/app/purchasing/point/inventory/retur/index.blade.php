@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li>Return</li>
        </ul>
        <h2 class="sub-header">Return</h2>
        @include('point-purchasing::app.purchasing.point.inventory.retur._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{ url('purchasing/point/retur') }}" method="get" class="form-horizontal">
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
                            <input type="text" name="search" class="form-control"
                                   placeholder="Search Form Number / Supplier..." value="{{\Input::get('search')}}"
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
                    {!! $list_retur->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Form Number</th>
                            <th>Supplier</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($list_retur as $retur)
                            <tr id="list-{{$retur->formulir_id}}">
                                <td>{{ date_Format_view($retur->formulir->form_date) }}</td>
                                <td>
                                    <a href="{{ url('purchasing/point/retur/'.$retur->id) }}">{{ $retur->formulir->form_number}}</a>
                                </td>
                                <td>
                                    <a href="{{ url('master/contact/supplier/'.$retur->supplier_id) }}">{{ $retur->supplier->codeName}}</a>
                                </td>
                                <td>
                                    @include('framework::app.include._approval_status_label', ['approval_status' => $retur->formulir->approval_status])
                                    @include('framework::app.include._form_status_label', ['form_status' => $retur->formulir->form_status])
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $list_retur->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop
