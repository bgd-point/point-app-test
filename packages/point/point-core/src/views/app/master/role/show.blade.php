@extends('core::app.layout')
 
@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('core::app/master/_breadcrumb')
        <li><a href="{{ url('master/user') }}">User</a></li>
        <li><a href="{{ url('master/role') }}">Role</a></li>
        <li>{{$role->name}}</li>
    </ul>

    <h2 class="sub-header">Role "{{ $role->name }}"</h2>
    @include('core::app.master.user._menu')

    <div class="block full">
        <!-- Block Tabs Title -->
        <div class="block-title">
            <ul class="nav nav-tabs" data-toggle="tabs">
                <li class="active"><a href="#block-tabs-home">Form</a></li>
                <li><a href="#block-tabs-profile">History</a></li>
                <li><a href="#block-tabs-settings"><i class="gi gi-settings"></i></a></li>
            </ul>
        </div>
        <!-- END Block Tabs Title -->

        <!-- Tabs Content -->
        <div class="tab-content">
            <div class="tab-pane active" id="block-tabs-home">
                <div class="form-horizontal form-bordered">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Name</label>
                        <div class="col-md-6 content-show">{{$role->name}}</div>
                    </div> 
                    <div class="form-group">
                        <label class="col-md-3 control-label">User Access</label>
                        <div class="col-md-6 content-show">
                            <ol>
                                @foreach($users as $role_user)
                                    <li><a href="{{url('master/user/'.$role_user->user_id)}}">{{ $role_user->user->name }}</a></li>
                                @endforeach
                            </ol>
                        </div>
                    </div> 
                </div>
            </div>
              
            <div class="tab-pane" id="block-tabs-profile">
                <div class="table-responsive"> 
                    <table id="list-table" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>User</th>
                                <th>Key</th>
                                <th>Old Value</th>  
                                <th>New Value</th>  
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($histories as $history)
                            <tr id="{{$history->id}}"> 
                                <td>{{ date_format_view($history->created_at, true) }}</td>
                                <td>{{ $history->user->name }}</td>
                                <td>{{ $history->key }}</td>
                                <td>{{ $history->old_value }}</td>
                                <td>{{ $history->new_value }}</td>
                            </tr>
                            @endforeach
                        </tbody> 
                    </table>
                </div>   
            </div>
            <div class="tab-pane" id="block-tabs-settings">
                <a href="{{url('master/role/'.$role->id.'/edit')}}" class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                <a href="{{url('master/role/'.$role->id.'/permission/1')}}" class="btn btn-effect-ripple btn-info"><i class="fa fa-gears"></i> Set Permission</a>
                <a href="{{url('master/role/'.$role->id.'/user-access')}}" class="btn btn-effect-ripple btn-info"><i class="fa fa-gears"></i> Set User Access</a>
                <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger" onclick="secureDelete({{$role->id}}, '{{url('master/role/delete')}}', '/master/role')"><i class="fa fa-times"></i> Delete</a>
            </div>
        </div>
        <!-- END Tabs Content -->
    </div> 
</div>
@stop
