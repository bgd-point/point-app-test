@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-finance::app.finance.point.cheque._breadcrumb')
            <li><a href="{{ url('finance/point/cheque') }}">Cheque</a></li>
            <li>Cheque Liquid</li>
        </ul>
        <h2 class="sub-header">Cheque</h2>
        @include('point-finance::app.finance.point.cheque._menu')
        <div class="panel panel-default">

            <div class="panel-body">
                <form action="{{url('finance/point/cheque/reject')}}" method="post" class="form-horizontal form-bordered">
                    {!! csrf_field() !!}
                    <input type="hidden" name="id" value="{{\Input::get('id')}}">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Reject Date *</label>
                        <div class="col-md-3">
                            <input type="text" name="rejected_at" class="form-control date input-datepicker"
                                   data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}"
                                   value="{{date(date_format_get(), strtotime(\Carbon::now()))}}">
                        </div>
                        <div class="col-md-3">
                            <div class="input-group bootstrap-timepicker">
                                <input type="text" id="time" name="time" class="form-control timepicker">
                                <span class="input-group-btn">
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-primary"><i class="fa fa-clock-o"></i></a>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Bank Account</label>
                        <div class="col-md-6">
                            <select class="selectize" name="coa_id" placeholder="choose one ...">
                                @foreach($list_coa as $coa)
                                <option value="{{$coa->id}}">{{$coa->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Reason reject *</label>
                        <div class="col-md-6">
                            <input type="text" name="reject_notes" class="form-control" value="" required autofocus>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-3">
                            <button type="submit" class="btn btn-effect-ripple btn-primary">Submit</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="item-datatable" class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th>Reference</th>
                                        <th>Bank</th>
                                        <th>Due Date</th>
                                        <th>Number</th>
                                        <th>Notes</th>
                                        <th class="text-right">Amount</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($list_cheque_detail as $cheque_detail)
                                        <tr>
                                            <td>{{ $cheque_detail->cheque->formulir->form_number }}</td>
                                            <td>
                                                {{ $cheque_detail->bank}}
                                            </td>
                                            <td>
                                                {{ date_format_view($cheque_detail->due_date)}}
                                            </td>
                                            <td>
                                                {{ $cheque_detail->number}}
                                            </td>
                                            <td>
                                                {{ $cheque_detail->notes}}
                                            </td>
                                            <td class="text-right">
                                                {{ number_format_price($cheque_detail->amount)}}
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                </form>
            </div>
        </div>
    </div>
@stop