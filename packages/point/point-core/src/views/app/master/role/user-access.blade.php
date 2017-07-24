@extends('core::app.layout')

@section('scripts') 
<script>
    function updateAccess(user_id, role_id)
    { 
        $.ajax({
            url: "{{URL::to('master/role/user-access/toggle')}}",
            type: 'POST',
            data: {
                user_id: user_id,
                role_id: role_id
            },
            success: function(data) {
                notification(data['title'], data['msg']);
            }, error: function(data) {
                notification(data['title'], data['msg']);
            }
        });
    }
</script>
@stop

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('core::app/master/_breadcrumb')
        <li><a href="{{ url('master/user') }}">User</a></li>
        <li><a href="{{ url('master/role') }}">Role</a></li>
        <li>Set User Access</li>
    </ul>

    <h2 class="sub-header">Role</h2>
    @include('core::app.master.user._menu')
        
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="table-responsive">
                <table id="example-datatable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th width="100px" class="text-center">Access</th>
                            <th>User</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td class="border-bottom text-center"><input type="checkbox" onchange="updateAccess({{ $user->id }}, {{$role->id}})" {{ $user->hasRole($role->id) ? 'checked':'' }}></td>
                            <td>{{ $user->name }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop
