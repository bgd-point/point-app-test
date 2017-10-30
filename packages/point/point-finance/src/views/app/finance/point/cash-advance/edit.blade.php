@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-finance::app.finance.point.cash-advance._breadcrumb')
            <li><a href="{{ url('finance/point/cash-advance') }}">Cash Advance</a></li>
            <li>Edit</li>
        </ul>
        <h2 class="sub-header">Cash Advance</h2>
        @include('point-finance::app.finance.point.cash-advance._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('finance/point/cash-advance/'.$cash_advance->id)}}" method="post"
                        class="form-horizontal form-bordered">
                    {!! csrf_field() !!}
                    <input name="_method" type="hidden" value="PUT">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Reason edit *</label>
                        <div class="col-md-6">
                            <input type="text" name="edit_notes" class="form-control" value="{{$cash_advance->formulir->approval_message}}" autofocus>
                        </div>
                    </div>
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Cash Advance Form</legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Number</label>

                        <div class="col-md-6 content-show">
                            {{$cash_advance->formulir->form_number}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Date</label>

                        <div class="col-md-3">
                            <input type="text" name="form_date" class="form-control date input-datepicker"
                                    data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}"
                                    value="{{ date(date_format_get(), strtotime($cash_advance->formulir->form_date)) }}">
                        </div>
                        <div class="col-md-3">
                            <div class="input-group bootstrap-timepicker">
                                <input type="text" id="time" name="time" class="form-control timepicker"
                                        value="{{date('H:i', strtotime($cash_advance->formulir->form_date))}}">
                                <span class="input-group-btn">
                                <a href="javascript:void(0)" class="btn btn-effect-ripple btn-primary"><i
                                            class="fa fa-clock-o"></i></a>
                            </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Employee *</label>
                        <div class="col-md-6">
                            <select id="employee-id" name="employee_id" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                @foreach($list_employee as $employee)
                                    <option @if($employee->id == $cash_advance->employee_id) selected @endif value="{{ $employee->id }}">{{ $employee->codeName }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Amount *</label>
                        <div class="col-md-6">
                            <input id="quantity" type="text" name="amount" class="form-control format-quantity" value="{{$cash_advance->amount}}"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>
                        <div class="col-md-6">
                            <input type="text" name="notes" class="form-control" value="{{$cash_advance->formulir->notes}}">
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
                                {{auth()->user()->name}}
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label">Request Approval To</label>
                            <div class="col-md-6">
                                <select name="approval_to" class="selectize" style="width: 100%;"
                                        data-placeholder="Choose one..">
                                    @foreach($list_user_approval as $user_approval)
                                        @if($user_approval->may('approval.point.finance.cash.advance'))
                                            <option @if($cash_advance->formulir->approval_to == $user_approval->id) selected @endif value="{{$user_approval->id}}">{{$user_approval->name}} </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </fieldset>

                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-3">
                            <button type="submit" class="btn btn-effect-ripple btn-primary">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script>
        initDatatable('#item-datatable');
    </script>
@stop
