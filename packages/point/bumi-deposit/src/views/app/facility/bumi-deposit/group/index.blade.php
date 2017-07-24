@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('bumi-deposit::app/facility/bumi-deposit/_breadcrumb')
        <li>Group</li>
    </ul>

    <h2 class="sub-header">Group</h2>
    @include('bumi-deposit::app.facility.bumi-deposit.group._menu')

    <div class="panel panel-default">
        <div class="panel-body">
            <form action="{{ url('facility/bumi-deposit/group') }}" method="get" class="form-horizontal">
                <div class="form-group">
                    <div class="col-sm-3">
                        <input type="text" name="search" class="form-control" placeholder="Search ..." value="{{\Input::get('search') ? \Input::get('search') : ''}}">
                    </div>
                    <div class="col-sm-3">
                        <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search</button>
                    </div>
                </div>
            </form>

            <br/>

            <div class="table-responsive">
                {!! $groups->render() !!}
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach( $groups as $group )
                        <tr>
                            <td>
                                <a href="{{url('facility/bumi-deposit/group/'.$group->id)}}">{{ $group->name }}</a>
                            </td>
                            <td>{{ $group->notes }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {!! $groups->render() !!}
            </div>
        </div>
    </div>  
</div>
@stop
