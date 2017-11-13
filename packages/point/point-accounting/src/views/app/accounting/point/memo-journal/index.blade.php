@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-accounting::app/accounting/point/memo-journal/_breadcrumb')
        <li>Memo Journal</li>
    </ul>
    <h2 class="sub-header">Memo Journal</h2>
    @include('point-accounting::app.accounting.point.memo-journal._menu')

    <div class="panel panel-default">
        <div class="panel-body">
            <form action="{{ url('accounting/point/memo-journal') }}" method="get" class="form-horizontal">
                <div class="form-group">
                    <div class="col-sm-3">
                        <select class="selectize" name="status" id="status" onchange="selectData('form_date', 'desc')">
                            <option value="0" @if(\Input::get('status') == 0) selected @endif>open</option>
                            <option value="1" @if(\Input::get('status') == 1) selected @endif>closed</option>
                            <option value="-1" @if(\Input::get('status') == -1) selected @endif>canceled</option>
                            <option value="all" @if(\Input::get('status') == 'all') selected @endif>all</option>
                        </select>
                    </div>
                    <div class="col-sm-3">
                        <div class="input-group input-daterange" data-date-format="{{date_format_masking()}}">
                            <input type="text" name="date_from" id="date-from" class="form-control date input-datepicker" placeholder="From"  value="{{\Input::get('date_from') ? \Input::get('date_from') : ''}}">
                            <span class="input-group-addon"><i class="fa fa-chevron-right"></i></span>
                            <input type="text" name="date_to" id="date-to" class="form-control date input-datepicker" placeholder="To" value="{{\Input::get('date_to') ? \Input::get('date_to') : ''}}">
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <input type="text" name="search" id="search" class="form-control" placeholder="Search..." value="{{\Input::get('search')}}" value="{{\Input::get('search') ? \Input::get('search') : ''}}">
                    </div>
                    <div class="col-sm-3">
                        <input type="hidden" name="order_by" value="{{\Input::get('order_by') ? \Input::get('order_by') : 'form_date'}}">
                        <input type="hidden" name="order_type" value="{{\Input::get('order_type') ? \Input::get('order_type') : 'desc'}}">
                        <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> search</button>
                        <a class="btn btn-success" id="full_view" onclick="showAll();">Show All</a>
                    </div>
                </div>
            </form>

            <br/>

            <div class="table-responsive">
                <?php 
                    $order_by = \Input::get('order_by') ? : 0;
                    $order_type = \Input::get('order_type') ? : 0;
                ?>
                {!! $list_memo_journal->appends(['order_by'=>app('request')->get('order_by'), 'order_type'=>app('request')->get('order_type'), 'status'=>app('request')->get('status'), 'search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th style="cursor:pointer" onclick="selectData('form_date', @if($order_by == 'form_date' && $order_type == 'asc') 'desc' @elseif($order_by == 'form_date' && $order_type == 'desc') 'asc' @else 'desc' @endif)">Date <span class="pull-right"><i class="fa @if($order_by == 'form_date' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'form_date' && $order_type == 'desc') fa-sort-desc @else fa-sort-asc @endif fa-fw"></i></span></th>
                            <th style="cursor:pointer" onclick="selectData('form_number', @if($order_by == 'form_number' && $order_type == 'asc') 'desc' @elseif($order_by == 'form_number' && $order_type == 'desc') 'asc' @else 'desc' @endif)">Form Number <span class="pull-right"><i class="fa @if($order_by == 'form_number' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'form_number' && $order_type == 'desc') fa-sort-desc @else fa-sort-asc @endif fa-fw"></i></span></th>
                            <th colspan="6" class="th-detail">DETAIL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($list_memo_journal as $memo_journal)
                        <tr id="list-{{$memo_journal->id}}">
                            <td>{{ date_format_view($memo_journal->formulir->form_date) }}</td>
                            <td><a href="{{url('accounting/point/memo-journal/'.$memo_journal->id)}}">{{$memo_journal->formulir->form_number}}</a></td>
                            <td class="td-detail">COA</td>
                            <td class="td-detail">MASTER REF</td>
                            <td class="td-detail">FORM REF</td>
                            <td class="td-detail">DESCRIPTION</td>
                            <td class="td-detail">DEBIT</td>
                            <td class="td-detail">Credit</td>
                        </tr>
                            @foreach($memo_journal->detail as $detail)
                            <tr class="tr-detail">
                                <td colspan="2"></td>
                                <td class="text-left">{{ $detail->coa->name}}</td>
                                <td class="text-left">{{ $detail->subledger_type ? $detail->subledger_type::find($detail->subledger_id)->name : '-'}}</td>
                                <td class="text-left">{{ $detail->form_reference_id ? $detail->reference->form_number : '-'}}</td>
                                <td class="text-left">{{ $detail->description}}</td>
                                <td class="text-right">{{ number_format_accounting($detail->debit) }}</td>
                                <td class="text-right">{{ number_format_accounting($detail->credit) }}</td>
                            </tr>
                            @endforeach

                        @endforeach  
                    </tbody> 
                </table>
                {!! $list_memo_journal->appends(['order_by'=>app('request')->get('order_by'), 'order_type'=>app('request')->get('order_type'), 'status'=>app('request')->get('status'), 'search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
            </div>
        </div>
    </div>  
</div>
@stop

@section('scripts')
<script>
function showAll(){
    $('.th-detail').show();
    $('.td-detail').show();
    $('.tr-detail').show();
    $('#full_view').attr('onclick','compact()');
    $('#full_view').text('Compact');
}
compact();
function compact(){
    $('.th-detail').hide();   
    $('.td-detail').hide();   
    $('.tr-detail').hide();   
    $('#full_view').attr('onclick','showAll()');
    $('#full_view').text('Show All');
}
function selectData(order_by, order_type) {
    var status = $("#status option:selected").val();
    var date_from = $("#date-from").val();
    var date_to = $("#date-to").val();
    var search = $("#search").val();
    var url = '{{url()}}/accounting/point/memo-journal/?order_by='+order_by+'&order_type='+order_type+'&status='+status+'&date_from='+date_from+'&date_to='+date_to+'&search='+search;
    location.href = url;
}
</script>
@stop
