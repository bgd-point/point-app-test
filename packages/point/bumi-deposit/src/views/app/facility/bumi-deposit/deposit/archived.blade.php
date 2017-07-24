@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('bumi-deposit::app/facility/bumi-deposit/_breadcrumb')
        <li><a href="{{ url('facility/bumi-deposit/deposit') }}">Deposit</a></li>
        <li><a href="{{ url('facility/bumi-deposit/deposit/'.$deposit->id) }}">{{ $deposit->formulir->form_number }}</a></li>
        <li>Archived</li>
    </ul>

    <h2 class="sub-header">Deposit</h2>
    @include('bumi-deposit::app.facility.bumi-deposit.deposit._menu')
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
                        <legend><i class="fa fa-angle-right"></i> Form {{$deposit_archived->formulir->form_number}}</legend>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Form Date</label>
                    <div class="col-md-9 content-show">
                        {{date_format_view($deposit_archived->formulir->form_date)}}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Bank</label>
                    <div class="col-md-9 content-show">
                        {{ $deposit_archived->bank->name }} ({{ $deposit_archived->bank->branch }}) <br>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Bank Account</label>
                    <div class="col-md-9 content-show">
                        {{ $deposit_archived->bankAccount->account_number }} a/n {{ $deposit_archived->bankAccount->account_name }} <br>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Bank Product</label>
                    <div class="col-md-9 content-show">
                        {{ $deposit_archived->bankProduct->product_name }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Category</label>
                    <div class="col-md-9 content-show">
                        {{ $deposit_archived->category->name }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Group</label>
                    <div class="col-md-9 content-show">
                        {{ $deposit_archived->group->name }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Owner</label>
                    <div class="col-md-9 content-show">
                        {{ $deposit_archived->owner->name }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Bilyet Number</label>
                    <div class="col-md-9 content-show">
                        {{ $deposit_archived->deposit_number }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Time Period</label>
                    <div class="col-md-9 content-show">
                        {{number_format_quantity($deposit_archived->deposit_time, 2)}} {{($deposit_archived->deposit_time < 2)?'day':'days'}}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Due Date</label>
                    <div class="col-md-9 content-show">
                        {{date_format_view($deposit_archived->due_date)}}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Deposit Value</label>
                    <div class="col-md-9 content-show">
                        {{ number_format_quantity($deposit_archived->original_amount) }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Bank Interest</label>
                    <div class="col-md-9 content-show">
                        {{number_format_quantity($deposit_archived->interest_percent)}} %
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Total Days in a Year</label>
                    <div class="col-md-9 content-show">
                        {{number_format_quantity($deposit_archived->total_days_in_year, 0)}} days
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Tax Fee</label>
                    <div class="col-md-9 content-show">
                        {{number_format_quantity($deposit_archived->tax_percent)}}%
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Bank Interest + Tax Fee</label>
                    <div class="col-md-9 content-show">
                        {{number_format_quantity($deposit_archived->total_interest)}}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Total Amount</label>
                    <div class="col-md-9 content-show">
                        {{ number_format_quantity($deposit_archived->total_amount) }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Notes</label>
                    <div class="col-md-9 content-show">
                        {{$deposit_archived->formulir->notes}}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Important Notes</label>
                    <div class="col-md-9 content-show">
                        {{ $deposit_archived->important_notes }}
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
                    <div class="col-md-9 content-show">
                        {{ $deposit_archived->formulir->createdBy->name }}
                    </div>
                </div>
            </fieldset>

            @if(formulir_is_close($deposit_archived->formulir_id))
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Withdrawal</legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Date</label>
                        <div class="col-md-9 content-show">
                            {{ date_format_view($deposit_archived->withdraw_date) }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Amount</label>
                        <div class="col-md-9 content-show">
                            {{ number_format_quantity($deposit_archived->withdraw_amount) }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>
                        <div class="col-md-9 content-show">
                            {{ $deposit_archived->withdraw_notes }}
                        </div>
                    </div>
                </fieldset>
            @endif
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
