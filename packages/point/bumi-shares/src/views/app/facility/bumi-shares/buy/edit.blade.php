@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('bumi-shares::app/facility/bumi-shares/_breadcrumb')
        <li><a href="{{ url('facility/bumi-shares/buy') }}">Buy</a></li>
        <li><a href="{{ url('facility/bumi-shares/buy/'.$shares_buy->id) }}">{{ $shares_buy->formulir->form_number }}</a></li>
        <li>Edit</li>
    </ul>

    <h2 class="sub-header">Buy Shares</h2>
    @include('bumi-shares::app.facility.bumi-shares.buy._menu')
    @include('core::app.error._alert')

    <div class="panel panel-default"> 
        <div class="panel-body">
            <form action="{{url('facility/bumi-shares/buy/'.$shares_buy->id)}}" method="post" class="form-horizontal form-bordered">
                {!! csrf_field() !!}
                <input name="_method" type="hidden" value="PUT">

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
                        <input type="text" name="form_date" class="form-control date input-datepicker" data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}" value="{{ date(date_format_get(), strtotime($shares_buy->formulir->form_date)) }}">
                    </div>
                    <div class="col-md-3">
                        <div class="input-group bootstrap-timepicker">
                            <input type="text" id="time" name="time" class="form-control timepicker" value="{{ date('H:i', strtotime($shares_buy->formulir->form_date)) }}">
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
                            <option value="{{$broker->id}}" @if($shares_buy->broker_id == $broker->id) selected @endif>{{$broker->name}}</option>
                            @endforeach
                        </select>
                        <input type="hidden" id="buy_fee" name="buy_fee" value="{{$shares_buy->fee}}">
                        <div class="input-group">
                            <span class="input-group-addon">Fee</span>
                            <input type="text" id="buy_fee" class="form-control format-percent" name="buy_fee" value="{{ $shares_buy->fee }}" onkeyup="calculate()">
                            <span class="input-group-addon">%</span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Shares *</label>
                    <div class="col-md-6">
                        <select id="shares-id" name="shares_id" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                            <option></option>
                            @foreach($list_shares as $shares)
                            <option value="{{$shares->id}}" @if($shares_buy->shares_id == $shares->id) selected @endif>{{$shares->name}}</option>
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
                            <option value="{{$owner->id}}" @if($shares_buy->owner_id == $owner->id) selected @endif>{{$owner->name}}</option>
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
                            <option value="{{$owner_group->id}}" @if($shares_buy->owner_group_id == $owner_group->id) selected @endif>{{$owner_group->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Notes </label>
                    <div class="col-md-6">
                        <textarea name="notes" class="form-control autosize">{{ $shares_buy->notes }}</textarea>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Quantity *</label>
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" id="quantity" name="quantity" class="form-control format-quantity" value="{{$shares_buy->quantity}}" onkeyup="calculate()" />
                            <span class="input-group-addon">sheet</span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Price *</label>
                    <div class="col-md-6">
                        <input type="text" id="price" name="price" class="form-control format-quantity" value="{{ $shares_buy->price }}" onkeyup="calculate()" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Total</label>
                    <div class="col-md-6">
                        <input type="text" readonly id="total" name="total" class="form-control format-quantity" value="{{ $shares_buy->price * $shares_buy->quantity + ($shares_buy->quantity * $shares_buy->price * $shares_buy->fee / 100) }}" />
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
                            {{ $shares_buy->formulir->createdBy->name }}
                        </div>
                    </div>                  
                    <div class="form-group">
                        <label class="col-md-3 control-label">Approval To *</label>
                        <div class="col-md-6">
                            <select id="approval-to" name="approval_to" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                <option value="{{$shares_buy->approval_to}}">{{ $shares_buy->formulir->approvalTo->name }}</option>
                                @foreach($list_user_approval as $user_approval)
                                
                                @if($user_approval->may('approval.bumi.shares.buy'))

                                @if($shares_buy->approval_to != $user_approval->id)
                                <option value="{{$user_approval->id}}">{{$user_approval->name}}</option>
                                @endif

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
function calculate() {
    var quantity = dbNum($('#quantity').val());
    var price = dbNum($('#price').val());
    var buy_fee = dbNum($('#buy_fee').val());
    var total = quantity * price;
    var total_fee = total * buy_fee / 100;
    $('#total').val(accountingNum(total + total_fee));
}
</script>
@stop
