@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-finance::app.finance.point.cash._breadcrumb')
            <li>Make a Payment</li>
        </ul>
        <h2 class="sub-header">Choose Payable</h2>
        @include('point-finance::app.finance.point.cash._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                @if(! $payment_references->count())
                    Please make a payment order first
                @endif
                @if($payment_references->count())
                    <div class="table-responsive">
                        {!! $payment_references->appends(['search'=>app('request')->get('search')])->render() !!}
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
                            <?php 
                                $show = false;
                                foreach($payment_reference->detail as $payment_reference_detail) {
                                    if (auth()->user()->name !== 'lioni' && preg_match('/lioni/i', $payment_reference_detail->coa->name)) {
                                        $show = true;
                                    }
                                }
                            ?>
                                @if($payment_reference->reference->form_status != -1 && show === true)
                                    <tr>
                                        <td class="text-center">
                                            <a href="{{ url('finance/point/cash/'.$payment_reference->payment_flow.'/create/'.$payment_reference->id) }}" class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-external-link"></i> Create</a>
                                        </td>
                                        <td>{{ date_format_view($payment_reference->reference->form_date) }}</td>
                                        <td>{!! formulir_url($payment_reference->reference) !!}</td>
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
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                        {!! $payment_references->appends(['search'=>app('request')->get('search')])->render() !!}
                    </div>
                @endif
            </div>
        </div>
    </div>
@stop
