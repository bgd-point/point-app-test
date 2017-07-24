@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
         @include('point-finance::app.finance.point.bank._breadcrumb')
         <li>Show</li>
    </ul>
    <h2 class="sub-header"> Bank | Payment </h2>
    @include('point-finance::app.finance.point.bank._menu')

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
                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="pull-right">
                                    @include('framework::app.include._form_status_label', ['form_status' => $bank->formulir->form_status])
                                </div>
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
                            {{ $bank->formulir->form_number }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Date</label>
                        <div class="col-md-6 content-show">
                            {{ date_format_view($bank->formulir->form_date, false) }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Bank Account</label>
                        <div class="col-md-6 content-show">
                            {{ $bank->account->name }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Supplier</label>
                        <div class="col-md-6 content-show">
                            {!! get_url_person($bank->person->id) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>
                        <div class="col-md-6 content-show">
                            {{ $bank->formulir->notes }}
                        </div>
                    </div>
                    
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Detail</legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="table-responsive"> 
                                    <table id="item-datatable" class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Account</th>
                                                <th>Notes</th>
                                                <th class="text-right">Amount</th>
                                                <th class="text-right">Allocation</th>
                                            </tr>
                                        </thead>
                                        <tbody class="manipulate-row">
                                        @foreach($bank->detail as $bank_detail)
                                        <tr>
                                            <td>{{ $bank_detail->coa->account }}</td>
                                            <td>{{ $bank_detail->notes_detail }}</td>
                                            <td class="text-right">{{ number_format_price($bank_detail->amount) }}</td>
                                            <td class="text-right">{{ $bank_detail->allocation->name }}</td>
                                        </tr>
                                        @endforeach
                                        </tbody> 
                                        <tfoot>
                                            <tr>
                                                <td colspan="2" class="text-right"><h4><b>TOTAL</b></h4></td>
                                                <td class="text-right">{{number_format_quantity($bank->total)}}</td>
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
                                {{ $bank->formulir->createdBy->name }}
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
                            @if(formulir_view_cancel($bank->formulir, 'delete.point.finance.cashier.bank'))
                            <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                               onclick="secureCancelForm('{{url('finance/point/payment/cancel')}}',
                                    '{{ $bank->formulir_id }}',
                                    'delete.point.finance.cashier.bank')"><i class="fa fa-times"></i> Cancel Form</a>
                            @endif
                        </div>
                    </div>
                </fieldset>

                @if($list_bank_archived->count() > 0)
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
                                        @foreach($list_bank_archived as $bank_archived)
                                        <tr>
                                            <td class="text-center">
                                                <a href="{{ url('finance/point/bank/'.$bank_archived->id.'/archived') }}" data-toggle="tooltip" title="Show" class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-file"></i> {{ 'Revision ' . $count++ }}</a>
                                            </td>
                                            <td>{{ date_format_view($bank->formulir->form_date) }}</td>
                                            <td>{{ $bank_archived->formulir->archived }}</td>
                                            <td>{{ $bank_archived->formulir->createdBy->name }}</td>
                                            <td>{{ $bank_archived->formulir->updatedBy->name }}</td>
                                            <td>{{ $bank_archived->formulir->edit_notes }}</td>
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
