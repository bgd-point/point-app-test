@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('core::app/master/_breadcrumb')
            <li><a href="{{ url('master/user') }}">User</a></li>
            <li>Role</li>
        </ul>

        <h2 class="sub-header">Role</h2>
        @include('core::app.master.user._menu')
        @include('core::app.error._alert')

        <div class="col-md-3">
            <div class="panel panel-default">
                <div class="panel-body">
                    @if(auth()->user()->may('create.role'))
                        <form action="{{url('master/role')}}" method="post" class="form-horizontal form-bordered">
                            {!! csrf_field() !!}
                            <fieldset>
                                <legend>Create Role</legend>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Name*</label>
                                    <div class="col-md-12">
                                        <input type="text" name="name" class="form-control" value="{{old('name')}}"
                                               autofocus>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-effect-ripple btn-primary">Submit</button>
                                    </div>
                                </div>
                            </fieldset>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="panel panel-default">
                <div class="panel-body">
                    <form action="{{ url('master/role') }}" method="get" class="form-horizontal">
                        <div class="form-group">
                            <div class="col-sm-3">
                                <input type="text" name="search" class="form-control" placeholder="Search Name..."
                                       value="{{\Input::get('search')}}" autofocus>
                            </div>
                            <div class="col-sm-9">
                                <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i
                                            class="fa fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                    </form>

                    <br/>

                    <div class="table-responsive">
                        {!! $roles->appends(['search'=>app('request')->get('search')])->render() !!}
                        <table class="table table-striped table-bordered">
                            <thead>
                            <tr>
                                <th width="100px" class="text-center"></th>
                                <th>NAME</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($roles as $role)
                                <tr id="list-{{$role->id}}">
                                    <td class="text-center">
                                        <a href="{{ url('master/role/'.$role->id) }}" data-toggle="tooltip" title="Show"
                                           class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-file"></i></a>
                                        <a href="javascript:void(0)" data-toggle="tooltip" title="Delete"
                                           class="btn btn-effect-ripple btn-xs btn-danger"
                                           onclick="secureDelete({{$role->id}}, '{{url('master/role/delete')}}')"><i
                                                    class="fa fa-times"></i></a>
                                    </td>
                                    <td><a href="{{ url('master/role/'.$role->id) }}">{{ $role->name }}</a></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {!! $roles->appends(['search'=>app('request')->get('search')])->render() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
