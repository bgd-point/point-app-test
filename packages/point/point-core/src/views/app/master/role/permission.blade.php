@extends('core::app.layout')

@section('scripts') 
<script>
    var role_id = <?php echo $role->id; ?>;

    function updatePermission(permission_id)
    { 
        $.ajax({
            url: "{{URL::to('master/role/permission/toggle')}}",
            type: 'GET',
            data: {
                role_id: role_id,
                permission_id: permission_id
            },
            success: function(data) {
                notification(data['title'], data['msg']);
            }, error: function(data) {
                notification(data['title'], data['msg']);
            }
        });
    }

    // update permission by action {
    $('.select-by-action input').on('change', function(e) {
        var selected = e.currentTarget.checked ? 1 : 0;
        var toUpdate = $(e.currentTarget).data('permission');
        var permissions = [];
        $("input[type='checkbox'][data-action='" + toUpdate +"']").prop('checked', selected).each(function(index, el) {
            permissions.push($(el).attr('id').split('-')[1]);
        });
        permissions = permissions.join(',');
        updatePermissionAll(permissions, role_id, selected);
    });

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

            updatePermissionAll('{{$array_permission}}', role_id, checked);
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
        <div class="panel-body select-by-action">
            <?php
                // save unique permission
                $unique_permission = array();
                // save value if that permission is checked all or not
                $unique_permission_value = array();

                foreach($list_permission_type as $permission_type) {
                    foreach(permission_get_by_type($permission_type->type) as $permission) {
                        // add permission to array if it is not yet in the array
                        if (!in_array($permission->action, $unique_permission)) {
                            $unique_permission[] = $permission->action;
                            $unique_permission_value[] = true;
                        }

                        // get index of current permission
                        if(!permission_check($role->id, $permission->id)) {
                            $index = array_search($permission->action,$unique_permission);
                            $unique_permission_value[$index] = false;
                        }
                    }
                }
            ?>
            <h2 class="sub-header">Select all</h2>
            @foreach($unique_permission as $key=>$value)
                <span style="margin-right: 20px;">
                    <input type="checkbox" id="p-{{ $key }}" {{ $unique_permission_value[$key] ? 'checked' : ''}} data-permission="{{ $value }}">
                    <label for="p-{{$key}}">{{ $value }}</label>
                </span>
            @endforeach
        </div>
    </div>

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
                            <td class="text-center"><input type="checkbox" id="permission-{{$permission->id}}" onclick="updatePermission({{ $permission->id }})" {{ permission_check($role->id, $permission->id) ? 'checked':'' }} data-action="{{$permission->action}}"></td>
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
