@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('bumi-shares::app/facility/bumi-shares/_breadcrumb')
        <li><a href="{{ url('facility/bumi-shares/buy') }}">Buy</a></li>
        <li>Create</li>
    </ul>

    <h2 class="sub-header">Buy Shares</h2>
    @include('bumi-shares::app.facility.bumi-shares.buy._menu')

    @include('core::app.error._alert')

    <div class="panel panel-default"> 
        <div class="panel-body">
            <form action="{{url('facility/bumi-shares/buy/create-step-2')}}" method="post" class="form-horizontal form-bordered">
                {!! csrf_field() !!}

                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Form</legend>
                        </div>
                    </div> 
                </fieldset>
                <div class="form-group">
                    <label class="col-md-3 control-label">Form Date *</label>
                    <div class="col-md-3">
                        <input type="text" name="form_date" class="form-control date input-datepicker" data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}" value="{{ date(date_format_get(), strtotime(old('form_date') ? date_format_db(old('form_date')) :date('Y-m-d'))) }}">
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
                    <label class="col-md-3 control-label">Broker *</label>
                    <div class="col-md-6">
                        <select id="broker-id" name="broker_id" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                            <option></option>
                            @foreach($list_broker as $broker)
                            <option value="{{$broker->id}}" @if(old('broker_id') == $broker->id) selected @endif>{{$broker->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Shares *</label>
                    <div class="col-md-6">
                        <select id="shares-id" name="shares_id" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                            <option></option>
                            @foreach($list_shares as $shares)
                            <option value="{{$shares->id}}" @if(old('shares_id') == $shares->id) selected @endif>{{$shares->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Owner *</label>
                    <div class="col-md-6">
                        <select id="owner-id" name="owner_id" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                            <option></option>
                            @foreach($list_owner as $owner)
                            <option value="{{$owner->id}}" @if(old('owner_id') == $owner->id) selected @endif>{{$owner->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Group *</label>
                    <div class="col-md-6">
                        <select id="kelompok-owner-id" name="owner_group_id" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                            <option></option>
                            @foreach($list_owner_group as $owner_group)
                            <option value="{{$owner_group->id}}" @if(old('owner_group') == $owner_group->id) selected @endif>{{$owner_group->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Notes</label>
                    <div class="col-md-6">
                        <textarea name="notes" class="form-control autosize">
                            {!! nl2br(e(old('notes'))) !!}
                        </textarea>
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
                            {{\Auth::user()->name}}
                        </div>
                    </div>
                </fieldset>

                <div class="form-group">
                    <div class="col-md-6 col-md-offset-3">
                        <button type="submit" class="btn btn-effect-ripple btn-primary">Next</button>
                    </div>
                </div>
            </form>
        </div>
    </div>  
</div>
@stop
