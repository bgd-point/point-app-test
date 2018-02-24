@extends('core::app.layout')

@section('content')
    <div id="page-content" class="inner-sidebar-left">
        @include('framework::app.master.contact._sidebar')
        <ul class="breadcrumb breadcrumb-top">
            @include('core::app/master/_breadcrumb')
            <li>Supplier</li>
        </ul>
        <h2 class="sub-header">{{ $person_type->name }}</h2>
        @include('framework::app.master.contact._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{ url('master/contact/'.$person_type->slug) }}" method="get" class="form-horizontal">
                    <div class="form-group">
                        <div class="col-sm-3">
                            <select class="selectize" name="status" id="status" onchange="selectData()">
                                <option value="0" @if(\Input::get('status')== 0) selected @endif>Enable</option>                            
                                <option value="1" @if(\Input::get('status')== 1) selected @endif>Disabled</option>                            
                            </select>
                        </div>
                        <div class="col-sm-3">
                            <input type="text" name="search" id="search" class="form-control" placeholder="Search Name..." value="{{\Input::get('search')}}" autofocus>
                        </div>
                        <div class="col-sm-6">
                            <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search</button>
                        </div>
                    </div>
                </form>

                <br/>

                <div class="table-responsive">
                {!! $list_contact->appends(['status'=>app('request')->get('status'), 'search'=>app('request')->get('search')])->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th width="100px" class="text-center"></th>
                                <th>Name</th>
                                <th>Group</th>
                                @if ($person_type->name == 'customer')
                                    <th>Credit Ceiling</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($list_contact as $contact)
                            <tr id="list-{{ $contact->id }}">
                                <td class="text-center">
                                    <a href="{{ url('master/contact/'. $person_type->slug .'/'.$contact->id) }}" data-toggle="tooltip" title="Show" class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-file"></i></a>
                                    <a href="javascript:void(0)" data-toggle="tooltip" title="Delete" class="btn btn-effect-ripple btn-xs btn-danger" onclick="secureDelete({{ $contact->id }}, '{{url('master/contact/'. $person_type->id .'/delete')}}')"><i class="fa fa-times"></i></a>

                                    <a id="link-state-{{$contact->id}}" href="javascript:void(0)" data-toggle="tooltip" 
                                    title="{{$contact->disabled == 0 ? 'disable' : 'enable' }}" 
                                    class="btn btn-effect-ripple btn-xs {{$contact->disabled == 0 ? 'btn-success' : 'btn-default' }}" 
                                    onclick="state({{$contact->id}})">
                                    <i id="icon-state-{{$contact->id}}" class="{{$contact->disabled == 0 ? 'fa fa-pause' : 'fa fa-play' }}"></i></a>
                                </td>
                                <td><a href="{{ url('master/contact/'. $person_type->slug .'/'.$contact->id) }}">{{ $contact->codeName}}</a></td>
                                <td>{{ $contact->group->name }}</td>
                                @if ($person_type->name == 'customer')
                                    <td>{{ number_format($contact->credit_ceiling) }}</td>
                                @endif
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $list_contact->appends(['status'=>app('request')->get('status'), 'search'=>app('request')->get('search')])->render() !!}
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
            
            $("#link-state-"+index).attr('title', status);
            if(result.data_value == 0 ){
                $("#link-state-"+index).removeClass("btn-default").addClass("btn-success");
                $("#icon-state-"+index).removeClass("fa fa-play").addClass("fa fa-pause");
            } else {
                $("#link-state-"+index).removeClass("btn-success").addClass("btn-default");
                $("#icon-state-"+index).removeClass("fa fa-pause").addClass("fa fa-play");
            } 
                                            
            swal(result.status, result.message,"success");
            
        }, error: function(data){
            swal('Failed', 'Something went wrong', 'error');
        }
    });
} 

function selectData() {
    var status = $("#status option:selected").val();
    var search = $("#search").val();
    var url = '{{url()}}/master/contact/{{$person_type->slug}}/?status='+status+'&search='+search;
    location.href = url;
}
</script>
@stop

