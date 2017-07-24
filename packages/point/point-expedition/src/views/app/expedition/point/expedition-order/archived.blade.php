@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-expedition::app/expedition/point/expedition-order/_breadcrumb')
            <li><a href="{{ url('expedition/point/expedition-order/'.$expedition_order->id) }}">{{$expedition_order->formulir->form_date}}</a></li>
            <li>Archived</li>
        </ul>
        <h2 class="sub-header">Expedition Order </h2>
        @include('point-expedition::app.expedition.point.expedition-order._menu')

        <div class="block full">
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
                            <legend>
                                <i class="fa fa-angle-right"></i> 
                                REF# <a href="{{ Point\PointExpedition\Models\ExpeditionOrderReference::where('expedition_reference_id', $reference->formulir_id)->first()->getLinkReference() }}"
                                        target="_blank">{{ $reference->formulir->form_number }}</a>
                            </legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Date</label>
                        <div class="col-md-6 content-show">
                            {{ date_format_view($expedition_order_archived->formulir->form_date, true) }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Supplier</label>
                        <div class="col-md-6 content-show">
                            {!! get_url_person($expedition_reference->person->id) !!}
                        </div>
                    </div>

                </fieldset>

                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Form</legend>
                        </div>
                    </div>
                </fieldset>
                <div class="form-group">
                    <label class="col-md-3 control-label">Delivery Date</label>
                    <div class="col-md-3 content-show">
                        {{ $expedition_order_archived->delivery_date }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Expedition</label>
                    <div class="col-md-6 content-show">
                        {!! get_url_person($expedition_order_archived->expedition->id) !!}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Notes</label>
                    <div class="col-md-6 content-show">
                        {{ $expedition_order_archived->formulir->notes }}
                    </div>
                </div>
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Item</legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="item-datatable" class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th>ITEM</th>
                                        <th class="text-right">QUANTITY</th>
                                        <th>UNIT</th>
                                    </tr>
                                    </thead>
                                    <?php $count = 0;?>
                                    @foreach($expedition_order_archived->items as $expedition_order_item)
                                        <tbody>
                                        <tr>
                                            <td>{{ $expedition_order_item->item->codeName }}</td>
                                            <td class="text-right">{{ number_format_quantity($expedition_order_item->quantity) }}</td>
                                            <td>{{ $expedition_order_item->unit }}</td>
                                        </tr>
                                        </tbody>
                                    @endforeach
                                    <tfoot>
                                    <tr>
                                        <td colspan="2"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="text-right"><strong>Total Fee</strong></td>
                                        <td>{{ number_format_quantity($expedition_order_archived->expedition_fee) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="text-right"><strong>Discount</strong></td>
                                        <td>{{ number_format_quantity($expedition_order_archived->discount) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="text-right"><strong>Tax Base</strong></td>
                                        <td>{{ number_format_quantity($expedition_order_archived->tax) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="text-right"><strong>Tax ($expedition_order_archived->type_of_tax)</strong></td>
                                        <td>{{ number_format_quantity($expedition_order_archived->tax) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="text-right"><h4><strong>Total</strong></h4></td>
                                        <td>
                                            <h4>
                                                <strong>{{ number_format_quantity($expedition_order_archived->total) }}</strong>
                                            </h4>
                                        </td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Authorized User</legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Creator</label>
                        <div class="col-md-6 content-show">
                            {{ $expedition_order_archived->formulir->createdBy->name }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Request Approval To</label>
                        <div class="col-md-6">
                            {{ $expedition_order_archived->formulir->approvalTo->name }}
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Status</legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Approval Status</label>
                        <div class="col-md-6 content-show">
                            @include('framework::app.include._approval_status_label_detailed', [
                                'approval_status' => $expedition_order_archived->formulir->approval_status,
                                'approval_message' => $expedition_order_archived->formulir->approval_message,
                                'approval_at' => $expedition_order_archived->formulir->approval_at,
                                'approval_to' => $expedition_order_archived->formulir->approvalTo->name,
                            ])
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Status</label>
                        <div class="col-md-6 content-show">
                            @include('framework::app.include._form_status_label', ['form_status' => $expedition_order_archived->formulir->form_status])
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script>
        initDatatable('#item-datatable');
    </script>
@stop
