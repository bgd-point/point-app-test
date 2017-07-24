@extends('core::app.layout')

@section('content')
<div id="page-content" class="inner-sidebar-left">

     @include('core::app.settings._sidebar')

     @include('core::app.error._alert')
 
    <div class="panel panel-default"> 
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="sub-header">Change Password</h2>
                    <form action="{{url('settings/password')}}" method="post" class="form-horizontal form-bordered">
                        {!! csrf_field() !!}

                        <div class="form-group">
                            <label class="col-md-3 control-label">Old Password</label>
                            <div class="col-md-6">
                                <input required type="password" name="old_password" class="form-control" autofocus>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">New Password</label>
                            <div class="col-md-6">
                                <input required type="password" name="new_password" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Retype Password</label>
                            <div class="col-md-6">
                                <input required type="password" name="retype_password" class="form-control">
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
    </div>  
</div>
@stop