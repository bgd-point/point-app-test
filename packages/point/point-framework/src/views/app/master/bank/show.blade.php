@extends('core::app.layout')
 
@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('core::app/master/_breadcrumb')
        <li><a href="{{ url('master/bank') }}">Bank</a></li>
        <li>{{ $bank->name }}</li>
    </ul>

    <h2 class="sub-header">Bank "{{ $bank->name }}"</h2>
    @include('framework::app.master.bank._menu')

    <div class="block full">
        <!-- Block Tabs Title -->
        <div class="block-title">
            <ul class="nav nav-tabs" data-toggle="tabs">
                <li class="active"><a href="#block-tabs-form">Form</a></li>
                <li><a href="#block-tabs-profile">History</a></li>
                <li><a href="#block-tabs-settings"><i class="gi gi-settings"></i></a></li>
            </ul>
        </div>
        <!-- END Block Tabs Title -->

        <!-- Tabs Content -->
        <div class="tab-content">
            <div class="tab-pane active" id="block-tabs-form">
                <div class="form-horizontal form-bordered">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Cash Account</label>
                        <div class="col-md-6 content-show">{{$bank->pettyCash ? $bank->pettyCash->account : ''}}</div>
                    </div>
                </div>
                <div class="form-horizontal form-bordered">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Code</label>
                        <div class="col-md-6 content-show">{{$bank->code}}</div>
                    </div> 
                </div>
                <div class="form-horizontal form-bordered">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Bank Name</label>
                        <div class="col-md-6 content-show">{{$bank->name}}</div>
                    </div> 
                </div>
                <div class="form-horizontal form-bordered">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Store Name</label>
                        <div class="col-md-6 content-show">{{$bank->store_name}}</div>
                    </div> 
                </div>
                <div class="form-horizontal form-bordered">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Address</label>
                        <div class="col-md-6 content-show">{{$bank->address}}</div>
                    </div> 
                </div>
                <div class="form-horizontal form-bordered">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Phone</label>
                        <div class="col-md-6 content-show">{{$bank->phone}}</div>
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
                <a href="{{url('master/bank/'.$bank->id.'/edit')}}" class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger" onclick="secureDelete({{$bank->id}}, '{{url('master/bank/delete')}}', '/master/bank')"><i class="fa fa-times"></i> Delete</a>

                <a id="link-state-{{$bank->id}}" href="javascript:void(0)" 
                class="btn btn-effect-ripple {{$bank->disabled == 0 ? 'btn-success' : 'btn-default' }}" 
                onclick="state({{$bank->id}})">
                <i id="icon-state-{{$bank->id}}" class="{{$bank->disabled == 0 ? 'fa fa-pause' : 'fa fa-play' }}"></i> {{$bank->disabled == 0 ? 'disable' : 'enable' }}</a>
            </div>
        </div>
        <!-- END Tabs Content -->
    </div> 
</div>
@stop

@section('scripts')
<script>

function state(index) {
    $.ajax({
        type:'post',
        url: "{{URL::to('master/bank/state')}}",
        data: {
            index: index,
        },
        success: function(result){
            if(result.status === "failed"){
                swal(result.status, result.message,"error");
                return false;
            }

            var status = result.data_value == 0 ? 'enable' : 'disable'; 
            
            if(result.data_value == 0 ){
                $("#link-state-"+index).removeClass("btn-default").addClass("btn-success");
                $("#icon-state-"+index).removeClass("fa fa-play").addClass("fa fa-pause");
                $("#link-state-"+index).html("<i class='fa fa-pause'></i> disable");
            } else {
                $("#link-state-"+index).removeClass("btn-success").addClass("btn-default");
                $("#icon-state-"+index).removeClass("fa fa-pause").addClass("fa fa-play");
                $("#link-state-"+index).html("<i class='fa fa-play'></i> enable");
            } 
                                            
            swal(result.status, result.message,"success");
        }, error : function(e){
            swal('Failed', 'Something went wrong', 'error');
        }
    });
} 
</script>
@stop
