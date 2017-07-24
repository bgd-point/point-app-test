@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li><a href="{{ url('purchasing/point/retur') }}">Retur</a></li>
            <li>Show</li>
        </ul>
        <h2 class="sub-header">Return </h2>
        @include('point-purchasing::app.purchasing.point.inventory.retur._menu')

        @include('core::app.error._alert')

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
                                        'approval_status' => $retur->formulir->approval_status,
                                        'approval_message' => $retur->formulir->approval_message,
                                        'approval_at' => $retur->formulir->approval_at,
                                        'approval_to' => $retur->formulir->approvalTo->name,
                                    ])
                                    @include('framework::app.include._form_status_label', ['form_status' => $retur->formulir->form_status])
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> INFO REFERENCE</legend>
                                </div>
                            </div>
                        </fieldset>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Date</label>

                            <div class="col-md-6 content-show">
                                {{date_Format_view($invoice->formulir->form_date, true)}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Number</label>

                            <div class="col-md-6 content-show">
                                <a href="{{url('purchasing/point/invoice/'.$invoice->id)}}">{{$invoice->formulir->form_number}}</a>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Supplier</label>

                            <div class="col-md-6 content-show">
                                <input type="hidden" name="supplier_id" value="{{$invoice->supplier_id}}">
                                <a href="{{url('master/contact/supplier/'.$invoice->supplier_id)}}">{{$invoice->supplier->codeName}}</a>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Notes</label>

                            <div class="col-md-6 content-show">
                                {{$invoice->formulir->notes}}
                            </div>
                        </div>

                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Return Form</legend>
                                </div>
                            </div>
                        </fieldset>

                        @if($revision)
                            <div class="form-group">
                                <label class="col-md-3 control-label">Revision</label>

                                <div class="col-md-6 content-show">
                                    {{ $revision }}
                                </div>
                            </div>
                        @endif
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Number</label>

                            <div class="col-md-6 content-show">
                                {{ $retur->formulir->form_number }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Date</label>

                            <div class="col-md-6 content-show">
                                {{ date_Format_view($retur->formulir->form_date, true) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Supplier</label>

                            <div class="col-md-6 content-show">
                                <a href="{{url('master/contact/supplier/'.$retur->supplier_id)}}">{{$retur->supplier->codeName}}</a>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Notes</label>

                            <div class="col-md-6 content-show">
                                {{ $retur->formulir->notes }}
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
                                                <th class="text-right">QUANTITY RECEIVED</th>
                                                <th class="text-right">QUANTITY RETUR</th>
                                                <th>UNIT</th>
                                                <th class="text-right">PRICE</th>
                                                <th class="text-right">DISCOUNT</th>
                                                <th class="text-right">TOTAL</th>
                                            </tr>
                                            </thead>
                                            <tbody class="manipulate-row">
                                            @foreach($invoice->items as $invoice_item)
                                                <?php
                                                $refer_to = \Point\Framework\Helpers\ReferHelper::getReferTo(get_class($invoice_item),
                                                        $invoice_item->id,
                                                        get_class($retur),
                                                        $retur->id);
                                                ?>
                                                <tr>
                                                    <td>
                                                        <a href="{{ url('master/item/'.$invoice_item->item_id) }}">{{ $invoice_item->item->codeName }}</a>
                                                        <input type="hidden" name="item_id[]"
                                                               value="{{$invoice_item->item_id}}"/>
                                                    </td>
                                                    <td class="text-right">{{ number_format_quantity($invoice_item->quantity) }}</td>
                                                    <td class="text-right">{{ number_format_quantity($refer_to->quantity) }}</td>
                                                    <td>{{ $refer_to->unit }}</td>
                                                    <td class="text-right">{{ number_format_quantity($refer_to->price) }}</td>
                                                    <td class="text-right">{{ number_format_quantity($refer_to->discount) }}</td>
                                                    <td class="text-right">{{ number_format_quantity(($refer_to->quantity * $refer_to->price) - ($refer_to->quantity * $refer_to->discount)) }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                            <tfoot>
                                            <tr>
                                                <td colspan="6" class="text-right">SUB TOTAL</td>
                                                <td class="text-right">{{ number_format_quantity($retur->subtotal) }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="6" class="text-right">DISCOUNT</td>
                                                <td class="text-right">{{ number_format_quantity($retur->discount) }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="6" class="text-right">TAX BASE</td>
                                                <td class="text-right">{{ number_format_quantity($retur->tax_base) }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="6" class="text-right">TAX</td>
                                                <td class="text-right">{{ number_format_quantity($retur->tax) }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="6" class="text-right">EXPEDITION FEE</td>
                                                <td class="text-right">{{ number_format_quantity($retur->expedition_fee) }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="6" class="text-right">TOTAL</td>
                                                <td class="text-right">{{ number_format_quantity($retur->total) }}</td>
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
                                    <legend><i class="fa fa-angle-right"></i> PERSON IN CHARGE</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">FORM CREATOR</label>

                                <div class="col-md-6 content-show">
                                    {{ $retur->formulir->createdBy->name }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">ARK APPROVAL TO</label>

                                <div class="col-md-6 content-show">
                                    {{ $retur->formulir->approvalTo->name }}
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
                                @if(formulir_view_edit($retur->formulir, 'update.point.purchasing.return'))
                                    <a href="{{url('purchasing/point/retur/'.$retur->id.'/edit')}}"
                                       class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                                @endif
                                @if(formulir_view_cancel($retur->formulir, 'delete.point.purchasing.return'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureCancelForm('{{url('formulir/cancel')}}',
                                               '{{ $retur->formulir_id }}',
                                               'delete.point.purchasing.return')"><i class="fa fa-times"></i> Cancel
                                        Form</a>
                                @endif
                                @if(formulir_view_close($retur->formulir, 'update.point.purchasing.return'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureCloseForm({{$retur->formulir_id}},'{{url('formulir/close')}}')">Close
                                        Form</a>
                                @endif
                                @if(formulir_view_reopen($retur->formulir, 'update.point.purchasing.return'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureReopenForm({{$retur->formulir_id}},'{{url('formulir/reopen')}}')">Reopen
                                        Form</a>
                                @endif
                            </div>
                        </div>
                    </fieldset>

                    @if(formulir_view_approval($retur->formulir, 'approval.point.purchasing.return'))
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Approval Action</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-6">
                                    <form action="{{url('purchasing/point/retur/'.$retur->id.'/approve')}}"
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
                                    <form action="{{url('purchasing/point/retur/'.$retur->id.'/reject')}}"
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

                    @if($list_retur_archived->count() > 0)
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
                                            @foreach($list_retur_archived as $retur_archived)
                                                <tr>
                                                    <td class="text-center">
                                                        <a href="{{ url('purchasing/point/retur/'.$retur_archived->id.'/archived') }}"
                                                           data-toggle="tooltip" title="Show"
                                                           class="btn btn-effect-ripple btn-xs btn-info"><i
                                                                    class="fa fa-file"></i> {{ 'Revision ' . $count++ }}
                                                        </a>
                                                    </td>
                                                    <td>{{ date_format_view($retur->formulir->form_date) }}</td>
                                                    <td>{{ $retur_archived->formulir->archived }}</td>
                                                    <td>{{ $retur_archived->formulir->createdBy->name }}</td>
                                                    <td>{{ $retur_archived->formulir->updatedBy->name }}</td>
                                                    <td>{{ $retur_archived->formulir->edit_notes }}</td>
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
        initDatatable('#item-datatable');
    </script>
@stop
