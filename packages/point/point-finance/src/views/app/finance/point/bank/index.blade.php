@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
         <li><a href="{{url('finance')}}">Finance</a></li>
         <li>Bank</li>
    </ul>

    <h2 class="sub-header">Bank | Payment</h2>
    @include('point-finance::app.finance.point.bank._menu')

    <div class="panel panel-default">
        <div class="panel-body">
            <form action="{{ url('finance/point/bank') }}" method="get" class="form-horizontal">
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
                            <input type="text" name="date_from" id="date-from" class="form-control date input-datepicker" placeholder="From"  value="{{\Input::get('date_from') ? \Input::get('date_from') : ''}}">
                            <span class="input-group-addon"><i class="fa fa-chevron-right"></i></span>
                            <input type="text" name="date_to" id="date-to" class="form-control date input-datepicker" placeholder="To" value="{{\Input::get('date_to') ? \Input::get('date_to') : ''}}">
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <input type="text" name="search" id="search" class="form-control" placeholder="Search" value="{{\Input::get('search')}}" value="{{\Input::get('search') ? \Input::get('search') : ''}}">
                    </div>
                    <div class="col-sm-1">
                        <input type="hidden" name="order_by" id="order-by" value="{{\Input::get('order_by') ? \Input::get('order_by') : 'form_date'}}">
                        <input type="hidden" name="order_type" id="order-type" value="{{\Input::get('order_type') ? \Input::get('order_type') : 'desc'}}">
                        <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search</button>
                    </div>
                </div>
            </form>

            <br/>

            @if($list_bank->count() > 0)
            <?php 
                $order_by = \Input::get('order_by') ? : 0;
                $order_type = \Input::get('order_type') ? : 0;
            ?>
            <div class="table-responsive">
                {!! $list_bank->appends(['order_by'=>app('request')->get('order_by'), 'order_type'=>app('request')->get('order_type'), 'status'=>app('request')->get('status'), 'search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>&nbsp;</th>    
                            <th style="cursor:pointer" onclick="selectData('form_date', @if($order_by == 'form_date' && $order_type == 'asc') 'desc' @elseif($order_by == 'form_date' && $order_type == 'desc') 'asc' @else 'desc' @endif)">Date <span class="pull-right"><i class="fa @if($order_by == 'form_date' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'form_date' && $order_type == 'desc') fa-sort-desc @else fa-sort-asc @endif fa-fw"></i></span></th>
                            <th style="cursor:pointer" onclick="selectData('form_number', @if($order_by == 'form_number' && $order_type == 'asc') 'desc' @elseif($order_by == 'form_number' && $order_type == 'desc') 'asc' @else 'desc' @endif)">Form Number <span class="pull-right"><i class="fa @if($order_by == 'form_number' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'form_number' && $order_type == 'desc') fa-sort-desc @else fa-sort-asc @endif fa-fw"></i></span></th>
                            <th style="cursor:pointer" onclick="selectData('person.name', @if($order_by == 'person.name' && $order_type == 'asc') 'desc' @elseif($order_by == 'person.name' && $order_type == 'desc') 'asc' @else 'desc' @endif)">Person <span class="pull-right"><i class="fa @if($order_by == 'person.name' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'person.name' && $order_type == 'desc') fa-sort-desc @else fa-sort-asc @endif fa-fw"></i></span></th>
                            <th>Notes</th>
                            <th class="text-right">Received</th>
                            <th class="text-right">Disbursed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($list_bank as $bank)
                            <tr @if($bank->formulir->form_status == -1) style="background:red;color:white" @endif>
                                <td>
                                    <a href="#" onclick="pagePrint('/finance/point/bank/print/{{$bank->id}}');" data-toggle="tooltip" title="Print" class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-print"></i> Print</a>
                                </td>
                                <td>{{ date_format_view($bank->formulir->form_date) }}</td>
                                <td>
                                    <a href="{{ url('finance/point/bank/'. $bank->payment_flow .'/'.$bank->id) }}" @if($bank->formulir->form_status == -1) style="background:red;color:white !important" @endif>{{ $bank->formulir->form_number}}</a>
                                </td>
                                <td>{!! get_url_person($bank->person->id) !!}</td>
                                <td>{{ $bank->formulir->notes }}</td>
                                <td colspan="2" class="text-center">
                                    @if($bank->formulir->form_status == -1)
                                        <label class="label label-danger">Canceled</label>
                                    @endif
                                </td>
                            </tr>
                            @foreach($bank->detail as $bank_detail)
                                <tr @if($bank->formulir->form_status == -1) style="background:red;color:white" @endif>
                                    <td></td>
                                    <td colspan="3">[{{ $bank_detail->coa->account }}] {{ $bank_detail->notes_detail }}</td>
                                    <td style="text-align: right" colspan="2">
                                        @if($bank->payment_flow == 'in')
                                        {{ number_format_price($bank_detail->amount) }}
                                        @endif
                                    </td>
                                    <td style="text-align: right" colspan="2">
                                        @if($bank->payment_flow == 'out')
                                        {{ number_format_price($bank_detail->amount) }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            <tr @if($bank->formulir->form_status == -1) style="background:red;color:white" @endif>
                                <td colspan="4"></td>
                                <td style="text-align: right" colspan="2">
                                    @if($bank->payment_flow == 'in')
                                    <b>{{ number_format_price($bank->total) }}</b>
                                    @endif
                                </td>
                                <td style="text-align: right" colspan="2">
                                    @if($bank->payment_flow == 'out')
                                    <b>{{ number_format_price(abs($bank->total)) }}</b>
                                    @endif
                                </td>
                            </tr>
                        @endforeach  
                    </tbody> 
                </table>
                {!! $list_bank->appends(['order_by'=>app('request')->get('order_by'), 'order_type'=>app('request')->get('order_type'), 'status'=>app('request')->get('status'), 'search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
            </div>
            @endif
        </div>
    </div>  
</div>
@stop

@section('scripts')
<script>
    function pagePrint(url){
        var printWindow = window.open( url, 'Print', 'left=75, top=0, width=1200, height=1000, toolbar=0, resizable=0');
        printWindow.addEventListener('load', function(){
            printWindow.print();
            
        }, true);
    }

    function selectData(order_by, order_type) {
        var status = $("#status option:selected").val();
        var date_from = $("#date-from").val();
        var date_to = $("#date-to").val();
        var search = $("#search").val();
        var url = '{{url()}}/finance/point/bank/?order_by='+order_by+'&order_type='+order_type+'&status='+status+'&date_from='+date_from+'&date_to='+date_to+'&search='+search;
        location.href = url;
    }
</script>
@stop
