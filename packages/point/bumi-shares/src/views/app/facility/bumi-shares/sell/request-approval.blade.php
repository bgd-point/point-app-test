@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('bumi-shares::app/facility/bumi-shares/_breadcrumb')
        <li><a href="{{ url('facility/bumi-shares/sell') }}">Sell</a></li>
        <li>Request Approval</li>
    </ul>

    <h2 class="sub-header">Sell Shares</h2>
    @include('bumi-shares::app.facility.bumi-shares.sell._menu')
    
    <form action="{{url('facility/bumi-shares/sell/send-request-approval')}}" method="post">
        {!! csrf_field() !!}

        <div class="panel panel-default">
            <div class="panel-body">            
                <div class="table-responsive">
                    {!! $list_shares_sell->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center">
                                    <input type="checkbox" id="check-all">
                                </th>
                                                     
                                <th>Form Date</th>
                                <th>Form Number</th>
                                <th>Shares</th>
                                <th>Group</th>
                                <th>Price</th>
                                <th>Approval To</th>
                                <th>Last Request</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($list_shares_sell as $shares_sell)
                            <tr id="list-{{$shares_sell->formulir_id}}">
                                <td class="text-center">
                                    <input type="checkbox" name="formulir_id[]" value="{{$shares_sell->formulir_id}}">
                                </td>
                                <td>{{ date_format_view($shares_sell->formulir->form_date) }}</td>
                                <td><a href="{{ url('facility/bumi-shares/sell/'.$shares_sell->id) }}">{{ $shares_sell->formulir->form_number}}</a></td>
                                <td>{{ $shares_sell->shares->name }}</td>
                                <td>{{ $shares_sell->ownerGroup->name }}</td>
                                <td>{{ \NumberHelper::formatAccounting($shares_sell->price) }}</td>
                                <td>{{ $shares_sell->formulir->approvalTo->name }}</td>
                                <td>
                                @if($shares_sell->formulir->request_approval_at == null)
                                    -
                                @else
                                    {{ date_format_view($shares_sell->formulir->request_approval_at, true) }}
                                @endif
                                </td>
                            </tr>
                            @endforeach  
                        </tbody> 
                    </table>
                    {!! $list_shares_sell->render() !!}
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-effect-ripple btn-primary">Send Request</button>
                    </div>
                </div>
            </div>
        </div>          
    </form>
</div>
@stop
