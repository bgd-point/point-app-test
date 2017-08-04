@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-accounting::app/accounting/point/memo-journal/_breadcrumb')
        <li><a href="{{url('accounting/point/memo-journal/'.$memo_journal->id)}}">{{$memo_journal->formulir->form_number}}</a></li>
        <li>Archived</li>
    </ul>
    <h2 class="sub-header">Memo Journal</h2>
    @include('point-accounting::app.accounting.point.memo-journal._menu')

    @include('core::app.error._alert')

    <div class="block full">
        <div class="tab-content">
            <div class="tab-pane active" id="block-tabs-home">
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
                                <legend><i class="fa fa-angle-right"></i> Form</legend>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Archived Number</label>
                            <div class="col-md-6 content-show">
                                {{ $memo_journal_archived->formulir->archived }}
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label">Notes</label>
                            <div class="col-md-6 content-show">
                                {{ $memo_journal_archived->formulir->notes }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Date</label>
                            <div class="col-md-6 content-show">
                                {{ date_format_view($memo_journal_archived->formulir->form_date) }}
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
                                        @foreach($memo_journal_archived->detail as $detail)
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
        </div>
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
