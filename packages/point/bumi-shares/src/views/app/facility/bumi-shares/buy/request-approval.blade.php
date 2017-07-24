@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('bumi-shares::app/facility/bumi-shares/_breadcrumb')
        <li><a href="{{ url('facility/bumi-shares/buy') }}">Buy</a></li>
        <li>Request Approval</li>
    </ul>

    <h2 class="sub-header">Buy Shares</h2>
    @include('bumi-shares::app.facility.bumi-shares.buy._menu')
    
    <form action="{{url('facility/bumi-shares/buy/send-request-approval')}}" method="post">
        {!! csrf_field() !!}

        <div class="panel panel-default">
            <div class="panel-body">            
                <div class="table-responsive">
                    {!! $list_shares_buy->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center">
                                    <input type="checkbox" id="check-all">
                                </th>
                                                     
                                <th>Form Date</th>
                                <th>Form Number</th>
                                <th>Owner</th>
                                <th>Shares</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Approval To</th>
                                <th>Last Request</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($list_shares_buy as $shares_buy)
                             <tr id="list-{{$shares_buy->formulir_id}}">
                                <td class="text-center">
                                 <input type="checkbox" name="formulir_id[]" value="{{$shares_buy->formulir_id}}">
                                </td>
                                <td>{{ date_format_view($shares_buy->formulir->form_date) }}</td>
                                <td><a href="{{ url('facility/bumi-shares/buy/'.$shares_buy->id) }}">{{ $shares_buy->formulir->form_number}}</a></td>
                                <td>{{ $shares_buy->owner->name }}</td>
                                <td>{{ $shares_buy->shares->name }}</td>
                                <td>{{ number_format_quantity($shares_buy->quantity) }}</td>
                                <td>{{ number_format_quantity($shares_buy->price) }}</td>
                                <td>{{ $shares_buy->formulir->approvalTo->name }}</td>
                                <td>
                                @if($shares_buy->formulir->request_approval_at == null)
                                    -
                                @else
                                    {{ date_format_view($shares_buy->formulir->request_approval_at, true) }}
                                @endif
                                </td>
                            </tr>
                            @endforeach  
                        </tbody> 
                    </table>
                    {!! $list_shares_buy->render() !!}
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-effect-ripple btn-primary">send request</button>
                    </div>
                </div>
            </div>
        </div>          
    </form>
</div>
@stop
