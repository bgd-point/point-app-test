@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('core::app/master/_breadcrumb')
        <li><a href="{{ url('master/bank') }}">Bank</a></li>
        <li>Create</li>
    </ul>

    <h2 class="sub-header">Bank</h2>

    @include('framework::app.master.bank._menu')
    @include('core::app.error._alert')

    <div class="panel panel-default"> 
        <div class="panel-body">
            <form action="{{url('master/bank')}}" method="post" class="form-horizontal form-bordered">
                {!! csrf_field() !!}
                <div class="form-group">
                    <label class="col-md-3 control-label">Name *</label>
                    <div class="col-md-6">
                        <input type="text" name="name" class="form-control" value="{{old('name')}}" autofocus>
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
