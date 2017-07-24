@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-accounting::app/accounting/point/cut-off/payable/_breadcrumb')
        <li>Index</li>
    </ul>
    <h2 class="sub-header">Cut Off Account Payable</h2>
    @include('point-accounting::app.accounting.point.cut-off.payable._menu')

    <div class="panel panel-default">
        <div class="panel-body">
            <form action="{{ url('accounting/point/cut-off/payable') }}" method="get" class="form-horizontal">
                <div class="form-group">
                    <div class="col-sm-6">
                        <div class="input-group input-daterange" data-date-format="{{date_format_masking()}}">
                            <input type="text" name="date_from" class="form-control date input-datepicker" placeholder="From"  value="{{\Input::get('date_from') ? \Input::get('date_from') : ''}}">
                            <span class="input-group-addon"><i class="fa fa-chevron-right"></i></span>
                            <input type="text" name="date_to" class="form-control date input-datepicker" placeholder="To" value="{{\Input::get('date_to') ? \Input::get('date_to') : ''}}">
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <input type="text" name="search" class="form-control" placeholder="Search..." value="{{\Input::get('search')}}" value="{{\Input::get('search') ? \Input::get('search') : ''}}">
                    </div>
                    <div class="col-sm-3">
                        <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search</button>
                    </div>
                </div>
            </form>

            <br/>

            <div class="table-responsive">
                {!! $list_cut_off->appends(['search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Form Date</th>
                            <th>Form Number</th>
                            <th>Form Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($list_cut_off as $cut_off)
                        <tr>
                            <td>{{ date_format_view($cut_off->formulir->form_date) }}</td>
                            <td><a href="{{ url('accounting/point/cut-off/payable/'.$cut_off->id) }}">{{ $cut_off->formulir->form_number}}</a></td>
                            <td>
                                @include('framework::app.include._approval_status_label', ['approval_status' => $cut_off->formulir->approval_status])
                                @include('framework::app.include._form_status_label', ['form_status' => $cut_off->formulir->form_status])
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {!! $list_cut_off->appends(['search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
            </div>
        </div>
    </div>
</div>
@stop
