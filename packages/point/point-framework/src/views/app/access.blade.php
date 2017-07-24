@extends('core::app.layout')

@section('scripts')
    <script>
        function updateAccess(user_id, permission_id)
        {
            $.ajax({
                url: "{{URL::to('temporary-access/toggle')}}",
                type: 'POST',
                data: {
                    user_id: user_id,
                    permission_id: permission_id
                },
                error: function(data) {
                    notification(data['title'], data['msg']);
                }
            });
        }
    </script>
@stop

@section('content')
    <div id="page-content">
        <a href="{{\URL::previous()}}" class="pull-right">
            <i class="fa fa-arrow-circle-left push-bit"></i> Back
        </a>
        <h2 class="sub-header">{{ $title }}</h2>

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{ url('temporary-access/'.$title.'/'.$permission_type->type) }}" method="get" class="form-horizontal">
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
                    <table id="example-datatable" class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th rowspan="2">User</th>
                            <th rowspan="2">Access</th>
                            <th colspan="7">Manage</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td colspan="2"></td>
                                @foreach(permission_get_by_type($permission_type->type) as $permission)
                                    <td class="text-center">{{$permission->action}}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td rowspan="2" class="border-bottom">{{ $user->name }}</td>
                                <td>Role</td>
                                @foreach(permission_get_by_type($permission_type->type) as $permission)
                                    @if($user->checkRole($permission->id))
                                        <td class="border-bottom text-center"><input type="checkbox" onclick="return false" {{ $user->checkRole($permission->id) ? 'checked':'' }}></td>
                                    @else
                                        <td></td>
                                    @endif

                                @endforeach
                            </tr>
                            <tr style="border-bottom:1px solid black">
                                <td class="border-bottom">Temporary</td>
                                @foreach(permission_get_by_type($permission_type->type) as $permission)

                                    @if(! $user->checkRole($permission->id))
                                        <td class="border-bottom text-center"><input type="checkbox" onchange="updateAccess({{ $user->id }}, {{ $permission->id }})" {{ $user->may($permission->slug) ? 'checked':'' }}></td>
                                    @else
                                        <td></td>
                                    @endif

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
