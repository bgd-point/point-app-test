@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li><a href="{{ url('purchasing/point/purchase-requisition') }}">Purchase Requisition</a></li>
            <li>Show</li>
        </ul>
        <h2 class="sub-header">Purchase Requisition</h2>
        @include('point-purchasing::app.purchasing.point.inventory.purchase-requisition._menu')

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

                                    @include('framework::app.include._form_status_label', ['form_status' => $purchase_requisition->formulir->form_status])

                                    @include('framework::app.include._approval_status_label_detailed', [
                                        'approval_status' => $purchase_requisition->formulir->approval_status,
                                        'approval_message' => $purchase_requisition->formulir->approval_message,
                                        'approval_at' => $purchase_requisition->formulir->approval_at,
                                        'approval_to' => $purchase_requisition->formulir->approvalTo->name,
                                    ])

                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Form</legend>
                                </div>
                            </div>
                        </fieldset>
                        @if($revision > 0)
                            <div class="form-group">
                                <label class="col-md-3 control-label">Revision</label>
                                <div class="col-md-6 content-show">
                                    {{ $revision }}
                                </div>
                            </div>
                        @endif
                        <div class="">
                            <fieldset>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Form Number</label>
                                    <div class="col-md-6 content-show">
                                        {{ $purchase_requisition->formulir->form_number }}
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Form Date</label>
                                    <div class="col-md-6 content-show">
                                        {{ date_format_view($purchase_requisition->formulir->form_date, false) }}
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Required Date</label>
                                    <div class="col-md-6 content-show">
                                        {{ date_format_view($purchase_requisition->required_date, false) }}
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Employee </label>
                                    <div class="col-md-6 content-show">
                                        {!! get_url_person($purchase_requisition->employee_id) !!}
                                    </div>
                                </div>
                                @if($purchase_requisition->supplier_id)
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Supplier </label>
                                    <div class="col-md-6 content-show">
                                        {!! get_url_person($purchase_requisition->supplier->id) !!}
                                    </div>
                                </div>
                                @endif
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Notes</label>
                                    <div class="col-md-6 content-show">
                                        {{ $purchase_requisition->formulir->notes }}
                                    </div>
                                </div>
                            </fieldset>
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
                                                    <th class="text-right">PRICE</th>
                                                    <th>ALLOCATION</th>
                                                    <th>NOTES</th>
                                                </tr>
                                                </thead>
                                                <tbody class="manipulate-row">
                                                @foreach($purchase_requisition->items as $purchase_requisition_item)
                                                    <tr>
                                                        <td>{{ $purchase_requisition_item->item->codeName }}</td>
                                                        <td class="text-right">{{ number_format_quantity($purchase_requisition_item->quantity, 0) }}</td>
                                                        <td>{{ $purchase_requisition_item->unit }}</td>
                                                        <td class="text-right">{{ number_format_quantity($purchase_requisition_item->price) }}</td>
                                                        <td>{{ $purchase_requisition_item->allocation->name }}</td>
                                                        <td>{{ $purchase_requisition_item->item_notes }}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                                <tfoot>
                                                <tr>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
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
                                        {{ $purchase_requisition->formulir->createdBy->name }}
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Request Approval To</label>

                                    <div class="col-md-6 content-show">
                                        {{ $purchase_requisition->formulir->approvalTo->name }}
                                    </div>
                                </div>
                            </fieldset>
                        </div>
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
                                @if(formulir_view_edit($purchase_requisition->formulir, 'update.point.purchasing.requisition'))
                                    <a href="{{url('purchasing/point/purchase-requisition/'.$purchase_requisition->id.'/edit')}}"
                                       class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                                @endif
                                @if(formulir_view_cancel($purchase_requisition->formulir, 'delete.point.purchasing.requisition'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureCancelForm('{{url('formulir/cancel')}}',
                                               '{{ $purchase_requisition->formulir_id }}',
                                               'delete.point.purchasing.requisition')"><i class="fa fa-times"></i>
                                        Cancel Form</a>
                                @endif
                                @if(formulir_view_close($purchase_requisition->formulir, 'update.point.purchasing.requisition'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureCloseForm({{$purchase_requisition->formulir_id}},'{{url('formulir/close')}}')">Close
                                        Form</a>
                                @endif
                                @if(formulir_view_reopen($purchase_requisition->formulir, 'update.point.purchasing.requisition'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureReopenForm({{$purchase_requisition->formulir_id}},'{{url('formulir/reopen')}}')">Reopen
                                        Form</a>
                                @endif
                                @if(formulir_view_email_vendor($purchase_requisition->formulir, 'create.point.purchasing.requisition'))
                                    <form action="{{url('purchasing/point/purchase-requisition/send-email-requisition')}}" method="post">
                                        {!! csrf_field() !!}
                                        <input type="hidden" readonly="" name="purchase_requisition_id" value="{{$purchase_requisition->id}}">
                                        <input type="submit" class="btn btn-primary" value="Send Email Requisition">
                                    </form>
                                @endif
                            </div>
                        </div>
                    </fieldset>

                    @if(formulir_view_approval($purchase_requisition->formulir, 'approval.point.purchasing.requisition'))
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Approval Action</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-6">
                                    <form action="{{url('purchasing/point/purchase-requisition/'.$purchase_requisition->id.'/approve')}}"
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
                                    <form action="{{url('purchasing/point/purchase-requisition/'.$purchase_requisition->id.'/reject')}}"
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

                    @if($list_purchase_requisition_archived->count() > 0)
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
                                            @foreach($list_purchase_requisition_archived as $purchase_requisition_archived)
                                                <tr>
                                                    <td class="text-center">
                                                        <a href="{{ url('purchasing/point/purchase-requisition/'.$purchase_requisition_archived->id.'/archived') }}"
                                                           data-toggle="tooltip" title="Show"
                                                           class="btn btn-effect-ripple btn-xs btn-info"><i
                                                                    class="fa fa-file"></i> {{ 'Revision ' . $count++ }}
                                                        </a>
                                                    </td>
                                                    <td>{{ date_format_view($purchase_requisition->formulir->form_date) }}</td>
                                                    <td>{{ $purchase_requisition_archived->formulir->archived }}</td>
                                                    <td>{{ $purchase_requisition_archived->formulir->createdBy->name }}</td>
                                                    <td>{{ $purchase_requisition_archived->formulir->updatedBy->name }}</td>
                                                    <td>{{ $purchase_requisition_archived->formulir->edit_notes }}</td>
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
