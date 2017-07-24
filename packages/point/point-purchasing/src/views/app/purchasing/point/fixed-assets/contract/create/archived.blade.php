@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.fixed-assets._breadcrumb')
            <li><a href="{{ url('purchasing/point/contract') }}">Contract</a></li>
            <li>Archived</li>
        </ul>
        <h2 class="sub-header">Contract | Fixed Assets</h2>
        @include('point-purchasing::app.purchasing.point.fixed-assets.contract._menu')

        @include('core::app.error._alert')

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
                            <legend><i class="fa fa-angle-right"></i> Formulir Contract</legend>
                        </div>
                    </div>
                </fieldset>
                <div class="form-group">
                    <label class="col-md-3 control-label">Form Number</label>
                    <div class="col-md-6 content-show">
                        {{$contract->formulir->form_number}}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Asset Account *</label>
                    <div class="col-md-6 content-show">
                        {{$contract_archived->coa->name}}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Acquisition date *</label>
                    <div class="col-md-6 content-show">
                        {{date_format_view($contract_archived->formulir->form_date)}}
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label">Asset name *</label>
                    <div class="col-md-6 content-show">
                        {{$contract_archived->codeName}}
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label">Useful period *</label>
                    <div class="col-md-6 content-show">
                        {{$contract_archived->useful_life}} Month
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Useful period Rev. </label>
                    <div class="col-md-6 content-show">
                        {{$contract_archived->useful_life_revision}} Month
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Salvage Value *</label>
                    <div class="col-md-6 content-show">
                        {{number_format_quantity($contract_archived->salvage_value, 0)}}
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label">Purchase date *</label>
                    <div class="col-md-6 content-show">
                        {{date_format_view($contract_archived->date_purchased)}}
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label">Supplier</label>
                    <div class="col-md-6 content-show">
                        {{$contract_archived->coa->name}}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Quantity *</label>
                    <div class="col-md-6 content-show">
                        {{$contract_archived->supplier->codeName}}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Price *</label>
                    <div class="col-md-6 content-show">
                        {{number_format_quantity($contract_archived->price, 0)}}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Total price *</label>
                    <div class="col-md-6 content-show">
                        {{number_format_quantity($contract_archived->total_price, 0)}}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Total paid </label>
                    <div class="col-md-6 content-show">
                        {{number_format_quantity($contract_archived->total_paid, 0)}}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">depreciation *</label>
                    <div class="col-md-6 content-show">
                        {{number_format_quantity($contract_archived->depreciation)}} Month
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="col-md-3 control-label">notes</label>
                    <div class="col-md-6 content-show">
                        {{$contract_archived->formulir->notes}}
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
                                            <td>{{$contract_detail->reference->formulir->form_number}} {{$contract_detail->reference->formulir->notes}}</td>
                                            <td>{{date_format_view($contract_detail->reference->date_purchased)}}</td>
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
                            {{ $contract_archived->formulir->createdBy->name }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Request Approval To</label>

                        <div class="col-md-6 content-show">
                            {{ $contract_archived->formulir->approvalTo->name }}
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
                                'approval_status' => $contract_archived->formulir->approval_status,
                                'approval_message' => $contract_archived->formulir->approval_message,
                                'approval_at' => $contract_archived->formulir->approval_at,
                                'approval_to' => $contract_archived->formulir->approvalTo->name,
                            ])
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Status</label>

                        <div class="col-md-6 content-show">
                            @include('framework::app.include._form_status_label', ['form_status' => $contract_archived->formulir->form_status])
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
