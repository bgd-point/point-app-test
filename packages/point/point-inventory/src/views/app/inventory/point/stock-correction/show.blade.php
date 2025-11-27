@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-inventory::app/inventory/point/stock-correction/_breadcrumb')
        <li>{{ $stock_correction->formulir->form_number }}</li>
    </ul>
    <h2 class="sub-header">Stock Correction</h2>
    @include('point-inventory::app.inventory.point.stock-correction._menu')
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
                                    'approval_status' => $stock_correction->formulir->approval_status,
                                    'approval_message' => $stock_correction->formulir->approval_message,
                                    'approval_at' => $stock_correction->formulir->approval_at,
                                    'approval_to' => $stock_correction->formulir->approvalTo->name,
                                ])
                                @include('framework::app.include._form_status_label', ['form_status' => $stock_correction->formulir->form_status])
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
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Number</label>
                        <div class="col-md-6 content-show">
                            {{ $stock_correction->formulir->form_number }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Date</label>
                        <div class="col-md-6 content-show">
                            {{ date_format_view($stock_correction->formulir->form_date, false) }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Warehouse</label>
                        <div class="col-md-6 content-show">
                            {{ $stock_correction->warehouse->codeName }}
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
                                                <th>STOCK BEFORE CORRECTION</th>
                                                <th>QUANTITY CORRECTION</th>
                                                <th>NOTES</th>
                                            </tr>
                                        </thead>
                                        <tbody class="manipulate-row">
                                        @foreach($stock_correction->items as $stock_correction_item)
                                        <tr>
                                            <td>{{ $stock_correction_item->item->codeName }}</td>
                                            <td>{{ number_format_quantity($stock_correction_item->stock_in_database) }} {{$stock_correction_item->unit}}</td>
                                            <td>{{ number_format_quantity($stock_correction_item->quantity_correction) }} {{$stock_correction_item->unit}}</td>
                                            <td>{{ $stock_correction_item->correction_notes }}</td>
                                        </tr>
                                        @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
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
                                {{ $stock_correction->formulir->createdBy->name }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Request Approval To</label>
                            <div class="col-md-6 content-show">
                                {{ $stock_correction->formulir->approvalTo->name }}
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
                            @if(formulir_view_edit($stock_correction->formulir, 'update.point.inventory.stock.correction'))
                            <!-- <a href="{{url('inventory/point/stock-correction/'.$stock_correction->id.'/edit')}}" class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a> -->
                            @endif
                            @if(formulir_view_cancel($stock_correction->formulir, 'delete.point.inventory.stock.correction'))
                            <!-- <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                               onclick="secureCancelForm('{{url('formulir/cancel')}}',
                                    '{{ $stock_correction->formulir_id }}',
                                    'delete.point.inventory.stock.correction')"><i class="fa fa-times"></i> Cancel Form</a> -->
                            @endif
                        </div>
                    </div>
                </fieldset>
                @if(formulir_view_approval($stock_correction->formulir, 'approval.point.inventory.stock.correction'))
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Approval Action</legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-6">
                            <form action="{{url('inventory/point/stock-correction/'.$stock_correction->id.'/approve')}}" method="post">
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
                            <form action="{{url('inventory/point/stock-correction/'.$stock_correction->id.'/reject')}}" method="post">
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

                @if($list_stock_correction_archived->count() > 0)
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
                                        <?php $count=0;?>
                                        @foreach($list_stock_correction_archived as $stock_correction_archived)
                                        <tr>
                                            <td class="text-center">
                                                <a href="{{ url('inventory/point/stock-correction/'.$stock_correction_archived->id.'/archived') }}" data-toggle="tooltip" title="Show" class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-file"></i> {{ 'Revision ' . $count++ }}</a>
                                            </td>
                                            <td>{{ date_format_view($stock_correction_archived->formulir->form_date) }}</td>
                                            <td>{{ $stock_correction->formulir->form_number }}</td>
                                            <td>{{ $stock_correction_archived->formulir->createdBy->name }}</td>
                                            <td>{{ $stock_correction_archived->formulir->updatedBy->name }}</td>
                                            <td>{{ $stock_correction_archived->formulir->edit_notes }}</td>
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
