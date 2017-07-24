@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li><a href="{{ url('purchasing/point/payment-order') }}">Payment Order</a></li>
            <li>Create step 1</li>
        </ul>
        <h2 class="sub-header">PAYMENT ORDER</h2>
        @include('point-purchasing::app.purchasing.point.inventory.payment-order._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{ url('purchasing/point/payment-order/create-step-1') }}" method="get">
                    <input type="text" class="form-control" name="search_supplier" placeholder="SUPPLIER"
                        @if(app('request')->input('search_supplier')) value="{{ app('request')->input('search_supplier') }}" @endif
                    />
                    <input type="submit" name="search" id="search" value="search" class="btn btn-primary" />
                </form>

                <div class="table-responsive">
                    {!! $list_invoice->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th width="100px" class="text-center"></th>
                            <th>Supplier</th>
                            <th>From Invoice</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="3">From Invoice</td>
                        </tr>
                        @foreach($list_invoice as $invoice)
                            <tr id="list-{{$invoice->formulir_id}}">
                                <td class="text-center">
                                    <a href="{{ url('purchasing/point/payment-order/create-step-2/'.$invoice->supplier_id) }}"
                                       class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-external-link"></i>
                                        PAYMENT ORDER</a>
                                </td>
                                <td>
                                    {!! get_url_person($invoice->supplier_id) !!}
                                </td>
                                <td> {{ $invoice->getListSupplier() }}</td>
                            </tr>
                        @endforeach
                        @if(count($list_cut_off_payable) > 0)
                        <tr>
                            <td colspan="3">From Cutoff</td>
                        </tr>
                        @foreach($list_cut_off_payable as $cut_off_payable)
                        <tr id="list-{{$cut_off_payable->formulir_id}}">
                            <td class="text-center">
                                <a href="{{ url('purchasing/point/payment-order/create-step-2/'.$cut_off_payable->subledger_id) }}"
                                   class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-external-link"></i>
                                    Payment Collection</a>
                            </td>
                            <td>
                                {!! get_url_person($cut_off_payable->person->id) !!}
                            </td>
                            <td>
                                {{date_format_view($cut_off_payable->cutoffPayable->formulir->form_date)}}
                                <a href="{{url('accounting/point/cut-off/payable/'.$cut_off_payable->cutoffPayable->id)}}"> {{$cut_off_payable->cutoffPayable->formulir->form_number}}</a>
                            </td>
                        </tr>
                        @endforeach
                        @endif
                        </tbody>
                    </table>
                    {!! $list_invoice->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop
