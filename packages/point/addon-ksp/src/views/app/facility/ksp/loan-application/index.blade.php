@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('ksp::app/facility/ksp/_breadcrumb')
        <li>Loan Application</li>
    </ul>

    <h2 class="sub-header">Loan Application</h2>
    @include('ksp::app.facility.ksp.loan-application._menu')

    <div class="panel panel-default">
        <div class="panel-body">
            <form action="{{ url('facility/ksp/buy') }}" method="get" class="form-horizontal">
                <div class="form-group">
                    <div class="col-sm-6">
                        <div class="input-group input-daterange" data-date-format="{{date_format_masking()}}">
                            <input type="text" name="date_from" class="form-control date input-datepicker" placeholder="From"  value="{{app('request')->input('date_from') ? app('request')->input('date_from') : ''}}">
                            <span class="input-group-addon"><i class="fa fa-chevron-right"></i></span>
                            <input type="text" name="date_to" class="form-control date input-datepicker" placeholder="To" value="{{app('request')->input('date_to') ? app('request')->input('date_to') : ''}}">
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <input type="text" name="search" class="form-control" placeholder="Search" value="{{app('request')->input('search')}}" value="{{app('request')->input('search') ? app('request')->input('search') : ''}}">
                    </div>
                    <div class="col-sm-3">
                        <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search</button>
                    </div>
                </div>
            </form>

            <br/>

            @if($list_loan_application->count() > 0)

            <div class="table-responsive">
                {!! $list_loan_application->render() !!}
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>                             
                            <th>Form Date</th>
                            <th>Form Number</th>
                            <th>Customer</th>
                            <th>Loan Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($list_loan_application as $loan_application)
                        <tr>
                            <td>{{ $loan_application->formulir->form_date }}</td>
                            <td><a href="{{ url('facility/ksp/loan-application/'.$loan_application->id) }}">{{ $loan_application->formulir->form_number }}</a></td>
                            <td>{{ $loan_application->customer->codeName }}</td>
                            <td>{{ number_format_price($loan_application->loan_amount) }}</td>
                        </tr>
                        @endforeach  
                    </tbody> 
                </table>
                {!! $list_loan_application->render() !!}
            </div>

            @endif

        </div>
    </div>  
</div>
@stop
