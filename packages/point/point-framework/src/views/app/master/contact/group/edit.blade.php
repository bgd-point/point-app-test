@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('core::app/master/_breadcrumb')
        <li><a href="{{ url('master/contact/'.$person_type->name) }}">{{ $person_type->name }}</a></li>
        <li><a href="{{ url('master/contact/'.$person_type->name.'/group') }}">Group</a></li>
        <li>Edit</li>
    </ul>

    <h2 class="sub-header">Group {{ $person_type->name }}</h2>
    @include('framework::app.master.contact._menu')
    @include('core::app.error._alert')

    <div class="col-sm-4">
        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('master/contact/'.$person_type->slug.'/group/'.$person_group->id)}}" method="post" class="form-horizontal form-bordered">
                    {!! csrf_field() !!}
                    <input name="_method" type="hidden" value="PUT">

                    <fieldset>
                        <legend><i class="fa fa-angle-right"></i> Edit</legend>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Name*</label>
                            <div class="col-md-12">
                                <input type="text" name="name" class="form-control" value="{{$person_group->name}}">
                            </div>
                        </div>
                    </fieldset>

                    <div class="form-group">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-effect-ripple btn-primary">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-sm-8">
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="table-responsive">
                    {!! $list_person_group->render() !!}
                    <table id="list-datatable" class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th width="50px" class="text-center"></th>
                            <th>NAME</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($list_person_group as $person_group)
                            <tr id="list-{{$person_group->id}}">
                                <td>
                                    <a href="{{url('master/contact/'.$person_type->slug.'/group/'.$person_group->id.'/edit')}}"
                                       class="btn btn-effect-ripple btn-xs btn-warning"><i class="fa fa-pencil"></i></a>
                                    <a href="javascript:void(0)" data-toggle="tooltip" title="Delete"
                                       class="btn btn-effect-ripple btn-xs btn-danger"
                                       onclick="secureDelete({{$person_group->id}}, '{{url('master/contact/'.$person_type->slug.'/group/delete')}}')"><i
                                                class="fa fa-times"></i></a>
                                </td>
                                <td>{{ $person_group->name }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $list_person_group->render() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@stop
