@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.fixed-assets._breadcrumb')
            <li><a href="{{ url('purchasing/point/fixed-assets/contract') }}">Contract</a></li>
            <li>Show</li>
        </ul>
        <h2 class="sub-header">Contract | Fixed Assets</h2>
        @include('point-purchasing::app.purchasing.point.fixed-assets.contract._menu')

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
                                        'approval_status' => $contract->formulir->approval_status,
                                        'approval_message' => $contract->formulir->approval_message,
                                        'approval_at' => $contract->formulir->approval_at,
                                        'approval_to' => $contract->formulir->approvalTo->name,
                                    ])
                                    @include('framework::app.include._form_status_label', ['form_status' => $contract->formulir->form_status])
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
                                {{$contract->formulir->form_number}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Asset Account *</label>
                            <div class="col-md-6 content-show">
                                {{$contract->coa->name}}
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-md-3 control-label">Acquisition date *</label>
                            <div class="col-md-6 content-show">
                                {{date_format_view($contract->formulir->form_date)}}
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label">Asset name *</label>
                            <div class="col-md-6 content-show">
                                {{$contract->codeName}}
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label">Useful life *</label>
                            <div class="col-md-6 content-show">
                                {{number_format_quantity($contract->useful_life, 0)}} Month
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Salvage Value *</label>
                            <div class="col-md-6 content-show">
                                {{number_format_quantity($contract->salvage_value, 0)}}
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label">Purchase date *</label>
                            <div class="col-md-6 content-show">
                                {{date_format_view($contract->date_purchased)}}
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label">Supplier</label>
                            <div class="col-md-6 content-show">
                                {{$contract->supplier->codeName}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Quantity *</label>
                            <div class="col-md-6 content-show">
                                {{$contract->quantity}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Price *</label>
                            <div class="col-md-6 content-show">
                                {{number_format_quantity($contract->price, 0)}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Total price *</label>
                            <div class="col-md-6 content-show">
                                {{number_format_quantity($contract->total_price, 0)}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Total paid </label>
                            <div class="col-md-6 content-show">
                                {{number_format_quantity($contract->total_paid, 0)}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">depreciation *</label>
                            <div class="col-md-6 content-show">
                                {{number_format_quantity($contract->depreciation)}} Month
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-md-3 control-label">notes</label>
                            <div class="col-md-6 content-show">
                                {{$contract->formulir->notes}}
                            </div>
                        </div>
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Details</legend>
                                </div>
                            </div>
                        </fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table id="service-datatable" class="table table-striped">
                                        <thead>
                                        <tr>
                                            <th style="min-width:220px">Description</th>
                                            <th style="min-width:220px">Date</th>
                                        </tr>
                                        </thead>
                                        <tbody class="manipulate-row">
                                        @foreach($contract->details as $contract_detail)
                                        <tr>
                                            <td>{{$contract_detail->reference->formulir->form_number}} {{$contract_detail->reference->formulir->notes}} #{{number_format_quantity($contract_detail->reference->total_price)}}</td>
                                            <td>{{date_format_view($contract_detail->reference->date_purchased)}}</td>
                                        </tr>
                                        @endforeach
                                        </tbody>
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
                                    {{ $contract->formulir->createdBy->name }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Request Approval To</label>
                                <div class="col-md-6 content-show">
                                    {{ $contract->formulir->approvalTo->name }}
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
                                @if(formulir_view_edit($contract->formulir, 'update.point.purchasing.contract'))
                                    <a href="{{url('purchasing/point/fixed-assets/contract/'.$contract->id.'/edit')}}"
                                       class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                                @endif
                                @if(formulir_view_cancel($contract->formulir, 'delete.point.purchasing.contract'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureCancelForm('{{url('formulir/cancel')}}',
                                               '{{ $contract->formulir_id }}',
                                               'delete.point.purchasing.contract')"><i class="fa fa-times"></i> Cancel
                                        Form</a>
                                @endif
                                @if(formulir_view_close($contract->formulir, 'update.point.purchasing.contract'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureCloseForm({{$contract->formulir_id}},'{{url('formulir/close')}}')">Close
                                        Form</a>
                                @endif
                                @if(formulir_view_reopen($contract->formulir, 'update.point.purchasing.contract'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger" onclick="secureReopenForm({{$contract->formulir_id}},'{{url('formulir/reopen')}}')">Reopen Form</a>
                                @endif
                            </div>
                        </div>
                    </fieldset>


                    @if(formulir_view_approval($contract->formulir, 'approval.point.purchasing.contract'))
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Approval Action</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-6">
                                    <form action="{{url('purchasing/point/fixed-assets/contract/'.$contract->id.'/approve')}}"
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
                                    <form action="{{url('purchasing/point/fixed-assets/contract/'.$contract->id.'/reject')}}"
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

                    @if($list_contract_archived->count() > 0)
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
                                            @foreach($list_contract_archived as $contract_archived)
                                                <tr>
                                                    <td class="text-center">
                                                        <a href="{{ url('purchasing/point/fixed-assets/contract/'.$contract_archived->id.'/archived') }}"
                                                           data-toggle="tooltip"
                                                           title="Show"
                                                           class="btn btn-effect-ripple btn-xs btn-info">
                                                            <i class="fa fa-file"></i> {{ 'Revision ' . $count++ }}
                                                        </a>
                                                    </td>
                                                    <td>{{ date_format_view($contract->formulir->form_date) }}</td>
                                                    <td>{{ $contract_archived->formulir->archived }}</td>
                                                    <td>{{ $contract_archived->formulir->createdBy->name }}</td>
                                                    <td>{{ $contract_archived->formulir->updatedBy->name }}</td>
                                                    <td>{{ $contract_archived->formulir->edit_notes }}</td>
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
