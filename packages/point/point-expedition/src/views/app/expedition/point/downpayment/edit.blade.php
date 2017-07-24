@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-expedition::app/expedition/point/downpayment/_breadcrumb')
            <li>Edit</li>
        </ul>
        <h2 class="sub-header">DOWNPAYMENT</h2>
        @include('point-expedition::app.expedition.point.downpayment._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('expedition/point/downpayment/'.$downpayment->id)}}" method="post"
                      class="form-horizontal form-bordered">
                    {!! csrf_field() !!}
                    <input name="_method" type="hidden" value="PUT">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Reason edit *</label>
                        <div class="col-md-6">
                            <input type="text" name="edit_notes" class="form-control" value="{{$downpayment->formulir->approval_message}}" autofocus>
                        </div>
                    </div>
                    @if($expedition_order)
                    <input type="hidden" name="expedition_order_id" value="{{$expedition_order->id}}">
                    <input type="hidden" name="expedition_id" value="{{$expedition_order->expedition_id}}">
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend>
                                    <i class="fa fa-angle-right"></i> 
                                    REF# <a target="_blank" href="{{url('expedition/point/expedition-order/'.$expedition_order->id)}}">{{$expedition_order->formulir->form_number}} </a>
                                </legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Date</label>
                        <div class="col-md-6 content-show">
                            {{ date_format_view($expedition_order->formulir->form_date)}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Total amount</label>
                        <div class="col-md-6 content-show">
                            {{ number_format_price($expedition_order->total) }}
                        </div>
                    </div>
                    @endif
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Formulir Downpayment</legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Number</label>
                        <div class="col-md-6 content-show">
                            {{$downpayment->formulir->form_number}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Date</label>
                        <div class="col-md-3">
                            <input type="text" name="form_date" class="form-control date input-datepicker"
                                   data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}"
                                   value="{{ date(date_format_get(), strtotime($downpayment->formulir->form_date)) }}">
                        </div>
                        <div class="col-md-3">
                            <div class="input-group bootstrap-timepicker">
                                <input type="text" id="time" name="time" class="form-control timepicker" value="{{date('H:i', strtotime($downpayment->formulir->form_date))}}">
                                <span class="input-group-btn">
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-primary">
                                        <i class="fa fa-clock-o"></i>
                                    </a>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Expedition</label>
                        <div class="col-md-6 content-show">
                            @if($expedition_order != "")
                            {{$expedition_order->expedition->codeName}}
                            @else
                            <select class="selectize" name="expedition_id" style="width:100%">
                                @foreach($list_expedition as $expedition)
                                <option value="{{ $expedition->id }}" @if(old('expedition_id') == $expedition->id) selected @endif>{{ $expedition->codeName }}</option>
                                @endforeach
                            </select>    
                            @endif
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Payment Type *</label>
                        <div class="col-md-6">
                            <select id="payment_type" name="payment_type" class="selectize" style="width: 100%;" data-placeholder="Please choose">
                                <option value="cash">Cash</option>
                                <option value="bank">Bank</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Amount *</label>
                        <div class="col-md-6">
                            <input id="quantity" type="text" name="amount" class="form-control format-quantity" value="{{$downpayment->amount}}"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>
                        <div class="col-md-6">
                            <input type="text" name="notes" class="form-control" value="{{$downpayment->formulir->notes}}">
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
                                <select name="approval_to" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                    <option value="{{ $downpayment->formulir->approval_to }}">{{ $downpayment->formulir->approvalTo->name }}</option>
                                    @foreach($list_user_approval as $user_approval)
                                        @if($user_approval->may('approval.point.expedition.downpayment'))
                                            <option value="{{$user_approval->id}}" @if($downpayment->approval_to == $user_approval->id) selected @endif>{{$user_approval->name}}</option>
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
