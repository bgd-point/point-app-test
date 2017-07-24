@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('bumi-deposit::app/facility/bumi-deposit/_breadcrumb')
        <li>Bank</li>
    </ul>

    <h2 class="sub-header">Bank</h2>
    @include('bumi-deposit::app.facility.bumi-deposit.bank._menu')

    <div class="panel panel-default">
        <div class="panel-body">
            <form action="{{ url('facility/bumi-deposit/bank') }}" method="get" class="form-horizontal">
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
                {!! $banks->render() !!}
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Bank Name</th>
                            <th>Branch</th>
                            <th>Address</th>
                            <th>Phone</th>
                            <th>Fax</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach( $banks as $bank )
                        <tr>
                            <td>
                                <a href="{{url('facility/bumi-deposit/bank/'.$bank->id)}}">{{ $bank->name }}</a>
                            </td>
                            <td>{{ $bank->branch }}</td>
                            <td>{{ $bank->address }}</td>
                            <td>{{ $bank->phone }}</td>
                            <td>{{ $bank->fax }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {!! $banks->render() !!}
            </div>
        </div>
    </div>  
</div>
@stop
