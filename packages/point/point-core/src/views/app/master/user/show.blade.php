@extends('core::app.layout')
 
@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('core::app/master/_breadcrumb')
        <li><a href="{{ url('master/user') }}">User</a></li>
        <li>Show</li>
    </ul>

    <h2 class="sub-header">User "{{ $user->name }}"</h2>
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
                        <div class="col-md-6 content-show">{{$user->name}}</div>
                    </div> 
                    <div class="form-group">
                        <label class="col-md-3 control-label">Email</label>
                        <div class="col-md-6 content-show">{{$user->email}}</div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Role</label>
                        <div class="col-md-6 content-show">
                            @foreach($user->allRoles($user->id) as $roleUser)
                                <a href="{{url('master/role/'.$roleUser->role_id)}}" class="label label-primary">{{\Point\Core\Models\Master\Role::find($roleUser->role_id)->name}}</a> &nbsp;
                            @endforeach
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
                <a href="{{url('master/user/'.$user->id.'/edit')}}" class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                <a href="{{url('master/user/'.$user->id.'/role')}}" class="btn btn-effect-ripple btn-info"><i class="fa fa-gears"></i> Set Role</a>
                <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger" onclick="secureDelete({{$user->id}}, '{{url('master/user/delete')}}', '/master/user')"><i class="fa fa-times"></i> Delete</a>
            </div>
        </div>
        <!-- END Tabs Content -->
    </div> 
</div>
@stop
