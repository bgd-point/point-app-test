@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
         @include('point-finance::app.finance.point.debt-cash._breadcrumb')
         <li>Show</li>
    </ul>
    <h2 class="sub-header"> Cash | Payment </h2>
    @include('point-finance::app.finance.point.debt-cash._menu')

    @include('core::app.error._alert')

    <div class="block full">
        <div class="block-title">
            <ul class="nav nav-tabs" data-toggle="tabs">
                <li class="active"><a href="#block-tabs-home">Form</a></li>
                <li><a href="#block-tabs-settings"><i class="gi gi-settings"></i></a></li>
            </ul>
        </div>
        <div class="tab-content">
            <div class="tab-pane active" id="block-tabs-home">
                <div class="form-horizontal form-bordered">
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="pull-right">
                                    @include('framework::app.include._form_status_label', ['form_status' => $cash->formulir->form_status])
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
                            {{ $cash->formulir->form_number }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Date</label>
                        <div class="col-md-6 content-show">
                            {{ date_format_view($cash->formulir->form_date, false) }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Cash Account</label>
                        <div class="col-md-6 content-show">
                            {{ $cash->account->name }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Supplier</label>
                        <div class="col-md-6 content-show">
                            {!! get_url_person($cash->person->id) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>
                        <div class="col-md-6 content-show">
                            {{ $cash->formulir->notes }}
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
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Account</th>
                                                <th>Notes</th>
                                                <th class="text-right">Amount</th>
                                                <th class="text-right">Allocation</th>
                                            </tr>
                                        </thead>
                                        <tbody class="">
                                        @foreach($cash->detail as $cash_detail)
                                        <tr>
                                            <td>{{ $cash_detail->coa->account }} @if($cash_detail->reference)| {!! formulir_url($cash_detail->reference) !!}@endif</td>
                                            <td>{{ $cash_detail->notes_detail }}</td>
                                            <td class="text-right">{{ number_format_price($cash_detail->amount) }}</td>
                                            <td class="text-right">{{ $cash_detail->allocation->name }}</td>
                                        </tr>
                                        @endforeach
                                        </tbody> 
                                        <tfoot>
                                            <tr>
                                                <td colspan="2" class="text-right">TOTAL</td>
                                                <td class="text-right">{{number_format_quantity($cash->total * -1)}}</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table> 
                                </div>
                            </div>                                           
                        </div>
                        @if($cash->cashCashAdvance)
                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                        <tr>
                                            <th>Cash Advance Usage</th>
                                            <th>Notes</th>
                                            <th class="text-right">Amount</th>
                                            <th>Close</th>
                                        </tr>
                                        </thead>
                                        <tbody class="">
                                        <?php $cash_advance_total = 0;?>
                                        @foreach($cash->cashCashAdvance as $cash_advance)
                                            <?php $cash_advance_total+= $cash_advance->cash_advance_amount; ?>
                                            <tr>
                                                <td><a href="{{url($cash_advance->cash::showUrl($cash_advance->cash->id))}}">{{ $cash_advance->cash->formulir->form_number }}</a></td>
                                                <td>{{ $cash_advance->cash->formulir->notes }}</td>
                                                <td class="text-right">{{ number_format_price($cash_advance->cash_advance_amount) }}</td>
                                                <td>{{ $cash_advance->close == true ? 'close' : '' }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                        <tfoot>
                                        <tr>
                                            <td colspan="2" class="text-right">TOTAL</td>
                                            <td class="text-right">{{number_format_quantity($cash_advance_total)}}</td>
                                            <td></td>
                                        </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endif
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
                                {{ $cash->formulir->createdBy->name }} ({{ date_format_view($cash->formulir->created_at) }})
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
                            @if(formulir_view_cancel($cash->formulir, 'delete.point.finance.cashier.cash'))
                            <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                               onclick="secureCancelForm('{{url('finance/point/payment/cancel')}}',
                                    '{{ $cash->formulir_id }}',
                                    'delete.point.finance.cashier.cash')"><i class="fa fa-times"></i> Cancel Form</a>
                            @else
                            <?php
                                $users = \Point\Core\Models\User::where('disabled', 0)->get();
                            ?>
                            <form action="{{ url('finance/point/payment/request-cancel') }}" method="post">
                                {{ csrf_field() }}
                                <input type="hidden" name="formulir_id" value="{{ $cash->formulir->id }}">
                                <div class="col-md-2 text-right" style="line-height: 36px;">
                                    Approval cancel to
                                </div>
                                <div class="col-md-3">
                                    <select name="approver" class="form-control">
                                        @foreach($users as $user)
                                            @if ($user->may('delete.point.finance.cashier.cash'))
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-7">
                                    <button type="submit" class="btn btn-effect-ripple btn-danger">
                                        <i class="fa fa-times"></i> Request Cancel Form
                                    </button>
                                </div>
                            </form>
                            @endif
                        </div>
                    </div>
                </fieldset>

                @if($list_cash_archived->count() > 0)
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
                                        @foreach($list_cash_archived as $cash_archived)
                                        <tr>
                                            <td class="text-center">
                                                <a href="{{ url('finance/point/debt-cash/'.$cash_archived->id.'/archived') }}" data-toggle="tooltip" title="Show" class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-file"></i> {{ 'Revision ' . $count++ }}</a>
                                            </td>
                                            <td>{{ date_format_view($cash->formulir->form_date) }}</td>
                                            <td>{{ $cash_archived->formulir->archived }}</td>
                                            <td>{{ $cash_archived->formulir->createdBy->name }}</td>
                                            <td>{{ $cash_archived->formulir->updatedBy->name }}</td>
                                            <td>{{ $cash_archived->formulir->edit_notes }}</td>
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
    </div>    
</div>
@stop