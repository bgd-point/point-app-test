@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('bumi-shares::app/facility/bumi-shares/_breadcrumb')
        <li>Owner</li>
    </ul>

    <h2 class="sub-header">Owner</h2>
    @include('bumi-shares::app.facility.bumi-shares.owner._menu')

    <div class="panel panel-default">
        <div class="panel-body">
            <form action="{{ url('facility/bumi-shares/owner') }}" method="get" class="form-horizontal">
                <div class="form-group">
                    <div class="col-sm-3">
                        <input type="text" name="search" class="form-control" placeholder="Search Name..." value="{{app('request')->input('search')}}" autofocus>
                    </div>
                    <div class="col-sm-9">
                        <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search</button>
                    </div>
                </div>
            </form>

            <br/>

            <div class="table-responsive">
                
                {!! $owner_list->render() !!}
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($owner_list as $owner)
                        <tr id="list-{{$owner->id}}">
                            <td><a href="{{ url('facility/bumi-shares/owner/'.$owner->id) }}">{{ $owner->name }}</a></td>
                        </tr>
                        @endforeach  
                    </tbody> 
                </table>
                {!! $owner_list->render() !!}
            </div>
        </div>
    </div>  
</div>
@stop
