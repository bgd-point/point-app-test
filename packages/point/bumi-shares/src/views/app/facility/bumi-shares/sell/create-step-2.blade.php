@extends('core::app.layout')

@section('content')

<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('bumi-shares::app/facility/bumi-shares/_breadcrumb')
        <li><a href="{{ url('facility/bumi-shares/sell') }}">Sell</a></li>
        <li>Create</li>
    </ul>

    <h2 class="sub-header">Sell Shares</h2>
    @include('bumi-shares::app.facility.bumi-shares.sell._menu')

    @include('core::app.error._alert')
    <div class="panel panel-default"> 
        <div class="panel-body">
            <form action="{{url('facility/bumi-shares/sell')}}" method="post" class="form-horizontal form-bordered">
                {!! csrf_field() !!}

                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Form</legend>
                        </div>
                    </div> 
                </fieldset>
                <div class="form-group">
                    <label class="col-md-3 control-label">Form Date</label>
                    <div class="col-md-6 content-show">
                        <input type="hidden" name="form_date" value="{{ $form_date }}">
                        <input type="hidden" name="time" value="{{ $time }}">
                        {{ $form_date }} {{$time}}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Broker</label>
                    <div class="col-md-6 content-show">
                        <input type="hidden" name="broker_id" value="{{$broker->id}}">
                        <input type="hidden" id="sales_fee" name="sales_fee" value="{{$broker->sales_fee}}">
                        {{ $broker->name }} ({{ number_format_quantity($broker->sales_fee) }} %)
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Shares</label>
                    <div class="col-md-6 content-show">
                        <input type="hidden" name="shares_id" value="{{$shares->id}}">
                        {{ $shares->name }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Owner</label>
                    <div class="col-md-6 content-show">
                        <input type="hidden" name="owner_id" value="{{$owner->id}}">
                        {{ $owner->name }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Group</label>
                    <div class="col-md-6 content-show">
                        <input type="hidden" name="owner_group_id" value="{{$owner_group->id}}">
                        {{ $owner_group->name }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Notes</label>
                    <div class="col-md-6 content-show">
                        <input type="hidden" name="notes" value="{{$notes}}">
                        {!! nl2br(e($notes)) !!}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Available Stock</label>
                    <div class="col-md-6 content-show">
                        <input type="hidden" name="available_stock" value="{{ number_format_quantity($available_stock) }}">
                        {{ number_format_quantity($available_stock) }} Sheet
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Selling Quantity *</label>
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" id="quantity" name="quantity" class="form-control format-quantity" value="{{  $quantity }}" onkeyup="calculate()" />
                            <span class="input-group-addon">SHEET</span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Selling Price *</label>
                    <div class="col-md-6">
                        <input type="text" id="price" name="price" class="form-control format-quantity" value="{{  $price }}" onkeyup="calculate()" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Total </label>
                    <div class="col-md-6">
                        <input type="text" readonly id="total" name="total" class="form-control format-quantity" value="{{ $total }}" />
                    </div>
                </div>

                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Authorized User</legend>
                        </div>
                    </div>  
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Creator </label>
                        <div class="col-md-6 content-show">
                            {{ auth()->user()->name }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Approval To *</label>
                        <div class="col-md-6">
                            <select id="approval-to" name="approval_to" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                <option></option>
                                @foreach($list_user_approval as $user_approval)
                                
                                @if($user_approval->may('approval.bumi.shares.buy'))
                                <option value="{{$user_approval->id}}" @if(old('approval_to') == $user_approval->id) selected @endif>{{$user_approval->name}}</option>
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
    var sales_fee = dbNum($('#sales_fee').val());
    var total = quantity * price;
    var total_komisi = total * sales_fee / 100;
    var total_selling = total - total_komisi;
    $('#total').val(accountingNum(total_selling));
}
</script>
@stop
