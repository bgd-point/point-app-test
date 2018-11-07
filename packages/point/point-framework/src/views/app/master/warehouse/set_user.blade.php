@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('core::app/master/_breadcrumb')
            <li><a href="{{ url('master/warehouse') }}">Warehouse</a></li>
            <li>Set User</li>
        </ul>

        <h2 class="sub-header">Warehouse</h2>

        @include('framework::app.master.warehouse._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                <div class="form-horizontal form-bordered">
                    {!! csrf_field() !!}
                    <input name="_method" type="hidden" value="PUT">

                    <fieldset>
                        <legend><i class="fa fa-angle-right"></i> Set User Warehouse</legend>
                        <div class="form-group">
                            <div class="col-md-9">
                                <span class="help-block">
                                    Set user related warehouse, when user is linked to the warehouse, then every transaction will be related to this warehouse
                                </span>
                            </div>
                        </div>
                    </fieldset>

                    @foreach($list_user as $user)
                    <div class="form-group">
                        <label class="col-md-3 control-label">
                            {{ $user->name }}
                        </label>
                        <div class="col-md-6">
                            <select id="warehouse-id" name="warehouse_id" class="selectize" style="width: 100%;" data-placeholder="Select Warehouse .." onchange="updateWarehouse({{$user->id}}, this.value)">
                                <option value="0">All Warehouse</option>
                                @foreach($list_warehouse as $warehouse)
                                    <option value="{{$warehouse->id}}" @if(\Point\Framework\Models\Master\UserWarehouse::getWarehouse($user->id) == $warehouse->id) selected @endif>{{$warehouse->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @endforeach

                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-3">
                            <a href="{{ url('master/warehouse') }}" class="btn btn-effect-ripple btn-primary">Done</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script>
        function updateWarehouse(user_id, warehouse_id){
            $.ajax({
                url: "{{URL::to('master/warehouse/set-user')}}",
                type: 'POST',
                data: {
                    user_id: user_id,
                    warehouse_id: warehouse_id
                },
                success: function(data) {
                    notification('success', 'Warehouse has been set');
                },error: function(data){
                    notification('Failed', 'Something went wrong');
                }
            });
        }
    </script>
@stop
