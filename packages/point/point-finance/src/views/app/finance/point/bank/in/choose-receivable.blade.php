@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-finance::app.finance.point.bank._breadcrumb')
            <li>Receive Payment</li>
        </ul>
        <h2 class="sub-header">Choose Payable</h2>
        @include('point-finance::app.finance.point.bank._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <a href="{{url('finance/point/bank/in/create')}}" class="btn btn-info">Receive new payment</a>
                @if($payment_references->count())
                    <div class="table-responsive">
                        {!! $payment_references->render() !!}
                        <table class="table table-striped table-bordered">
                            <thead>
                            <tr>
                                <th width="100px" class="text-center"></th>
                                <th>Payment Date</th>
                                <th>Form Number</th>
                                <th>Payment To</th>
                                <th>Notes</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($payment_references as $payment_reference)
                                <tr>
                                    <td class="text-center">
                                        <a href="{{ url('finance/point/bank/'.$payment_reference->payment_flow.'/create/'.$payment_reference->id) }}" class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-external-link"></i> Create</a>
                                    </td>
                                    <td>{{ date_format_view($payment_reference->reference->form_date) }}</td>
                                    <td>{{ $payment_reference->reference->form_number }}</td>
                                    <td>{{ $payment_reference->person->codeName }}</td>
                                    <td>{{ $payment_reference->reference->notes }}</td>
                                </tr>
                                <tr>
                                    <th></th>
                                    <th colspan="3">Description</th>
                                    <th class="text-right">Amount</th>
                                </tr>
                                @foreach($payment_reference->detail as $payment_reference_detail)
                                    <tr>
                                        <td></td>
                                        <td colspan="3">[ {{ $payment_reference_detail->coa->account }} ] {{ $payment_reference_detail->notes_detail }}</td>
                                        <td class="text-right">{{ number_format_price($payment_reference_detail->amount) }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td></td>
                                    <td colspan="3">
                                    <td class="text-right"><b>{{ number_format_price($payment_reference->total) }}</b></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {!! $payment_references->render() !!}
                    </div>
                @endif
            </div>
        </div>
    </div>
@stop
