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
                            <th>Remaining</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="3">From Invoice</td>
                        </tr>
                        @foreach($list_invoice as $invoice)
                            <?php
                            $list_invoice_by_person = \Point\PointPurchasing\Models\Inventory\Invoice::joinFormulir()
                                ->joinSupplier()
                                ->notArchived()
                                ->where('supplier_id', '=', $invoice->supplier_id)
                                ->selectOriginal()
                                ->orderByStandard()
                                ->get();
                            ?>
                            @foreach($list_invoice_by_person as $invoice_by_person)
                            <?php
                            $amount = number_format_price(\Point\Framework\Helpers\ReferHelper::remaining(get_class($invoice_by_person), $invoice_by_person->id, $invoice_by_person->total), 0);
                            ?>
                            @if ($amount > 0)
                            <tr id="list-{{$invoice->formulir_id}}">
                                <td class="text-center">
                                    <a href="{{ url('purchasing/point/payment-order/create-step-2/'.$invoice->supplier_id) }}"
                                       class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-external-link"></i>
                                        PAYMENT ORDER</a>
                                </td>
                                <td>
                                    {!! get_url_person($invoice->supplier_id) !!}
                                </td>
                                <td>{{date_format_view($invoice_by_person->formulir->form_date)}}</td>
                                <td><a href="{{url('sales/point/indirect/invoice/'.$invoice_by_person->id)}}">{{$invoice_by_person->formulir->form_number}}</a></td>
                                <td style="text-align: right">{{ $amount }}</td>
                            </tr>
                            @endif
                            @endforeach
                        @endforeach
                        @if(count($list_cut_off_payable) > 0)
                        <tr>
                            <td colspan="3">From Cutoff</td>
                        </tr>
                        @foreach($list_cut_off_payable as $cut_off_payable)
                        <?php
                        $cut_off_payable_detail = $cut_off_payable->reference_type::find($cut_off_payable->reference_id);
                        $reference_payable = Point\PointAccounting\Models\CutOffPayable::find($cut_off_payable_detail->cut_off_payable_id);
                        ?>
                        <tr>
                            <td class="text-center">
                                <a href="{{ url('purchasing/point/payment-order/create-step-2/'.$cut_off_payable->person_id) }}"
                                   class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-external-link"></i>
                                    Payment Collection</a>
                            </td>
                            <td>
                                {!! get_url_person($cut_off_payable->person_id) !!}
                            </td>
                            <td>
                                {{date_format_view($reference_payable->formulir->form_date)}}
                                <a href="{{url('accounting/point/cut-off/payable/'.$reference_payable->id)}}"> {{$reference_payable->formulir->form_number}}</a>
                            </td>
                            <td></td>
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
