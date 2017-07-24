@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('bumi-shares::app/facility/bumi-shares/_breadcrumb')
        <li>Broker</li>
    </ul>

    <h2 class="sub-header">Broker</h2>
    @include('bumi-shares::app.facility.bumi-shares.broker._menu')

    <div class="panel panel-default">
        <div class="panel-body">
            <form action="{{ url('facility/bumi-shares/broker') }}" method="get" class="form-horizontal">
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
                {!! $list_broker->render() !!}
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Notes</th>
                            <th>Buy Fee</th>
                            <th>Sales Fee</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($list_broker as $broker)
                        <tr id="list-{{$broker->id}}">
                            <td><a href="{{ url('facility/bumi-shares/broker/'.$broker->id) }}">{{ $broker->name }}</a></td>
                            <td>{!! nl2br(e($broker->notes)) !!}</td>
                            <td>{{ number_format_quantity($broker->buy_fee, 3) }}</td>
                            <td>{{ number_format_quantity($broker->sales_fee, 3) }}</td>
                        </tr>
                        @endforeach  
                    </tbody> 
                </table>
                {!! $list_broker->render() !!}
            </div>
        </div>
    </div>  
</div>
@stop
