@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('core::app/master/_breadcrumb')
        <li>User</li>
    </ul>

    <h2 class="sub-header">User</h2>
    @include('core::app.master.user._menu')
    <div class="panel panel-default">
        <div class="panel-body">
            <form action="{{ url('master/user') }}" method="get" class="form-horizontal">
                <div class="form-group">
                    <div class="col-sm-3">
                        <input type="text" name="search" class="form-control" placeholder="Search Name..." value="{{\Input::get('search')}}" autofocus>
                    </div>
                    <div class="col-sm-9">
                        <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search</button> 
                    </div>
                </div>
            </form>

            <br/>

            <div class="table-responsive">
                {!! $users->appends(['search'=>app('request')->get('search')])->render() !!}
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th width="100px" class="text-center"></th>
                            <th></th>
                            <th>NAME</th>
                            <th>EMAIL</th>
                            <th>ROLE</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr id="list-{{$user->id}}" @if($user->disabled) style="color:white;background: red;" @endif>
                            <td class="text-center">
                                <a href="{{ url('master/user/'.$user->id) }}" data-toggle="tooltip" title="Show" class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-file"></i></a>
                                @if($user->id > 2)
                                <a href="javascript:void(0)" data-toggle="tooltip" title="Delete" class="btn btn-effect-ripple btn-xs btn-danger" onclick="secureDelete({{$user->id}}, '{{url('master/user/delete')}}')"><i class="fa fa-times"></i></a>
                                <a id="link-state-{{$user->id}}" href="javascript:void(0)" data-toggle="tooltip"
                                    title="{{$user->disabled == 0 ? 'disable' : 'enable' }}"
                                    class="btn btn-effect-ripple btn-xs {{$user->disabled == 0 ? 'btn-success' : 'btn-default' }}"
                                    onclick="state({{$user->id}})">
                                    <i id="icon-state-{{$user->id}}" class="{{$user->disabled == 0 ? 'fa fa-pause' : 'fa fa-play' }}"></i>
                                </a>
                                @endif
                            </td> 
                            <td><img src="@include('core::app._avatar', ['user_id' => $user->id])" alt="avatar" style="width:40px;height:40px"></td>
                            <td><a @if($user->disabled) style="color:white;" @endif href="{{ url('master/user/'.$user->id) }}">{{ $user->name }}</a></td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @foreach($user->allRoles($user->id) as $roleUser)
                                    <a href="{{url('master/role/'.$roleUser->role_id)}}" class="label label-primary">
                                        {{\Point\Core\Models\Master\Role::find($roleUser->role_id)->name}}
                                    </a>
                                    <br/>
                                @endforeach
                            </td>
                        </tr>
                        @endforeach  
                    </tbody> 
                </table>
                {!! $users->appends(['search'=>app('request')->get('search')])->render() !!}
            </div>
        </div>
    </div>  
</div>
@stop

@section('scripts')
    <script>
      function state(index) {
        $.ajax({
          type:'post',
          url: "{{URL::to('master/user/state')}}",
          data: {
            index: index
          },
          success: function(result){
            if(result.status === "failed"){
              swal(result.status, result.message);
              return false;
            }

            var status = result.data_value == 0 ? 'enable' : 'disable';
            $("#link-state-"+index).attr('title', status);

            if(result.data_value == 0 ){
              $("#link-state-"+index).removeClass("btn-default").addClass("btn-success");
              $("#icon-state-"+index).removeClass("fa fa-play").addClass("fa fa-pause");
            } else {
              $("#link-state-"+index).removeClass("btn-success").addClass("btn-default");
              $("#icon-state-"+index).removeClass("fa fa-pause").addClass("fa fa-play");
            }

            swal(result.status, result.message,"success");
          }, error: function(e){
            swal('Failed', 'Something went wrong', 'error');
          }
        });
      }
    </script>
@stop
