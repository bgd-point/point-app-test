@extends('core::app.layout')

@section('scripts') 
<script>
    function updateRole(user_id, role_id)
    { 
        $.ajax({
            url: "{{URL::to('master/user/role/toggle')}}",
            type: 'GET',
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
        <li><a href="{{ url('master/user/'.$user->id) }}">{{ $user->name }}</a></li>
        <li>Role</li>
    </ul>

    <h2 class="sub-header">User "{{ $user->name }}"</h2>
    @include('core::app.master.user._menu')

    <div class="panel panel-default">
        <div class="panel-body">
            <form action="{{ url('master/user/'.$user->id.'/role') }}" method="get" class="form-horizontal">
                <div class="form-group">
                    <div class="col-sm-3">
                        <input type="text" name="search" class="form-control" placeholder="Search Name..." value="{{\Input::get('search')}}" autofocus>
                    </div>
                    <div class="col-sm-9">
                        <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search</button> 
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr> 
                            <th width="100px">Manage</th>
                            <th>Role</th>                            
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $role)
                        <tr>
                            <td class="text-center"><input type="checkbox" onclick="updateRole({{ $user->id }},{{ $role->id }})" {{ \Point\Core\Models\Master\RoleUser::check($user->id, $role->id) ? 'checked':'' }}></td>
                            <td>{{$role->name}}</td>                            
                        </tr>  
                        @endforeach
                    </tbody>
                </table>

                <a href="{{ url('/master/warehouse/set-user') }}" class="btn btn-primary">Set User Warehouse</a>
            </div>
        </div>
    </div>
</div>
@stop
