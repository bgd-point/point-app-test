@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li><a href="{{ url('purchasing/point/payment-order/basic') }}">Payment Order</a></li>
            <li><a href="{{ url('purchasing/point/payment-order/basic/'.$payment_order->id) }}">{{$payment_order->formulir->form_number}}</a></li>
            <li>Archived</li>
        </ul>
        <h2 class="sub-header">PAYMENT ORDER </h2>
        @include('point-purchasing::app.purchasing.point.inventory.payment-order.basic._menu')

        @include('core::app.error._alert')

        <div class="block full">
            <!-- Tabs Content -->
            <div class="tab-content">
                <div class="tab-pane active" id="block-tabs-home">
                    <div class="form-horizontal form-bordered">
                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="alert alert-danger alert-dismissable">
                                    <h1 class="text-center"><strong>Archived</strong></h1>
                                </div>
                            </div>
                        </div>
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> PAYMENT ORDER</legend>
                                </div>
                            </div>
                        </fieldset>
                        <div class="form-group">
                            <label class="col-md-3 control-label">PAYMENT DATE</label>
                            <div class="col-md-6 content-show">
                                {{date_Format_view($payment_order_archived->formulir->form_date, true)}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Number</label>
                            <div class="col-md-6 content-show">
                                {{ $payment_order_archived->formulir->form_number }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Supplier</label>
                            <div class="col-md-6 content-show">
                                {!! get_url_person($payment_order_archived->supplier_id) !!}
                            </div>
                        </div>
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-vcenter">
                                            <thead>
                                            <tr>
                                                <th>DATE</th>
                                                <th>FORM NUMBER</th>
                                                <th>NOTES</th>
                                                <th class="text-right">TOTAL</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach(\Point\Framework\Helpers\ReferHelper::getRefers(get_class($payment_order_archived), $payment_order_archived->id) as $refer)
                                                <?php
                                                $payment_order_archived_detail = \Point\PointPurchasing\Models\Inventory\Basic\PaymentOrderDetail::find($refer->to_id);
                                                $reference_type = $refer->by_type;
                                                $reference = $reference_type::find($refer->by_id);
                                                ?>
                                                <tr>
                                                    <td>
                                                        {{ date_format_view($reference->formulir->form_date) }}
                                                    </td>
                                                    <td>{{ $reference->formulir->form_number }}</td>
                                                    <td>{{ $payment_order_archived_detail->detail_notes }}</td>
                                                    <td class="text-right">{{ number_format_quantity($payment_order_archived_detail->amount) }}</td>
                                                </tr>
                                            @endforeach
                                            @if($payment_order_archived->others->count())
                                            <tr>
                                                <td colspan="4"><h4><b>Others</b></h4></td>
                                            </tr>
                                            @endif
                                            @foreach($payment_order_archived->others as $payment_order_archived_other)

                                                <tr>
                                                    <td colspan="2">
                                                        {{$payment_order_archived_other->coa->account}} <br/>
                                                        <b>ALLOCATION
                                                            :</b> {{$payment_order_archived_other->allocation->name}}
                                                    </td>

                                                    <td>{{$payment_order_archived_other->other_notes}}</td>
                                                    <td class="text-right">{{number_format_quantity($payment_order_archived_other->amount)}}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                            <tfoot>
                                            <tr>
                                                <td colspan="3" class="text-right"><h4><b>TOTAL</b></h4></td>
                                                <td class="text-right">{{number_format_quantity($payment_order_archived->total_payment)}}</td>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
            </div>
            <!-- END Tabs Content -->
        </div>
    </div>
@stop

@section('scripts')
    <script>
        initDatatable('#item-datatable');
    </script>
@stop
