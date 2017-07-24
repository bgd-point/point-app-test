@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('core::app/master/_breadcrumb')
        <li><a href="{{ url('master/warehouse') }}">Warehouse</a></li>
        <li>Create</li>
    </ul>

    <h2 class="sub-header">Warehouse</h2>

    @include('framework::app.master.warehouse._menu')
    @include('core::app.error._alert')

    <div class="panel panel-default"> 
        <div class="panel-body">
            <form action="{{url('master/warehouse')}}" method="post" class="form-horizontal form-bordered">
                {!! csrf_field() !!}

                <div class="form-group">
                    <label class="col-md-3 control-label">Cash Account *</label>
                    <div class="col-md-6">
                        <div class="@if(access_is_allowed_to_view('create.coa')) input-group @endif">
                            <select id="petty_cash_account" name="petty_cash_account" class="selectize">
                                <option value="">Choose your cash account</option>
                                @foreach($list_petty_cash_account as $petty_cash_account)
                                    <option value="{{ $petty_cash_account->id }}" @if(old('petty_cash_account')==$petty_cash_account->id) selected @endif>{{ $petty_cash_account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Code *</label>
                    <div class="col-md-6">
                        <input type="text" name="code" class="form-control" value="{{ $code }}" readonly>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Name *</label>
                    <div class="col-md-6">
                        <input type="text" name="name" class="form-control" value="{{old('name')}}" autofocus>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Store Name </label>
                    <div class="col-md-6">
                        <input type="text" name="store_name" class="form-control" value="{{old('store_name')}}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Address </label>
                    <div class="col-md-6">
                        <input type="text" name="address" class="form-control" value="{{old('address')}}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Phone </label>
                    <div class="col-md-6">
                        <input type="text" name="phone" class="form-control" value="{{old('phone')}}">
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

@stop
