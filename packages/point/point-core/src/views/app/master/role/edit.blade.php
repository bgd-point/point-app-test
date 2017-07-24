@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('core::app/master/_breadcrumb')
        <li><a href="{{ url('master/user') }}">User</a></li>
        <li><a href="{{ url('master/role') }}">Role</a></li>
        <li><a href="{{ url('master/role/'.$role->id) }}">{{$role->name}}</a></li>
        <li>Edit</li>
    </ul>

    <h2 class="sub-header">Role "{{ $role->name }}"</h2>
    @include('core::app.master.user._menu')
    @include('core::app.error._alert')

    <div class="panel panel-default"> 
        <div class="panel-body">
            <form action="{{url('master/role/'.$role->id)}}" method="post" class="form-horizontal form-bordered">
                {!! csrf_field() !!}
                <input name="_method" type="hidden" value="PUT">

                <div class="form-group">
                    <label class="col-md-3 control-label">Name</label>
                    <div class="col-md-6">
                        <input type="text" name="name" value="{{$role->name}}" class="form-control">
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
