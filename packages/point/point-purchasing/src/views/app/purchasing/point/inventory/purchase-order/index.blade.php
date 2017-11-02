@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li>Purchase order</li>
        </ul>
        <h2 class="sub-header">Purchase Order</h2>
        @include('point-purchasing::app.purchasing.point.inventory.purchase-order._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{ url('purchasing/point/purchase-order') }}" method="get" class="form-horizontal">
                    <div class="form-group">
                        <div class="col-sm-4">
                            <select class="selectize" name="status" id="status" onchange="selectData('form_date', 'desc')">
                                <option value="0" @if(\Input::get('status') == 0) selected @endif>open</option>
                                <option value="1" @if(\Input::get('status') == 1) selected @endif>closed</option>
                                <option value="-1" @if(\Input::get('status') == -1) selected @endif>canceled</option>
                                <option value="all" @if(\Input::get('status') == 'all') selected @endif>all</option>
                            </select>
                        </div>
                        
                        <div class="col-sm-4">
                            <div class="input-group input-daterange" data-date-format="{{date_format_masking()}}">
                                <input type="text" name="date_from" id="date-from" class="form-control date input-datepicker"
                                       placeholder="From"
                                       value="{{\Input::get('date_from') ? \Input::get('date_from') : ''}}">
                                <span class="input-group-addon"><i class="fa fa-chevron-right"></i></span>
                                <input type="text" name="date_to" id="date-to" class="form-control date input-datepicker"
                                       placeholder="To"
                                       value="{{\Input::get('date_to') ? \Input::get('date_to') : ''}}">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <input type="text" name="search" id="search" class="form-control" placeholder="Search..."
                                   value="{{\Input::get('search')}}"
                                   value="{{\Input::get('search') ? \Input::get('search') : ''}}">
                        </div>
                        <div class="col-sm-12">
                            <input type="hidden" name="order_by" value="{{\Input::get('order_by') ? \Input::get('order_by') : 'form_date'}}">
                            <input type="hidden" name="order_type" value="{{\Input::get('order_type') ? \Input::get('order_type') : 'desc'}}">
                            <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search
                            </button>
                            @if(auth()->user()->may('read.point.purchasing.order'))
                                <a class="btn btn-effect-ripple btn-effect-ripple btn-info button-export" id="btn-pdf" href="{{url('purchasing/point/purchase-order/pdf?date_from='.\Input::get('date_from').'&date_to='.\Input::get('date_to').'&search='.\Input::get('search').'&order_by='.\Input::get('order_by').'&order_type='.\Input::get('order_type').'&status='.\Input::get('status'))}}"> export to PDF</a>
                            @endif

                                <a class="btn btn-success" onclick="showAll();">Show All</a>
                                <input type="hidden" id="check_show" >
                        </div>
                    </div>
                </form>

                <br/>

                <div class="table-responsive">
                    <?php 
                        $order_by = \Input::get('order_by') ? : 0;
                        $order_type = \Input::get('order_type') ? : 0;
                    ?>
                    
                    {!! $list_purchase_order->appends(['order_by'=>app('request')->get('order_by'), 'order_type'=>app('request')->get('order_type'), 'status'=>app('request')->get('status'), 'search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr class="th-head">
                            <th style="width: 180px"></th>
                            <th style="cursor:pointer" onclick="selectData('form_date', @if($order_by == 'form_date' && $order_type == 'asc') 'desc' @elseif($order_by == 'form_date' && $order_type == 'desc') 'asc' @else 'desc' @endif)">Form Date <span class="pull-right"><i class="fa @if($order_by == 'form_date' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'form_date' && $order_type == 'desc') fa-sort-desc @else fa-sort-asc @endif fa-fw"></i></span></th>
                            <th style="cursor:pointer" onclick="selectData('form_number', @if($order_by == 'form_number' && $order_type == 'asc') 'desc' @elseif($order_by == 'form_number' && $order_type == 'desc') 'asc' @else 'desc' @endif)">Form Number <span class="pull-right"><i class="fa @if($order_by == 'form_number' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'form_number' && $order_type == 'desc') fa-sort-desc @else fa-sort-asc @endif fa-fw"></i></span></th>
                            <th style="cursor:pointer" onclick="selectData('person.name', @if($order_by == 'person.name' && $order_type == 'asc') 'desc' @elseif($order_by == 'person.name' && $order_type == 'desc') 'asc' @else 'desc' @endif)">Supplier <span class="pull-right"><i class="fa @if($order_by == 'person.name' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'person.name' && $order_type == 'desc') fa-sort-desc @else fa-sort-asc @endif fa-fw"></i></span></th>
                            <th>Grand Total</th>
                            <th>Total Remaining Downpayment</th>
                            <th>Total Downpayment</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        
                        @foreach($list_purchase_order as $purchase_order)
                        <tr class="row-detail" id="row_detail_{{$purchase_order->id}}">
                            <?php array_push($array_purchase_order_id,$purchase_order->id); ?>
                            <td>
                                @if($purchase_order->formulir->approval_status == '1' && $purchase_order->formulir->form_status == 0 && auth()->user()->may('create.point.purchasing.downpayment') && $purchase_order->is_cash == 1)
                                    {{ $purchase_order->checkDownpayment() }}
                                @endif
                            </td>
                            <td>{{ date_format_view($purchase_order->formulir->form_date) }}</td>
                            <td>
                                <a href="{{ url('purchasing/point/purchase-order/'.$purchase_order->id) }}">{{ $purchase_order->formulir->form_number}}</a>
                            </td>
                            <td>
                                {!! get_url_person($purchase_order->supplier_id) !!}
                            </td>
                            <td>{{number_format_price($purchase_order->total)}}
                                <i class='text-info' style='font-size:12px'> [{{ $purchase_order->is_cash == 1 ? 'Cash' : 'Credit' }}]</i> <br>
                            </td>
                            <td>{{ number_format_price($purchase_order->getTotalRemainingDownpayment(($purchase_order->id))) }}</td>
                            <td>{{ number_format_price($purchase_order->getTotalDownpayment(($purchase_order->id))) }}</td>
                            <td>
                                @include('framework::app.include._approval_status_label', ['approval_status' => $purchase_order->formulir->approval_status])
                                @include('framework::app.include._form_status_label', ['form_status' => $purchase_order->formulir->form_status])
                            </td>
                        </tr>
                        @endforeach
                        <input type="hidden" id="array_purchase_order_id" value="{{ implode('#',$array_purchase_order_id) }}">
                        </tbody>
                    </table>
                    {!! $list_purchase_order->appends(['order_by'=>app('request')->get('order_by'), 'order_type'=>app('request')->get('order_type'), 'status'=>app('request')->get('status'), 'search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop
@section('scripts')
<script>
$('#check_show').val(0);
function showAll(){
    $('.btn-success').attr('onclick','compact()');
    $('.btn-success').text('Compact');
    $('.header_detail').remove();
    var html = '<th class="header_detail">ITEM</th>'
                +'<th class="header_detail">QTY</th>'
                +'<th class="header_detail">PRICE</th>'
    $('.th-head').append(html);
    $('.txt-detail').remove();
    $('.row-detail').append('<td class="txt-detail extend_column_detail" colspan="3" align="center"><strong>DETAIL</strong></td>');
    var check_show = $('#check_show').val();
    var array_purchase_order_id = $('#array_purchase_order_id').val();
    if(check_show == 0){
        var temp = array_purchase_order_id.split('#');
        for (var x = temp.length - 1; x >= 0; x--) {
            var str_url = "{{ url('purchasing/point/purchase-order/detail/') }}/"+temp[x];
            $.ajax({ url:str_url, success: function(data) {
                for (var i = 0; i < data.length; i++) {
                    var extend_table_row = ' <tr class="extend_column_detail">'
                            +'      <td colspan="8" class="extend_column_detail"></td>'
                            +'      <td class="extend_column_detail">'+data[i].item_name+'</td>'
                            +'      <td class="extend_column_detail">'+data[i].quantity+'</td>'
                            +'      <td class="extend_column_detail">'+data[i].price+'</td>'
                            +'  </tr>';

                    $('#row_detail_'+data[i].point_purchasing_order_id).after(extend_table_row);
                
                $('#check_show').val(1);
                }
            }});
        }
    }else{
        $('.extend_column_detail').show();
    }
}
function compact(){
    $('.btn-success').attr('onclick','showAll()');
    $('.btn-success').text('Show All');

    $('.header_detail').remove();
    $('.extend_column_detail').hide();

}
function selectData(order_by, order_type) {
    var status = $("#status option:selected").val();
    var date_from = $("#date-from").val();
    var date_to = $("#date-to").val();
    var search = $("#search").val();
    var url = '{{url()}}/purchasing/point/purchase-order/?order_by='+order_by+'&order_type='+order_type+'&status='+status+'&date_from='+date_from+'&date_to='+date_to+'&search='+search;
    location.href = url;
}
</script>
@stop
