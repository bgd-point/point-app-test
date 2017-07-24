@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('core::app/master/_breadcrumb')
        <li><a href="{{ url('master/fixed-assets-item') }}">Item</a></li>
        <li>Create</li>
    </ul>

    <h2 class="sub-header">Fixed Assets Item</h2>
    @include('framework::app.master.fixed-assets._menu')

    @include('core::app.error._alert')

    <div class="panel panel-default"> 
        <div class="panel-body">
            <form action="{{url('master/fixed-assets-item/')}}" method="post" class="form-horizontal form-bordered">
                {!! csrf_field() !!}

                <div class="form-group">
                    <label class="col-md-3 control-label">Fixed Assets Account *</label>
                    <div class="col-md-6">
                        <div class="@if(access_is_allowed_to_view('create.coa')) input-group @endif">
                            <select id="account_fixed_asset_id" name="account_fixed_asset_id" onchange="selectAccountFixedAsset(this.value)" class="selectize">
                                <option value="">Choose your asset account</option>
                                @foreach($list_account_fixed_assets as $account_fixed_assets)
                                    <option value="{{ $account_fixed_assets->id }}" @if(old('account_fixed_asset_id')==$account_fixed_assets->id) selected @endif>{{ $account_fixed_assets->name }}</option>
                                @endforeach
                            </select>
                            @if(access_is_allowed_to_view('create.coa'))
                            <span class="input-group-btn">
                                <a href="#modal-coa" onclick="resetForm()" class="btn btn-effect-ripple btn-primary" data-toggle="modal">
                                    <i class="fa fa-plus"></i>
                                </a>
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Code *</label>
                    <div class="col-md-6">
                        <input readonly="" type="text" id="code" name="code" class="form-control" value="{{ $code }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Name *</label>
                    <div class="col-md-6">
                        <input type="text" name="name" class="form-control" value="{{old('name')}}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Useful Life *</label>
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" name="useful_life" id="useful-life" class="form-control format-quantity text-right" value="{{old('useful_life')}}">
                            <span class="input-group-addon">year</span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Salvage Value *</label>
                    <div class="col-md-6">
                        <input type="text" name="salvage_value" class="form-control format-quantity text-right" value="{{old('salvage_value')}}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Unit *</label>
                    <div class="col-md-6">
                        <input type="text" name="unit" class="form-control" value="{{old('unit')}}">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-6 col-md-offset-3">
                        <button type="submit" class="btn btn-effect-ripple btn-primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>  
</div>

@include('framework::app.master.coa.__create')
@stop

@section('scripts')
<script>
    function selectAccountFixedAsset(account_id) {
        $.ajax({
            url: "{{url('master/fixed-assets-item/get-useful-life')}}",
            data: {account_id : account_id},
            success: function(data) {
                $('#useful-life').val(data);
            },
        })
    }
</script>
@stop
