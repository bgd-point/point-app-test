@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('core::app/master/_breadcrumb')
        <li><a href="{{ url('master/user') }}">User</a></li>
        <li><a href="{{ url('master/user/'.$user->id) }}">{{ $user->name }}</a></li>
        <li>Edit</li>
    </ul>

    <h2 class="sub-header">User "{{ $user->name }}"</h2>
    @include('core::app.master.user._menu')
    @include('core::app.error._alert')

    <div class="panel panel-default"> 
        <div class="panel-body">
            <form action="{{url('master/user/'.$user->id)}}" method="post" class="form-horizontal form-bordered" enctype="multipart/form-data">
                {!! csrf_field() !!}
                <input name="_method" type="hidden" value="PUT">

                <div class="form-group">
                    <label class="col-md-3 control-label">Name</label>
                    <div class="col-md-6">
                        <input type="text" name="name" class="form-control" value="{{$user->name}}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Email</label>
                    <div class="col-md-6">
                        <input type="email" name="email" class="form-control" value="{{$user->email}}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label">Photo</label>
                    <div class="col-md-6">
                        <input type="file" name="photo">
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
