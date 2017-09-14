@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-expedition::app/expedition/point/expedition-order/_breadcrumb')
            <li>Show</li>
        </ul>
        <h2 class="sub-header">Expedition Order </h2>
        @include('point-expedition::app.expedition.point.expedition-order._menu')

        <div class="block full">
            <!-- Block Tabs Title -->
            <div class="block-title">
                <ul class="nav nav-tabs" data-toggle="tabs">
                    <li class="active"><a href="#block-tabs-home">Form</a></li>
                    <li><a href="#block-tabs-settings"><i class="gi gi-settings"></i></a></li>
                </ul>
            </div>
            <!-- END Block Tabs Title -->

            <!-- Tabs Content -->
            <div class="tab-content">
                <div class="tab-pane active" id="block-tabs-home">
                    <div class="form-horizontal form-bordered">

                        <fieldset>
                            <div class="form-group pull-right">
                                <div class="col-md-12">
                                    @include('framework::app.include._approval_status_label', [
                                        'approval_status' => $expedition_order->formulir->approval_status,
                                        'approval_message' => $expedition_order->formulir->approval_message,
                                        'approval_at' => $expedition_order->formulir->approval_at,
                                        'approval_to' => $expedition_order->formulir->approvalTo->name,
                                    ])
                                    @include('framework::app.include._form_status_label', ['form_status' => $expedition_order->formulir->form_status])
                                </div>
                            </div>
                        </fieldset>

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
                                    {{ date_format_view($expedition_order->formulir->form_date, true) }}
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
                            <label class="col-md-3 control-label">Form Number</label>
                            <div class="col-md-3 content-show">
                                {{ $expedition_order->formulir->form_number }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Delivery Date</label>
                            <div class="col-md-3 content-show">
                                {{ date_format_view($expedition_order->delivery_date) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Expedition</label>
                            <div class="col-md-6 content-show">
                                {!! get_url_person($expedition_order->expedition->id) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Notes</label>
                            <div class="col-md-6 content-show">
                                {{ $expedition_order->formulir->notes }}
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
                                            @foreach($expedition_order->items as $expedition_order_item)
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
                                                <td colspan="2" class="text-right">
                                                    <strong>Subtotal</strong>
                                                </td>
                                                <td class="text-right">{{ number_format_quantity($expedition_order->expedition_fee) }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" class="text-right"><strong>Discount</strong></td>
                                                <td class="text-right">{{ number_format_quantity($expedition_order->discount) }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" class="text-right"><strong>Tax Base</strong></td>
                                                <td class="text-right">{{ number_format_quantity($expedition_order->tax_base) }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" class="text-right">
                                                    <strong>Tax</strong>
                                                    ({{ $expedition_order->type_of_tax }})
                                                </td>
                                                <td class="text-right">{{ number_format_quantity($expedition_order->tax) }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" class="text-right"><h4><strong>Total</h4></strong></td>
                                                <td class="text-right">
                                                    <h4><strong>{{ number_format_quantity($expedition_order->total) }}</strong></h4>
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
                                    {{ $expedition_order->formulir->createdBy->name }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Request Approval To</label>
                                <div class="col-md-6 content-show">
                                    {{ $expedition_order->formulir->approvalTo->name }}
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
                                @if(formulir_view_edit($expedition_order->formulir, 'update.point.expedition.order'))
                                    <a href="{{url('expedition/point/expedition-order/'.$expedition_order->id.'/edit')}}"
                                       class="btn btn-effect-ripple btn-info">
                                        <i class="fa fa-pencil"></i> Edit
                                    </a>
                                @endif
                                @if(formulir_view_cancel($expedition_order->formulir, 'delete.point.expedition.order'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureCancelForm('{{url('formulir/cancel')}}',
                                               '{{ $expedition_order->formulir_id }}',
                                               'delete.point.expedition.order')">
                                        <i class="fa fa-times"></i>
                                        Cancel Form
                                    </a>
                                @endif
                                @if(formulir_view_close($expedition_order->formulir, 'update.point.expedition.order'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureCloseForm({{$expedition_order->formulir_id}},'{{url('formulir/close')}}')">
                                        Close Form
                                    </a>
                                @endif
                                @if(formulir_view_reopen($expedition_order->formulir, 'update.point.expedition.order'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureReopenForm({{$expedition_order->formulir_id}},'{{url('formulir/reopen')}}')">
                                        Reopen Form
                                    </a>
                                @endif
                                @if($expedition_order->formulir->approval_status == 1 && $expedition_order->formulir->form_status == 0 && $expedition_order->is_finish == 0)
                                <a href="{{url('expedition/point/expedition-order/create-step-2/'.$reference->formulir_id.'/?group='.$expedition_order->id)}}" class="btn btn-effect-ripple btn-info"> 
                                    Continue to Other Expedition
                                </a>
                                <a href="{{url('expedition/point/expedition-order/finish/'.$expedition_order->id)}}" class="btn btn-effect-ripple btn-info"> 
                                    Finish
                                </a>
                                @endif
                                @if(formulir_view_email_vendor($expedition_order->formulir, 'create.point.expedition.order'))
                                    <form action="{{url('expedition/point/expedition-order/send-email-order')}}" method="post">
                                        {!! csrf_field() !!}
                                        <input type="hidden" readonly="" name="expedition_order_id" value="{{$expedition_order->id}}">
                                        <input type="submit" class="btn btn-primary" value="Send Email To Expedition">
                                    </form>
                                @endif
                            </div>
                        </div>
                    </fieldset>

                    @if(formulir_view_approval($expedition_order->formulir, 'approval.point.expedition.order'))
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Approval Action</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-6">
                                    <form action="{{url('expedition/point/expedition-order/'.$expedition_order->id.'/approve')}}" method="post">
                                        {!! csrf_field() !!}
                                        <div class="input-group">
                                            <input type="text" name="approval_message" class="form-control" placeholder="Message">
                                            <span class="input-group-btn">
                                                <input type="submit" class="btn btn-primary" value="Approve">
                                            </span>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-md-6">
                                    <form action="{{url('expedition/point/expedition-order/'.$expedition_order->id.'/reject')}}" method="post">
                                        {!! csrf_field() !!}

                                        <div class="input-group">
                                            <input type="text" name="approval_message" class="form-control" placeholder="Message">
                                            <span class="input-group-btn">
                                                <input type="submit" class="btn btn-danger" value="Reject">
                                            </span>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </fieldset>
                    @endif
                    @if($list_expedition_order_archived->count() > 0)
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Archived Form</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-12 content-show">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                            <tr>
                                                <th></th>
                                                <th>Form Date</th>
                                                <th>Form Number</th>
                                                <th>Created By</th>
                                                <th>Updated By</th>
                                                <th>Reason</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php $count = 0;?>
                                            @foreach($list_expedition_order_archived as $expedition_order_archived)
                                                <tr>
                                                    <td class="text-center">
                                                        <a href="{{ url('expedition/point/expedition-order/'.$expedition_order_archived->id.'/archived') }}"
                                                           data-toggle="tooltip" title="Show"
                                                           class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-file"></i> {{ 'Revision ' . $count++ }}
                                                        </a>
                                                    </td>
                                                    <td>{{ date_format_view($expedition_order_archived->formulir->form_date) }}</td>
                                                    <td>{{ $expedition_order_archived->formulir->archived }}</td>
                                                    <td>{{ $expedition_order_archived->formulir->createdBy->name }}</td>
                                                    <td>{{ $expedition_order_archived->formulir->updatedBy->name }}</td>
                                                    <td>{{ $expedition_order_archived->formulir->edit_notes }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
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
    <script>
        var item_table = initDatatable('#item-datatable');
    </script>
@stop
