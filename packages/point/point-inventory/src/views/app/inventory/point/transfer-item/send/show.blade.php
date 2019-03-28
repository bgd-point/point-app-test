@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-inventory::app/inventory/point/transfer-item/_breadcrumb')
        <li>{{ $transfer_item->formulir->form_number }}</li>
    </ul>
    <h2 class="sub-header">Transfer Item</h2>
    @include('point-inventory::app.inventory.point.transfer-item._menu')
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
                                    'approval_status' => $transfer_item->formulir->approval_status,
                                    'approval_message' => $transfer_item->formulir->approval_message,
                                    'approval_at' => $transfer_item->formulir->approval_at,
                                    'approval_to' => $transfer_item->formulir->approvalTo->name,
                                ])
                                @include('framework::app.include._form_status_label', ['form_status' => $transfer_item->formulir->form_status])
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
                            {{ $transfer_item->formulir->form_number }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Date</label>
                        <div class="col-md-6 content-show">
                            {{date_format_view($transfer_item->formulir->form_date)}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">From Warehouse</label>
                        <div class="col-md-6 content-show">
                            {{$transfer_item->warehouseFrom->codeName}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">To Warehouse</label>
                        <div class="col-md-6 content-show">
                            {{$transfer_item->warehouseTo->codeName}}
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
                                                <th style="min-width: 250px;">ITEM</th>
                                                <th class="text-right">QUANTITY SEND</th>
                                                <th class="text-right">QUANTITY RECEIVED</th>
                                            </tr>
                                        </thead>
                                        <tbody class="manipulate-row">
                                        @foreach($transfer_item->items as $transfer_item_detail)
                                        
                                        <tr>
                                            <td>{{ $transfer_item_detail->item->codeName }}</td>
                                            <td class="text-right">{{ number_format_quantity($transfer_item_detail->qty_send) }} {{$transfer_item_detail->unit}}</td>
                                            <td class="text-right">{{$transfer_item->received_date ? number_format_quantity($transfer_item_detail->qty_received) .' '. $transfer_item_detail->unit : '-'}}</td>
                                        </tr>
                                        @endforeach
                                        </tbody> 
                                       
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
                                {{ $transfer_item->formulir->createdBy->name }}
                            </div>
                        </div>                  
                        <div class="form-group">
                            <label class="col-md-3 control-label">Request Approval To</label>
                            <div class="col-md-6 content-show">
                                {{ $transfer_item->formulir->approvalTo->name }}
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
                            @if(formulir_view_edit($transfer_item->formulir, 'update.point.inventory.transfer.item'))
                            <a href="{{url('inventory/point/transfer-item/send/'.$transfer_item->id.'/edit')}}" class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                            @endif

                            @if(formulir_view_cancel($transfer_item->formulir, 'delete.point.inventory.transfer.item'))
                            <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                               onclick="secureCancelForm('{{url('formulir/cancel')}}',
                                    '{{ $transfer_item->formulir_id }}',
                                    'delete.point.inventory.transfer.item')"><i class="fa fa-times"></i> Cancel Form</a>
                            @endif
                            <a class="btn btn-effect-ripple btn-info" href="{{url('inventory/point/transfer-item/send/'.$transfer_item->id.'/print')}}">Print</a>
                        </div>
                    </div>
                </fieldset>

                @if(formulir_view_approval($transfer_item->formulir, 'approval.point.inventory.transfer.item'))
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Approval Action</legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-6">
                            <form action="{{url('inventory/point/transfer-item/send/'.$transfer_item->id.'/approve')}}" method="post">
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
                            <form action="{{url('inventory/point/transfer-item/send/'.$transfer_item->id.'/reject')}}" method="post">
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
                
                @if($transfer_item_archived->count() > 0)
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
                                        @foreach($transfer_item_archived as $list_transfer_item_archived)
                                        <tr>
                                            <td class="text-center">
                                                <a href="{{ url('inventory/point/transfer-item/send/'.$list_transfer_item_archived->id.'/archived') }}" data-toggle="tooltip" title="Show" class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-file"></i> {{ 'Revision ' . $count++ }}</a>
                                            </td>
                                            <td>{{ date_format_view($list_transfer_item_archived->formulir->form_date) }}</td>
                                            <td>{{ $transfer_item->formulir->form_number }}</td>
                                            <td>{{ $list_transfer_item_archived->formulir->createdBy->name }}</td>
                                            <td>{{ $list_transfer_item_archived->formulir->updatedBy->name }}</td>
                                            <td>{{ $list_transfer_item_archived->formulir->edit_notes }}</td>
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
