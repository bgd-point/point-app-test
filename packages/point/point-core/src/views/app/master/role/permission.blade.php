@extends('core::app.layout')

@section('scripts') 
<script>
    function updatePermission(role_id, permission_id)
    { 
        $.ajax({
            url: "{{URL::to('master/role/permission/toggle')}}",
            type: 'GET',
            data: {
                role_id: role_id,
                permission_id: permission_id
            },
            success: function(data) {
            }, error: function(data) {
                notification(data['title'], data['msg']);
            }
        });
    }

    // Permission Toggle
    $(document).ready(function () {
        @foreach($list_permission_type as $permission_type)
        var checked = 0;
        $("#check-all-{{$permission_type->id}}").change(function () {
            <?php $array_permission = []; ?>
            @foreach(permission_get_by_type($permission_type->type) as $permission)
            <?php array_push($array_permission, $permission->id); ?>
            $("#permission-{{$permission->id}}").prop('checked', $(this).prop("checked"));
            @endforeach
            <?php $array_permission = implode(',', $array_permission);?>
            if ($("#check-all-{{$permission_type->id}}").is(":checked")) {
                checked = 1;
            } else {
                checked = 0;
            }

            updatePermissionAll('{{$array_permission}}', {{ $role->id }}, checked);
        });

        @endforeach
    });

    function updatePermissionAll(permission, role_id, attach) {
        $.ajax({
            url: "{{URL::to('master/role/permission-all')}}",
            type: 'GET',
            data: {
                role_id: role_id,
                permission_id: permission,
                attach: attach
            },
            success: function(data) {
                notification(data['title'], data['msg']);
            }, error: function(data) {
                notification(data['title'], data['msg']);
            }
        })
    }
</script>
@stop

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('core::app/master/_breadcrumb')
        <li><a href="{{ url('master/user') }}">User</a></li>
        <li><a href="{{ url('master/role') }}">Role</a></li>
        <li>Set Permission</li>
    </ul>

    <h2 class="sub-header">Role "{{ $role->name }}"</h2>
    @include('core::app.master.user._menu')
        
    @foreach($list_permission_group as $permission_group)
        @if($permission_group->group == 'BUMI DEPOSIT' || $permission_group->group == 'BUMI SHARES')
            @if(client_has_addon(strtolower(str_replace(' ','-',$permission_group->group))))
                <a href="{{ url('master/role/'.$role->id.'/permission/'.$permission_group->id) }}" class="btn btn-xs {{\Request::segment(3)=='create'?'btn-primary':'btn-warning'}}">
                    {{$permission_group->group}}
                </a>
            @endif
        @else
            <a href="{{ url('master/role/'.$role->id.'/permission/'.$permission_group->id) }}" class="btn btn-xs {{\Request::segment(3)=='create'?'btn-primary':'btn-warning'}}">
                {{$permission_group->group}}
            </a>
        @endif
    @endforeach

    <br/><br/>

    <div class="panel panel-default">
        <div class="panel-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr> 
                            <th colspan="2">Permission Name</th>
                            <th>Manage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($list_permission_type as $permission_type)
                        <tr> 
                            <td colspan="2"></td>
                            @foreach(permission_get_by_type($permission_type->type) as $permission)
                            <td class="text-center">{{$permission->action}}</td>
                            @endforeach
                        </tr>
                        <tr> 
                            <td><input type="checkbox" id="check-all-{{$permission_type->id}}" {{permission_check_all($role->id, $permission_type->type) ? 'checked' : ''}}></td>
                            <td><b>{{$permission_type->type}}</b></td>
                            @foreach(permission_get_by_type($permission_type->type) as $permission)
                            <td class="text-center"><input type="checkbox" id="permission-{{$permission->id}}" onclick="updatePermission({{ $role->id }}, {{ $permission->id }})" {{ permission_check($role->id, $permission->id) ? 'checked':'' }}></td>
                            @endforeach
                        </tr>  
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop
