@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('ksp::app/facility/ksp/_breadcrumb')
        <li>Loan Application</li>
    </ul>

    <h2 class="sub-header">Loan Application</h2>
    @include('ksp::app.facility.ksp.loan-application._menu')

    @include('core::app.error._alert')
    
    <form action="{{url('facility/ksp/loan-application/send-request-approval')}}" method="post">
        {!! csrf_field() !!}

        <div class="panel panel-default">
            <div class="panel-body">            
                <div class="table-responsive">
                    {!! $list_loan_application->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center">
                                    <input type="checkbox" id="check-all">
                                </th>
                                                     
                                <th>Form Date</th>
                                <th>Form Number</th>
                                <th>Customer</th>
                                <th>Loan Amount</th>
                                <th>Approval To</th>
                                <th>Last Request</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($list_loan_application as $loan_application)
                             <tr id="list-{{$loan_application->formulir_id}}">
                                <td class="text-center">
                                    <input type="checkbox" name="formulir_id[]" value="{{$loan_application->formulir_id}}">
                                </td>
                                <td>{{ date_format_view($loan_application->formulir->form_date) }}</td>
                                <td><a href="{{ url('facility/ksp/loan-application/'.$loan_application->id) }}">{{ $loan_application->formulir->form_number}}</a></td>
                                <td>{{ $loan_application->customer->codeName }}</td>
                                <td>{{ number_format_quantity($loan_application->loan_amount) }}</td>
                                <td>{{ $loan_application->formulir->approvalTo->name }}</td>
                                <td>
                                @if($loan_application->formulir->request_approval_at == null)
                                    -
                                @else
                                    {{ date_format_view($loan_application->formulir->request_approval_at, true) }}
                                @endif
                                </td>
                            </tr>
                            @endforeach  
                        </tbody> 
                    </table>
                    {!! $list_loan_application->render() !!}
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-effect-ripple btn-primary">send request</button>
                    </div>
                </div>
            </div>
        </div>          
    </form>
</div>
@stop
