@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li><a href="{{ url('purchasing/point/payment-order') }}">Payment Order</a></li>
            <li><a href="{{ url('purchasing/point/payment-order/'.$payment_order->id) }}">{{$payment_order->formulir->form_number}}</a></li>
            <li>Archived</li>
        </ul>
        <h2 class="sub-header">PAYMENT ORDER </h2>
        @include('point-purchasing::app.purchasing.point.inventory.payment-order._menu')

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
                                {{ $payment_order_archived->formulir->archived }}
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
                                        <table class="table table-striped">
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
                                                $payment_order_archived_detail = \Point\PointPurchasing\Models\Inventory\PaymentOrderDetail::find($refer->to_id);
                                                $reference_type = $refer->by_type;
                                                $reference = $reference_type::find($refer->by_id);
                                                if (get_class($reference) == get_class(new Point\PointAccounting\Models\CutOffPayableDetail())) {
                                                    $reference->formulir = $reference->cutoffPayable->formulir;
                                                }
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

                                            <tr>
                                                <td colspan="4"><h4><b>Others</b></h4></td>
                                            </tr>
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

                <div class="tab-pane" id="block-tabs-settings">
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Action</legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                @if(formulir_view_edit($payment_order_archived->formulir, 'update.point.purchasing.payment.order'))
                                    <a href="{{url('purchasing/point/payment-order/'.$payment_order_archived->id.'/edit')}}"
                                       class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                                @endif
                                @if(formulir_view_cancel($payment_order_archived->formulir, 'delete.point.purchasing.payment.order'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureCancelForm('{{url('formulir/cancel')}}',
                                               '{{ $payment_order_archived->formulir_id }}',
                                               'delete.point.purchasing.payment.order')"><i class="fa fa-times"></i>
                                        Cancel Form</a>
                                @endif
                                @if(formulir_view_close($payment_order_archived->formulir, 'update.point.purchasing.payment.order'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureCloseForm({{$payment_order_archived->formulir_id}},'{{url('formulir/close')}}')">Close
                                        Form</a>
                                @endif
                                @if(formulir_view_reopen($payment_order_archived->formulir, 'update.point.purchasing.payment.order'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureReopenForm({{$payment_order_archived->formulir_id}},'{{url('formulir/reopen')}}')">Reopen
                                        Form</a>
                                @endif
                            </div>
                        </div>
                    </fieldset>

                    @if(formulir_view_approval($payment_order_archived->formulir, 'approval.point.purchasing.payment.order'))
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Approval Action</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-6">
                                    <form action="{{url('purchasing/point/payment-order/'.$payment_order_archived->id.'/approve')}}"
                                          method="post">
                                        {!! csrf_field() !!}

                                        <div class="input-group">
                                            <input type="text" name="approval_message" class="form-control"
                                                   placeholder="Message">
                                            <span class="input-group-btn">
                                        <input type="submit" class="btn btn-primary" value="Approve">
                                    </span>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-md-6">
                                    <form action="{{url('purchasing/point/payment-order/'.$payment_order_archived->id.'/reject')}}"
                                          method="post">
                                        {!! csrf_field() !!}

                                        <div class="input-group">
                                            <input type="text" name="approval_message" class="form-control"
                                                   placeholder="Message">
                                            <span class="input-group-btn">
                                        <input type="submit" class="btn btn-danger" value="Reject">
                                    </span>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </fieldset>
                    @endif
                </div>
            </div>
            <!-- END Tabs Content -->
        </div>
    </div>
@stop

@section('scripts')
    <style>
        tbody.manipulate-row:after {
            content: '';
            display: block;
            height: 100px;
        }
    </style>
    <script>
        var item_table = $('#item-datatable').DataTable({
            bSort: false,
            bPaginate: false,
            bInfo: false,
            bFilter: false,
            bScrollCollapse: false,
            scrollX: true
        });
    </script>
@stop
