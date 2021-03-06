@extends('core::app.layout')
@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('bumi-deposit::app/facility/bumi-deposit/_breadcrumb')
        <li>Deposit</li>
    </ul>

    <h2 class="sub-header">Deposit</h2>
    @include('bumi-deposit::app.facility.bumi-deposit.deposit._menu')

    <div class="panel panel-default">
        <div class="panel-body">
            <form action="{{ url('facility/bumi-deposit/deposit') }}" method="get" class="form-horizontal">
                <div class="form-group">
                    <div class="col-sm-15">
                        <div class="pull-right">
                            <label class="label" style="background: white;color:black">Ongoing</label>
                            <label class="label" style="background: yellow;color: black;">Due date</label>
                            <label class="label" style="background: red;">Important Notes</label>
                            <label class="label" style="background: grey;">Done</label>
                            <br><br>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-6">
                        <div class="input-group input-daterange" data-date-format="{{date_format_masking()}}">
                            <input type="text" name="form_date_from" class="form-control date input-datepicker" placeholder="Form Date From"  value="{{\Input::get('form_date_from') ? \Input::get('form_date_from') : ''}}">
                            <span class="input-group-addon"><i class="fa fa-chevron-right"></i></span>
                            <input type="text" name="form_date_to" class="form-control date input-datepicker" placeholder="Form Date To" value="{{\Input::get('form_date_to') ? \Input::get('form_date_to') : ''}}">
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="input-group input-daterange" data-date-format="{{date_format_masking()}}">
                            <input type="text" name="due_date_from" class="form-control date input-datepicker" placeholder="Due Date From"  value="{{\Input::get('due_date_from') ? \Input::get('due_date_from') : ''}}">
                            <span class="input-group-addon"><i class="fa fa-chevron-right"></i></span>
                            <input type="text" name="due_date_to" class="form-control date input-datepicker" placeholder="Due Date To" value="{{\Input::get('due_date_to') ? \Input::get('due_date_to') : ''}}">
                        </div>
                    </div>

                    <br>
                    <br>

                    <div class="col-sm-3">
                        <select class="selectize" style="width: 100%;" data-placeholder="Choose one.." name="select_field">
                            <option @if(app('request')->get('select_field') || ! app('request')->get('select_field')) selected @endif value="all">All</option>
                            <option @if(app('request')->get('select_field') == 'form_number') selected @endif value="form_number">Form Number</option>
                            <option @if(app('request')->get('select_field') == 'group') selected @endif value="group">Group</option>
                            <option @if(app('request')->get('select_field') == 'bank') selected @endif value="bank">Bank</option>
                            <option @if(app('request')->get('select_field') == 'bilyet') selected @endif value="bilyet">No Bilyet</option>
                            <option @if(app('request')->get('select_field') == 'category') selected @endif value="category">Category</option>
                            <option @if(app('request')->get('select_field') == 'notes') selected @endif value="notes">Notes</option>
                            <option @if(app('request')->get('select_field') == 'deposit') selected @endif value="deposit">Deposit Value</option>
                            <option @if(app('request')->get('select_field') == 'withdrawal') selected @endif value="withdrawal">Withdrawal</option>
                        </select>
                    </div>

                    <div class="col-sm-3">
                        <input type="text" name="search" class="form-control" placeholder="Search..." value="{{\Input::get('search') ? \Input::get('search') : ''}}">
                    </div>

                    <div class="col-sm-3">
                        <button type="submit" class="btn btn-block btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search</button>
                    </div>
                </div>
            </form>

            <br/>

            <div class="table-responsive">
                {!! $deposits->render() !!}
                <table id="deposit-datatable" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Form Number</th>
                            <th>Form Date</th>
                            <th>Due Date</th>
                            <th>Group</th>
                            <th>Bank</th>
                            <th>No Bilyet</th>
                            <th>Category</th>
                            <th>Notes</th>
                            <th>Bank Interest</th>
                            <th>Bank Interest + Tax Fee</th>
                            <th>Deposit Value</th>
                            <th>Withdrawal</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach( $deposits as $deposit )
                    <tr
                        @if(!empty($deposit->important_notes))style="background-color: red;color:white"@endif
                        @if(formulir_is_close($deposit->formulir_id))style="background-color: dimgrey;color:white"
                        @elseif(date('Y-m-d H:i:s') > $deposit->due_date)style="background-color: yellow;"@endif
                    >
                        <td>
                            <a href="{{url('facility/bumi-deposit/deposit/'.$deposit->id)}}"
                               @if(!empty($deposit->important_notes))style="background-color: red;color:white"@endif
                               @if(formulir_is_close($deposit->formulir_id))style="background-color: dimgrey;color:white"
                               @elseif(date('Y-m-d H:i:s', strtotime("+7 day")) > $deposit->due_date)style="background-color: yellow;"@endif
                            >{{ $deposit->formulir->form_number }}</a>
                        </td>
                        <td>{{ date_format_view($deposit->formulir->form_date) }}</td>
                        <td>{{ date_format_view($deposit->due_date) }}</td>
                        <td>{{ $deposit->group->name }}</td>
                        <td>{{ $deposit->bank->name }}</td>
                        <td>{{ $deposit->deposit_number }}</td>
                        <td>{{ $deposit->category->name }}</td>
                        <td>{!! nl2br(e($deposit->formulir->notes)) !!}</td>
                        <td>{{ number_format_quantity($deposit->interest_percent) }} % @if($deposit->total_days_in_year > 0)({{ number_format_quantity(($deposit->deposit_time * $deposit->original_amount * $deposit->interest_percent) / ($deposit->total_days_in_year * 100)) }})@endif</td>
                        <td>{{ number_format_quantity($deposit->total_interest) }}</td>
                        <td>{{ number_format_quantity($deposit->original_amount) }}</td>
                        <td>
                            @if(formulir_is_close($deposit->formulir_id))
                            {{ number_format_quantity($deposit->withdraw_amount) }} at {{ date_format_view($deposit->withdraw_date) }}
                            @endif
                        </td>
                    </tr>

                    @if($deposit->important_notes)
                        <tr>
                            <td colspan="12" style="background-color: red;color:white"> IMPORTANT NOTES : {!! nl2br(e($deposit->important_notes)) !!}</td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                        </tr>
                    @endif
                    @endforeach
                    </tbody>
                </table>
                {!! $deposits->appends(['date_from'=> app('request')->get('date_from'), 'date_to'=> app('request')->get('date_to'),'select_field' => app('request')->get('select_field'), 'search'=> app('request')->get('search')]) !!}
            </div>
        </div>
    </div>  
</div>
@stop

@section('scripts')
    <script>
        initDatatable('#deposit-datatable');
    </script>
@stop
