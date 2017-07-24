@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('bumi-shares::app/facility/bumi-shares/_breadcrumb')
        <li><a href="{{ url('facility/bumi-shares/sell') }}">Sell</a></li>
        <li>{{ $shares_sell->formulir->form_number }}</li>
    </ul>

    <h2 class="sub-header">Sell Shares</h2>
    @include('bumi-shares::app.facility.bumi-shares.sell._menu')

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
                                @include('framework::app.include._form_status_label', ['form_status' => $shares_sell->formulir->form_status])
                                @include('framework::app.include._approval_status_label', [
                                    'approval_status' => $shares_sell->formulir->approval_status,
                                    'approval_message' => $shares_sell->formulir->approval_message,
                                    'approval_at' => $shares_sell->formulir->approval_at,
                                    'approval_to' => $shares_sell->formulir->approvalTo->name,
                                ])
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
                            {{ $shares_sell->formulir->form_number ? $shares_sell->formulir->form_number : $shares_sell->formulir->archived }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Date</label>
                        <div class="col-md-6 content-show">
                            {{ \DateHelper::formatView($shares_sell->formulir->form_date, true) }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Broker</label>
                        <div class="col-md-6 content-show">
                            {{ $shares_sell->broker->name }} ({{ number_format_quantity($shares_sell->fee) }} %)
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Shares</label>
                        <div class="col-md-6 content-show">
                            {{ $shares_sell->shares->name }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Owner</label>
                        <div class="col-md-6 content-show">
                            {{ $shares_sell->owner->name }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Group</label>
                        <div class="col-md-6 content-show">
                            {{ $shares_sell->ownerGroup->name }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>
                        <div class="col-md-6 content-show">
                            {!! nl2br(e($shares_sell->formulir->notes)) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Sell Quantity</label>
                        <div class="col-md-6 content-show">
                            {{ \NumberHelper::formatPrice($shares_sell->quantity) }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Sell Price</label>
                        <div class="col-md-6 content-show">
                            {{ \NumberHelper::formatPrice($shares_sell->price) }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Total</label>
                        <div class="col-md-6 content-show">
                            {{ \NumberHelper::formatPrice($shares_sell->quantity * $shares_sell->price + ($shares_sell->quantity * $shares_sell->price * $shares_sell->fee / 100)) }}
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
                                {{ $shares_sell->formulir->createdBy->name }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Ask Approval To</label>
                            <div class="col-md-6 content-show">
                                {{ $shares_sell->formulir->approvalTo->name }}
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
                            @if(formulir_view_edit($shares_sell->formulir, 'update.bumi.shares.sell'))
                            <a href="{{url('facility/bumi-shares/sell/'.$shares_sell->id.'/edit')}}" class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                            @endif
                            @if(formulir_view_cancel($shares_sell->formulir, 'delete.bumi.shares.sell'))
                            <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger" onclick="secureCancelForm('{{url('facility/bumi-shares/sell/cancel')}}',
                                '{{ $shares_sell->formulir_id }}',
                                'delete.bumi.shares.sell')"><i class="fa fa-times"></i> Cancel Form</a>
                            @endif
                        </div>
                    </div>
                </fieldset>

                @if(formulir_view_approval($shares_sell->formulir,'approval.bumi.shares.sell'))
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Approval Action</legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-6">
                            <form action="{{url('facility/bumi-shares/sell/'.$shares_sell->id.'/approve')}}" method="post">
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
                            <form action="{{url('facility/bumi-shares/sell/'.$shares_sell->id.'/reject')}}" method="post">
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
                
                @if($list_shares_sell_archived->count() > 0)
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
                                            <th>Date</th>
                                            <th>Number</th>
                                            <th>Created By</th>
                                            <th>Updated By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $count=0;?>
                                        @foreach($list_shares_sell_archived as $shares_sell_archived)
                                        <tr>
                                            <td class="text-center">
                                                <a href="{{ url('facility/bumi-shares/sell/'.$shares_sell_archived->formulir_id.'/archived') }}" data-toggle="tooltip" title="Show" class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-file"></i> {{ 'Revision ' . $count++ }}</a>
                                            </td>
                                            <td>{{ \DateHelper::formatView($shares_sell->formulir->form_date) }}</td>
                                            <td>{{ $shares_sell_archived->formulir->archived }}</td>
                                            <td>{{ $shares_sell_archived->createdBy->name }}</td>
                                            <td>{{ $shares_sell_archived->updatedBy->name }}</td>
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
