@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('bumi-shares::app/facility/bumi-shares/_breadcrumb')
        <li>Buy</li>
    </ul>

    <h2 class="sub-header">Buy Shares</h2>
    @include('bumi-shares::app.facility.bumi-shares.buy._menu')

    <div class="panel panel-default">
        <div class="panel-body">
            <form action="{{ url('facility/bumi-shares/buy') }}" method="get" class="form-horizontal">
                <div class="form-group">
                    <div class="col-sm-3">
                        <select class="selectize" name="status" id="status" onchange="selectData('form_date', 'desc')">
                            <option value="0" @if(\Input::get('status') == 0) selected @endif>open</option>
                            <option value="1" @if(\Input::get('status') == 1) selected @endif>closed</option>
                            <option value="-1" @if(\Input::get('status') == -1) selected @endif>canceled</option>
                            <option value="all" @if(\Input::get('status') == 'all') selected @endif>all</option>
                        </select>
                    </div>
                    
                    <div class="col-sm-4">
                        <div class="input-group input-daterange" data-date-format="{{date_format_masking()}}">
                            <input type="text" name="date_from" id="date-from" class="form-control date input-datepicker" placeholder="From"  value="{{app('request')->input('date_from') ? app('request')->input('date_from') : ''}}">
                            <span class="input-group-addon"><i class="fa fa-chevron-right"></i></span>
                            <input type="text" name="date_to" id="date-to" class="form-control date input-datepicker" placeholder="To" value="{{app('request')->input('date_to') ? app('request')->input('date_to') : ''}}">
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <input type="text" name="search" id="search" class="form-control" placeholder="Search" value="{{app('request')->input('search')}}" value="{{app('request')->input('search') ? app('request')->input('search') : ''}}">
                    </div>
                    <div class="col-sm-1">
                        <input type="hidden" name="order_by" value="{{\Input::get('order_by') ? \Input::get('order_by') : 'form_date'}}">
                        <input type="hidden" name="order_type" value="{{\Input::get('order_type') ? \Input::get('order_type') : 'desc'}}">
                        <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search</button>
                    </div>
                </div>
            </form>

            <br/>
            <?php 
                $order_by = \Input::get('order_by') ? : 0;
                $order_type = \Input::get('order_type') ? : 0;
            ?>
            
            @if($list_shares_buy->count() > 0)

            <div class="table-responsive">
                {!! $list_shares_buy->appends(['order_by'=>app('request')->get('order_by'), 'order_type'=>app('request')->get('order_type'), 'status'=>app('request')->get('status'), 'search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>                             
                            <th style="cursor:pointer" onclick="selectData('form_date', @if($order_by == 'form_date' && $order_type == 'asc') 'desc' @elseif($order_by == 'form_date' && $order_type == 'desc') 'asc' @else 'desc' @endif)">Date <span class="pull-right"><i class="fa @if($order_by == 'form_date' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'form_date' && $order_type == 'desc') fa-sort-desc @else fa-sort-asc @endif fa-fw"></i></span></th>
                            <th style="cursor:pointer" onclick="selectData('form_number', @if($order_by == 'form_number' && $order_type == 'asc') 'desc' @elseif($order_by == 'form_number' && $order_type == 'desc') 'asc' @else 'desc' @endif)">Form Number <span class="pull-right"><i class="fa @if($order_by == 'form_number' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'form_number' && $order_type == 'desc') fa-sort-desc @else fa-sort-asc @endif fa-fw"></i></span></th>
                            <th>Broker</th>
                            <th>Shares</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Broker Fee</th>
                            <th>Owner</th>
                            <th>Group</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($list_shares_buy as $shares_buy)
                        <tr id="list-{{$shares_buy->formulir_id}}">
                            <td>{{ \DateHelper::formatView($shares_buy->formulir->form_date) }}</td>
                            <td><a href="{{ url('facility/bumi-shares/buy/'.$shares_buy->id) }}">{{ $shares_buy->formulir->form_number}}</a></td>
                            <td><a href="{{ url('facility/bumi-shares/broker/'.$shares_buy->saham_broker_id) }}">{{ $shares_buy->broker->name}}</a></td>
                            <td><a href="{{ url('facility/bumi-shares/shares/'.$shares_buy->saham_shares_id) }}">{{ $shares_buy->shares->name}}</a></td>
                            <td>{{ number_format_quantity($shares_buy->quantity) }}</td>
                            <td>{{ \NumberHelper::formatAccounting($shares_buy->price) }}</td>
                            <td>{{ number_format_quantity($shares_buy->fee) }}</td>
                            <td><a href="{{ url('facility/bumi-shares/owner/'.$shares_buy->owner_id) }}">{{ $shares_buy->owner->name}}</a></td>
                            <td><a href="{{ url('facility/bumi-shares/owner-group/'.$shares_buy->owner_group_id) }}">{{ $shares_buy->ownerGroup->name}}</a></td>
                            <td>
                                @include('framework::app.include._form_status_label', ['form_status' => $shares_buy->formulir->form_status])
                                @include('framework::app.include._approval_status_label', [
                                    'approval_status' => $shares_buy->formulir->approval_status,
                                    'approval_message' => $shares_buy->formulir->approval_message,
                                    'approval_at' => $shares_buy->formulir->approval_at,
                                    'approval_to' => $shares_buy->formulir->approvalTo->name,
                                ])
                            </td>
                        </tr>
                        @endforeach  
                    </tbody> 
                </table>
                {!! $list_shares_buy->appends(['order_by'=>app('request')->get('order_by'), 'order_type'=>app('request')->get('order_type'), 'status'=>app('request')->get('status'), 'search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
            </div>

            @endif

        </div>
    </div>  
</div>
@stop

@section('scripts')
<script>
function selectData(order_by, order_type) {
    var status = $("#status option:selected").val();
    var date_from = $("#date-from").val();
    var date_to = $("#date-to").val();
    var search = $("#search").val();
    var url = '{{url()}}/facility/bumi-shares/buy?order_by='+order_by+'&order_type='+order_type+'&status='+status+'&date_from='+date_from+'&date_to='+date_to+'&search='+search;
    location.href = url;
}
</script>
@stop
