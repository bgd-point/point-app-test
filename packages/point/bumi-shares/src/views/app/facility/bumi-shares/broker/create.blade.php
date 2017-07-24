@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('bumi-shares::app/facility/bumi-shares/_breadcrumb')
        <li><a href="{{ url('facility/bumi-shares/broker') }}">Broker</a></li>
        <li>Create</li>
    </ul>

    <h2 class="sub-header">Broker</h2>
    @include('bumi-shares::app.facility.bumi-shares.broker._menu')

    @include('core::app.error._alert')

    <div class="panel panel-default"> 
        <div class="panel-body">
            <form action="{{url('facility/bumi-shares/broker')}}" method="post" class="form-horizontal form-bordered">
                {!! csrf_field() !!}
                <div class="form-group">
                    <label class="col-md-3 control-label">Name *</label>
                    <div class="col-md-6">
                        <input type="text" name="name" class="form-control" value="{{old('name')}}" autofocus>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Notes</label>
                    <div class="col-md-6">
                        <input type="text" name="notes" class="form-control" value="{{old('notes')}}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Buy Fee *</label>
                    <div class="col-md-3">
                        <div class="input-group">
                            <input type="text" name="buy_fee" class="form-control format-percent" value="{{old('buy_fee')}}">
                            <span class="input-group-addon">%</span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Sales Fee *</label>
                    <div class="col-md-3">
                        <div class="input-group">
                            <input type="text" name="sales_fee" class="form-control format-percent" value="{{old('sales_fee')}}">
                            <span class="input-group-addon">%</span>
                        </div>
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
