@extends('core::app.layout')

@section('content')
    <div id="page-content" class="inner-sidebar-left">

        @include('framework::app.master.contact._sidebar')

        <ul class="breadcrumb breadcrumb-top">
            @include('core::app/master/_breadcrumb')
            <li><a href="{{ url('master/contact/'.$person_type->name) }}">Supplier</a></li>
            <li>{{ $person->code }}</li>
        </ul>

        <h2 class="sub-header">{{ $person_type->name }}</h2>
        @include('framework::app.master.contact._menu')

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
                            <label class="col-md-3 control-label">Group</label>
                            <div class="col-md-6 content-show">
                                {{$person->group->name}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Code</label>
                            <div class="col-md-6 content-show">
                                {{$person->code}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Name</label>
                            <div class="col-md-6 content-show">
                                {{$person->name}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Email</label>
                            <div class="col-md-6 content-show">
                                {{$person->email}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Address</label>
                            <div class="col-md-6">
                                {{$person->address}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Phone</label>
                            <div class="col-md-6">
                                {{$person->phone}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Notes</label>
                            <div class="col-md-6">
                                {{$person->notes}}
                            </div>
                        </div>

                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-3">
                                </div>
                                <div class="col-md-9">
                                    <legend><i class="fa fa-angle-right"></i> Contact Person</legend>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-3">
                                </div>
                                <div class="col-md-9">
                                    <div class="table-responsive">
                                        <table id="contact-datatable" class="table table-striped">
                                            <thead>
                                            <tr>
                                                <th></th>
                                                <th>NAME</th>
                                                <th>PHONE</th>
                                                <th>ADDRESS</th>
                                                <th>EMAIL</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($person->contacts as $contact)
                                                <tr>
                                                    <td></td>
                                                    <td>{{$contact->name}}</td>
                                                    <td>{{$contact->phone}}</td>
                                                    <td>{{$contact->address}}</td>
                                                    <td>{{$contact->email}}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-3">
                                </div>
                                <div class="col-md-9">
                                    <legend><i class="fa fa-angle-right"></i> Data Bank</legend>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-3">
                                </div>
                                <div class="col-md-9">
                                    <div class="table-responsive">
                                        <table id="bank-datatable" class="table table-striped">
                                            <thead>
                                            <tr>
                                                <th></th>
                                                <th>BANK BRANCH</th>
                                                <th>BANK NAME</th>
                                                <th>ACCOUNT NUMBER</th>
                                                <th>ACCOUNT NAME</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($person->banks as $bank)
                                                <tr>
                                                    <td></td>
                                                    <td>{{$bank->branch}}</td>
                                                    <td>{{$bank->name}}</td>
                                                    <td>{{$bank->account_number}}</td>
                                                    <td>{{$bank->account_name}}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
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
                    <a href="{{url('master/contact/'.$person_type->slug.'/'.$person->id.'/edit')}}" class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger" onclick="secureDelete({{$person->id}}, '{{url('master/contact/'.$person_type->slug.'/delete')}}', '/master/contact/{{$person_type->slug}}')"><i class="fa fa-times"></i> Delete</a>

                    <a id="link-state-{{$person->id}}" href="javascript:void(0)" 
                    class="btn btn-effect-ripple {{$person->disabled == 0 ? 'btn-success' : 'btn-default' }}" 
                    onclick="state({{$person->id}})">
                    <i id="icon-state-{{$person->id}}" class="{{$person->disabled == 0 ? 'fa fa-pause' : 'fa fa-play' }}"></i> {{$person->disabled == 0 ? 'disable' : 'enable' }}</a>

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
        url: "{{URL::to('master/contact/state')}}",
        data: {
            index: index,
            slug: {!! json_encode($person_type->slug) !!}
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
            
        }, error: function(e){
            swal('Failed','Something went wrong','error');
        }
    });
} 

</script>
@stop

