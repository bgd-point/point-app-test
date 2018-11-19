@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-accounting::app/accounting/point/memo-journal/_breadcrumb')
        <li><a href="{{url('accounting/point/memo-journal')}}">Memo Journal</a></li>
        <li>Show</li>
    </ul>
    <h2 class="sub-header">Memo Journal | Show</h2>
    @include('point-accounting::app.accounting.point.memo-journal._menu')

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
                                    'approval_status' => $memo_journal->formulir->approval_status,
                                    'approval_message' => $memo_journal->formulir->approval_message,
                                    'approval_at' => $memo_journal->formulir->approval_at,
                                    'approval_to' => $memo_journal->formulir->approvalTo->name,
                                ])
                                @include('framework::app.include._form_status_label', ['form_status' => $memo_journal->formulir->form_status])
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

                    <fieldset>
                    @if($revision)
                    <div class="form-group">
                        <label class="col-md-3 control-label">Revision</label>
                        <div class="col-md-6 content-show">
                            {{ $revision }}
                        </div>
                    </div>
                    @endif

                    <div class="form-group">
                        <label class="col-md-3 control-label">Number</label>
                        <div class="col-md-6 content-show">
                               {{ $memo_journal->formulir->form_number }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>
                        <div class="col-md-6 content-show">
                               {{ $memo_journal->formulir->notes }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Date</label>
                        <div class="col-md-6 content-show">
                            {{ date_format_view($memo_journal->formulir->form_date) }}
                        </div>
                    </div>
                    </fieldset>

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Details</legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="table-responsive"> 
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>COA</th>
                                                <th>Master Ref</th>
                                                <th>Form Ref</th>
                                                <th>Description</th>
                                                <th>Debit</th>
                                                <th>Credit</th>
                                            </tr>
                                        </thead>
                                        <tbody class="manipulate-row">
                                        @foreach($memo_journal->detail as $detail)
                                        <tr>
                                            <td>{{ $detail->coa->name }}</td>
                                            <td>{{ $detail->subledger_type ? $detail->subledger_type::find($detail->subledger_id)->name : '-'}}</td>
                                            <td>{{ $detail->form_reference_id ? $detail->reference->form_number : '-'}}</td>
                                            <td>{{ $detail->description }}</td>
                                            <td>{{ number_format_accounting($detail->debit) }}</td>
                                            <td>{{ number_format_accounting($detail->credit) }}</td>
                                        </tr>
                                        @endforeach
                                        </tbody> 
                                        <tfoot>
                                            <tr>                                            
                                                <td colspan="4"><strong>Balance</strong></td>
                                                <td>{{ number_format_accounting($memo_journal->debit) }}</td>
                                                <td>{{ number_format_accounting($memo_journal->credit) }}</td>
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
                                <legend><i class="fa fa-angle-right"></i> Person In Charge</legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Creator</label>
                            <div class="col-md-6 content-show">
                                {{ $memo_journal->formulir->createdBy->name }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Approval To</label>
                            <div class="col-md-6 content-show">
                                {{ $memo_journal->formulir->approvalTo->name }}
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
            <div class="tab-pane" id="block-tabs-settings">
                @if($memo_journal->form_status == 0)
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Action</legend>                    
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            @if(formulir_view_edit($memo_journal->formulir, 'update.point.accounting.memo.journal'))
                            <a href="{{url('accounting/point/memo-journal/'.$memo_journal->id.'/edit')}}" class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                            @endif
                            @if(formulir_view_cancel($memo_journal->formulir, 'delete.point.accounting.memo.journal'))
                            <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger" 
                               onclick="secureCancelForm('{{url('formulir/cancel')}}', {{$memo_journal->formulir_id}},
                               'delete.point.accounting.memo.journal')"><i class="fa fa-times"></i> cancel</a>
                            @endif
                        </div>
                    </div>
                </fieldset>
                @endif

                @if(formulir_view_approval($memo_journal->formulir, 'approval.point.accounting.memo.journal'))
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Approval Actions</legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-6">
                                <form action="{{url('accounting/point/memo-journal/'.$memo_journal->id.'/approve')}}" method="post">
                                    {!! csrf_field() !!}
                                    <input type="text" name="approval_message" class="form-control" placeholder="Message">
                                    <hr/>
                                    <input type="submit" class="btn btn-primary" value="Approve">
                                </form>
                            </div>
                            <div class="col-md-6">
                                <form action="{{url('accounting/point/memo-journal/'.$memo_journal->id.'/reject')}}" method="post">
                                    {!! csrf_field() !!}
                                    <input type="text" name="approval_message" class="form-control" placeholder="Message">
                                    <hr/>
                                    <input type="submit" class="btn btn-danger" value="Reject">
                                </form>
                            </div>
                        </div>
                    </fieldset>
                @endif

                @if($list_memo_journal_archived->count() > 0)
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
                                        @foreach($list_memo_journal_archived as $memo_journal_archived)
                                        <tr>
                                            <td class="text-center">
                                                <a href="{{ url('accounting/point/memo-journal/'.$memo_journal_archived->id.'/archived') }}" data-toggle="tooltip" title="Show" class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-file"></i> {{ 'Revision ' . $count++ }}</a>
                                            </td>
                                            <td>{{ date_format_view($memo_journal->formulir->form_date) }}</td>
                                            <td>{{ $memo_journal_archived->formulir->archived }}</td>
                                            <td>{{ $memo_journal_archived->formulir->createdBy->name }}</td>
                                            <td>{{ $memo_journal_archived->formulir->updatedBy->name }}</td>
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
