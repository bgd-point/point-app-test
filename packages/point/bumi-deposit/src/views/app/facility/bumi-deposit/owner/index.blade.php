@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('bumi-deposit::app/facility/bumi-deposit/_breadcrumb')
        <li>Owner</li>
    </ul>

    <h2 class="sub-header">Owner</h2>
    @include('bumi-deposit::app.facility.bumi-deposit.owner._menu')

    <div class="panel panel-default">
        <div class="panel-body">
            <form action="{{ url('facility/bumi-deposit/owner') }}" method="get" class="form-horizontal">
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
                {!! $owners->render() !!}
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach( $owners as $owner )
                        <tr>
                            <td>
                                <a href="{{url('facility/bumi-deposit/owner/'.$owner->id)}}">{{ $owner->name }}</a>
                            </td>
                            <td>{{ $owner->notes }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {!! $owners->render() !!}
            </div>
        </div>
    </div>  
</div>
@stop
