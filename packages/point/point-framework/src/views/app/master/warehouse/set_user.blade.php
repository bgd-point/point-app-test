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

                    <div class="form-group">
                        <label class="col-md-3 control-label">User</label>
                        <div class="col-md-6">
                            <select class="selectize user" style="width: 100%;">
                                <option value="">Select User...</option>
                                @foreach($list_user as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>}
                </div>
            </div>
            <div class="panel-body">
                <div class="col-md-6 col-md-offset-3">
                    <div class="table-responsive" id="table-warehouse">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <th></th>
                                <th>Warehouse Name</th>
                            </thead>
                            <tbody>
                            @foreach($list_warehouse as $warehouse)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" data-id="{{$warehouse->id}}">
                                    </td>
                                    <td style="width: 100%">{{ $warehouse->name }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
<script>
function updateWarehouse(user_id, warehouse_id, value){
    $.ajax({
        url: "{{URL::to('master/warehouse/set-user')}}",
        type: 'POST',
        data: {
            user_id,
            warehouse_id,
            value,
        },
        success: function(data) {
            notification('success', 'Warehouse has been set');
        },error: function(data){
            notification('Failed', 'Something went wrong');
        }
    });
}

function resetCheckboxWarehouse() {
    $("input[type=checkbox]").prop('checked', false);
}
function setCheckboxWarehouse(id) {
    $("input[type=checkbox][data-id="+id+"]").prop('checked', true);
}

const list_user = {!! json_encode($list_user) !!};

$(".user").change(e => {
    resetCheckboxWarehouse();

    let userId = Number(e.currentTarget.value);

    let user = list_user.find(user => {
        return user.id === userId;
    });
    
    user.warehouse.forEach(warehouse => {
        setCheckboxWarehouse(warehouse.warehouse_id);
    });
});

$("input[type=checkbox]").click(e => {
    let warehouse_id = $(e.currentTarget).data("id");
    let user_id = Number($(".user")[0].value);
    let value = $(e.currentTarget).is(":checked");
    console.log(user_id, warehouse_id, value);
    updateWarehouse(user_id, warehouse_id, value);
});

</script>
@stop
