@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-sales::app.sales.point.sales._breadcrumb')
            <li><a href="{{ url('sales/point/indirect/sales-quotation') }}">Sales Quotation</a></li>
            <li>Show</li>
        </ul>
        <h2 class="sub-header">Sales Quotation</h2>
        @include('point-sales::app.sales.point.sales.sales-quotation._menu')

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

                                    @include('framework::app.include._form_status_label', ['form_status' => $sales_quotation->formulir->form_status])

                                    @include('framework::app.include._approval_status_label_detailed', [
                                        'approval_status' => $sales_quotation->formulir->approval_status,
                                        'approval_message' => $sales_quotation->formulir->approval_message,
                                        'approval_at' => $sales_quotation->formulir->approval_at,
                                        'approval_to' => $sales_quotation->formulir->approvalTo->name,
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
                                        {{ $sales_quotation->formulir->form_number }}
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Form Date</label>

                                    <div class="col-md-6 content-show">
                                        {{ date_format_view($sales_quotation->formulir->form_date, false) }}
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Customer</label>
                                    <div class="col-md-6 content-show">
                                        {!! get_url_person($sales_quotation->person->id) !!}
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Notes</label>

                                    <div class="col-md-6 content-show">
                                        {{ $sales_quotation->formulir->notes }}
                                    </div>
                                </div>
                            </fieldset>
                            <fieldset>
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <legend><i class="fa fa-angle-right"></i> Item</legend>
                                    </div>
                                </div>
                            </fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table id="item-datatable" class="table table-striped">
                                            <thead>
                                            <tr>
                                                <th>ITEM</th>
                                                <th>ALLOCATION</th>
                                                <th>QUANTITY</th>
                                                <th>UNIT</th>
                                                <th>PRICE</th>
                                                <th>DISCOUNT (%)</th>
                                                <th>TOTAL</th>
                                            </tr>
                                            </thead>
                                            <tbody class="manipulate-row">
                                            @foreach($sales_quotation->items as $sales_quotation_item)
                                                <tr>
                                                    <td>{{ $sales_quotation_item->item->codeName }}</td>
                                                    <td>{{ $sales_quotation_item->allocation_id ? $sales_quotation_item->allocation->name : 'no allocation' }}</td>
                                                    <td>{{ number_format_quantity($sales_quotation_item->quantity) }}</td>
                                                    <td>{{ $sales_quotation_item->unit }}</td>
                                                    <td>{{ number_format_quantity($sales_quotation_item->price) }}</td>
                                                    <td>{{ number_format_quantity($sales_quotation_item->discount) }}</td>
                                                    <td class="text-right">{{ number_format_quantity($sales_quotation_item->quantity * $sales_quotation_item->price - $sales_quotation_item->quantity * $sales_quotation_item->price/100 * $sales_quotation_item->discount) }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                            <tfoot>
                                            <tr>
                                                <td colspan="6" class="text-right">SUBTOTAL</td>
                                                <td class="text-right">{{ number_format_quantity($sales_quotation->subtotal) }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="6" class="text-right">DISCOUNT (%)</td>
                                                <td class="text-right">{{ number_format_quantity($sales_quotation->discount) }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="6" class="text-right">TAX BASE</td>
                                                <td class="text-right">{{ number_format_quantity($sales_quotation->tax_base) }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="6" class="text-right">TAX ({{ $sales_quotation->type_of_tax }}
                                                    )
                                                </td>
                                                <td class="text-right">{{ number_format_quantity($sales_quotation->tax) }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="6" class="text-right">EXPEDITION FEE</td>
                                                <td class="text-right">{{ number_format_quantity($sales_quotation->expedition_fee) }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="6" class="text-right">TOTAL</td>
                                                <td class="text-right">{{ number_format_quantity($sales_quotation->total) }}</td>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <fieldset>
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <legend><i class="fa fa-angle-right"></i> Authorized User</legend>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Form Creator</label>

                                    <div class="col-md-6 content-show">
                                        {{ $sales_quotation->formulir->createdBy->name }}
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Request Approval To</label>

                                    <div class="col-md-6 content-show">
                                        {{ $sales_quotation->formulir->approvalTo->name }}
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
                                @if(formulir_view_edit($sales_quotation->formulir, 'update.point.sales.quotation'))
                                    <a href="{{url('sales/point/indirect/sales-quotation/'.$sales_quotation->id.'/edit')}}"
                                       class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                                @endif

                                @if(formulir_view_cancel_or_request_cancel($sales_quotation->formulir, 'delete.point.sales.quotation', 'approval.point.sales.quotation') == 1)
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger" onclick="secureCancelForm('{{url('formulir/cancel')}}', '{{ $sales_quotation->formulir_id }}','approval.point.sales.quotation')">
                                        <i class="fa fa-times"></i> 
                                        Cancel Form
                                    </a>
                                @elseif(formulir_view_cancel_or_request_cancel($sales_quotation->formulir, 'delete.point.sales.quotation', 'approval.point.sales.quotation') == 2)
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger" onclick="secureRequestCancelForm(this, '{{url('formulir/requestCancel')}}', '{{ $sales_quotation->formulir_id }}', 'delete.point.sales.quotation')">
                                        <i class="fa fa-times"></i> 
                                        Request Cancel Form
                                    </a>
                                @endif

                                @if(formulir_view_close($sales_quotation->formulir, 'update.point.sales.quotation'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureCloseForm({{$sales_quotation->formulir_id}},'{{url('formulir/close')}}')">Close
                                        Form</a>
                                @endif
                                @if(formulir_view_reopen($sales_quotation->formulir, 'update.point.sales.quotation'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureReopenForm({{$sales_quotation->formulir_id}},'{{url('formulir/reopen')}}')">Reopen
                                        Form</a>
                                @endif
                                @if(formulir_view_email_vendor($sales_quotation->formulir, 'create.point.sales.quotation'))
                                    <form action="{{url('sales/point/indirect/sales-quotation/send-email-quotation')}}" method="post">
                                        {!! csrf_field() !!}
                                        <input type="hidden" readonly="" name="sales_quotation_id" value="{{$sales_quotation->id}}">
                                        <input type="submit" class="btn btn-primary" value="Send Email To Customer">
                                    </form>
                                @endif
                            </div>
                        </div>
                    </fieldset>

                    @if(formulir_view_approval($sales_quotation->formulir, 'approval.point.sales.quotation'))
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Approval Action</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-6">
                                    <form action="{{url('sales/point/indirect/sales-quotation/'.$sales_quotation->id.'/approve')}}"
                                          method="post">
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
                                    <form action="{{url('sales/point/indirect/sales-quotation/'.$sales_quotation->id.'/reject')}}"
                                          method="post">
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

                    @if($list_sales_quotation_archived->count() > 0)
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
                                            @foreach($list_sales_quotation_archived as $sales_quotation_archived)
                                                <tr>
                                                    <td class="text-center">
                                                        <a href="{{ url('sales/point/indirect/sales-quotation/'.$sales_quotation_archived->id.'/archived') }}"
                                                           data-toggle="tooltip" title="Show"
                                                           class="btn btn-effect-ripple btn-xs btn-info"><i
                                                                    class="fa fa-file"></i> {{ 'Revision ' . $count++ }}
                                                        </a>
                                                    </td>
                                                    <td>{{ date_format_view($sales_quotation->formulir->form_date) }}</td>
                                                    <td>{{ $sales_quotation_archived->formulir->archived }}</td>
                                                    <td>{{ $sales_quotation_archived->formulir->createdBy->name }}</td>
                                                    <td>{{ $sales_quotation_archived->formulir->updatedBy->name }}</td>
                                                    <td>{{ $sales_quotation_archived->formulir->edit_notes }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    @endif

                    @if($email_history->count() > 0)
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> EMAIL HISTORY</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-12 content-show">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th style="text-align: right;">NO</th>
                                                    <th style="text-align: center;">SENT AT</th>
                                                    <th>SENDER</th>
                                                    <th>RECIPIENT</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($email_history as $index=>$item)
                                                <tr>
                                                    <td style="text-align: right;">{{ $index+1 }}</td>
                                                    <td style="text-align: center;">{{ date_format_view($item->sent_at, true) }}</td>
                                                    <td><a href="/master/user/{{ $item->sender }}">{{ $item->user->name }}</a></td>
                                                    <td>{!! get_url_person($item->recipient) !!}
                                                        <span style="text-transform: none !important;"> ({{ $item->recipient_email }})</span>
                                                    </td>
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
