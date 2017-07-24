@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('bumi-deposit::app/facility/bumi-deposit/_breadcrumb')
        <li>Deposit Report</li>
    </ul>

    <h2 class="sub-header">Deposit Report</h2>
    <div class="text-right">
        @include('bumi-deposit::app.facility.bumi-deposit.deposit-report._menu')
    </div>

    <div class="panel panel-default">
        <div class="panel-body">
            <form action="{{ url('facility/bumi-deposit/deposit-report') }}" method="get" class="form-horizontal">
                <div class="form-group">
                    <div class="col-sm-6">
                        <div class="input-group">
                            <span class="input-group-addon">Group</span>
                            <select class="selectize" style="width: 100%;" data-placeholder="Choose one.." name="deposit_group_id">
                                <option value="0">All</option>
                                @foreach( $groups as $group )
                                    <option value="{{ $group->id }}" @if(\Input::get('deposit_group_id') == $group->id) selected @endif> {{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="input-group">
                            <span class="input-group-addon">Owner</span>
                            <select class="selectize" style="width: 100%;" data-placeholder="Choose one.." name="deposit_owner_id">
                                @foreach( $owners as $owner )
                                    <option value="0">All</option>
                                    <option value="{{ $owner->id }}" @if(\Input::get('deposit_owner_id') == $owner->id) selected @endif> {{ $owner->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="input-group">
                            <span class="input-group-addon">Bank</span>
                            <select class="selectize" style="width: 100%;" data-placeholder="Choose one.." name="deposit_bank_id">
                                @foreach( $banks as $bank )
                                    <option value="0">All</option>
                                    <option value="{{ $bank->id }}" @if(\Input::get('deposit_bank_id') == $bank->id) selected @endif> {{ $bank->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="input-group">
                            <span class="input-group-addon">Status</span>
                            <select class="selectize" style="width: 100%;" data-placeholder="Choose one.." name="status">
                                <option value="0" @if(\Input::get('status') == 0) selected @endif>All</option>
                                <option value="ongoing" @if(\Input::get('status') == null || \Input::get('status') == 'ongoing') selected @endif>Ongoing</option>
                                <option value="closed" @if(\Input::get('status') == 'closed') selected @endif>Closed</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search</button>
                    </div>
                </div>
            </form>

            <br/>

            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th>Form Number</th>
                        <th>Owner</th>
                        <th>Form Date - Due Date</th>
                        <th>Bank</th>
                        <th>Bank Account</th>
                        <th>No Bilyet</th>
                        <th>Bank Interest</th>
                        <th class="text-right">Deposit Value</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($selected_group as $group)
                        @if($group->deposits(\Input::get('deposit_owner_id'), \Input::get('deposit_bank_id'), \Input::get('status'))->count())
                        <tr>
                            <td colspan="8" class="themed-background-dark text-light-op">{{ $group->name }}</td>
                        </tr>
                        <?php $total_deposit = 0; ?>
                        @foreach($group->deposits(\Input::get('deposit_owner_id'), \Input::get('deposit_bank_id'), \Input::get('status'))->get() as $deposit)
                            <?php $total_deposit += $deposit->original_amount; ?>
                            <tr>
                                <td><a href="{{ url('facility/bumi-deposit/deposit/'.$deposit->id) }}">{{ $deposit->formulir->form_number }}</a></td>
                                <td>{{ $deposit->owner->name }}</td>
                                <td>{{ date_format_view($deposit->formulir->form_date) }} - {{ date_format_view($deposit->due_date) }}</td>
                                <td>{{ $deposit->bank->name }}</td>
                                <td>{{ $deposit->bankAccount->account_number }} a/n {{ $deposit->bankAccount->account_name }}</td>
                                <td>{{ $deposit->deposit_number }}</td>
                                <td>{{ $deposit->interest_percent }} %</td>
                                <td class="text-right">{{ number_format_quantity($deposit->original_amount) }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="7"></td>
                            <td class="text-right"><b>{{ number_format_quantity($total_deposit) }}</b></td>
                        </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop
