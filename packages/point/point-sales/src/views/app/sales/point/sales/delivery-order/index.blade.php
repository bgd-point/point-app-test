@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-sales::app.sales.point.sales._breadcrumb')
            <li>Delivery Order</li>
        </ul>
        <h2 class="sub-header">Delivery Order</h2>
        @include('point-sales::app.sales.point.sales.delivery-order._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{ url('sales/point/indirect/delivery-order') }}" method="get" class="form-horizontal">
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
                            @if(auth()->user()->may('read.point.sales.delivery.order'))
                                <a class="btn btn-effect-ripple btn-effect-ripple btn-info button-export" id="btn-pdf" href="{{url('sales/point/indirect/delivery-order/pdf?date_from='.\Input::get('date_from').'&date_to='.\Input::get('date_to').'&search='.\Input::get('search').'&order_by='.\Input::get('order_by').'&order_type='.\Input::get('order_type').'&status='.\Input::get('status'))}}"> export to PDF</a>
                            @endif
                            <a class="btn btn-success" id="full_view" onclick="showAll();">Show All</a>
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
                    {!! $list_delivery_order->appends(['order_by'=>app('request')->get('order_by'), 'order_type'=>app('request')->get('order_type'), 'status'=>app('request')->get('status'), 'search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr class="th-head">
                            <th style="cursor:pointer" onclick="selectData('form_date', @if($order_by == 'form_date' && $order_type == 'asc') 'desc' @elseif($order_by == 'form_date' && $order_type == 'desc') 'asc' @else 'desc' @endif)">Date <span class="pull-right"><i class="fa @if($order_by == 'form_date' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'form_date' && $order_type == 'desc') fa-sort-desc @else fa-sort-asc @endif fa-fw"></i></span></th>
                            <th style="cursor:pointer" onclick="selectData('form_number', @if($order_by == 'form_number' && $order_type == 'asc') 'desc' @elseif($order_by == 'form_number' && $order_type == 'desc') 'asc' @else 'desc' @endif)">Form Number <span class="pull-right"><i class="fa @if($order_by == 'form_number' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'form_number' && $order_type == 'desc') fa-sort-desc @else fa-sort-asc @endif fa-fw"></i></span></th>
                            <th style="cursor:pointer" onclick="selectData('person.name', @if($order_by == 'person.name' && $order_type == 'asc') 'desc' @elseif($order_by == 'person.name' && $order_type == 'desc') 'asc' @else 'desc' @endif)">Customer <span class="pull-right"><i class="fa @if($order_by == 'person.name' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'person.name' && $order_type == 'desc') fa-sort-desc @else fa-sort-asc @endif fa-fw"></i></span></th>
                            <th>Warehouse</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($list_delivery_order as $delivery_order)
                            <tr class="row-detail" id="row_detail_{{$delivery_order->id}}">
                            <?php array_push($array_delivery_order_id,$delivery_order->id); ?>
                                <td>{{ date_format_view($delivery_order->formulir->form_date) }}</td>
                                <td>
                                    <a href="{{ url('sales/point/indirect/delivery-order/'.$delivery_order->id) }}">{{ $delivery_order->formulir->form_number}}</a>
                                </td>
                                <td>
                                    {!! get_url_person($delivery_order->person->id) !!}
                                </td>
                                <td>
                                    <a href="{{ url('master/warehouse/'.$delivery_order->warehouse_id) }}">{{ $delivery_order->warehouse->codeName}}</a>
                                </td>
                                <td>
                                    @include('framework::app.include._approval_status_label', ['approval_status' => $delivery_order->formulir->approval_status])
                                    @include('framework::app.include._form_status_label', ['form_status' => $delivery_order->formulir->form_status])
                                </td>
                            </tr>
                        @endforeach
                        <input type="hidden" id="array_delivery_order_id" value="{{ implode('#',$array_delivery_order_id) }}">
                        </tbody>
                    </table>
                    {!! $list_delivery_order->appends(['order_by'=>app('request')->get('order_by'), 'order_type'=>app('request')->get('order_type'), 'status'=>app('request')->get('status'), 'search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
<script>
$('#check_show').val(0);
function showAll(){
    $('#full_view').attr('onclick','compact()');
    $('#full_view').text('Compact');
    $('.header_detail').remove();
    var html = '<th class="header_detail">ITEM</th>'
                +'<th class="header_detail">QTY</th>'
                +'<th class="header_detail">PRICE</th>'
    $('.th-head').append(html);
    $('.txt-detail').remove();
    $('.row-detail').append('<td class="txt-detail extend_column_detail" colspan="3" align="center"><strong>DETAIL</strong></td>');
    var check_show = $('#check_show').val();
    var array_delivery_order_id = $('#array_delivery_order_id').val();
    if(check_show == 0){
        var temp = array_delivery_order_id.split('#');
        for (var x = temp.length - 1; x >= 0; x--) {
            var str_url = "{{ url('sales/point/indirect/delivery-order/detail/') }}/"+temp[x];
            $.ajax({ url:str_url, success: function(data) {
                for (var i = 0; i < data.length; i++) {
                    var extend_table_row = ' <tr class="extend_column_detail">'
                            +'      <td colspan="5" class="extend_column_detail"></td>'
                            +'      <td class="extend_column_detail">'+data[i].item_name+'</td>'
                            +'      <td class="extend_column_detail">'+data[i].quantity+' '+data[i].unit+'</td>'
                            +'      <td class="extend_column_detail">'+data[i].price+'</td>'
                            +'  </tr>';

                    $('#row_detail_'+data[i].point_sales_delivery_order_id).after(extend_table_row);
                
                $('#check_show').val(1);
                }
            }});
        }
    }else{
        $('.extend_column_detail').show();
    }
}
function compact(){
    $('#full_view').attr('onclick','showAll()');
    $('#full_view').text('Show All');

    $('.header_detail').remove();
    $('.extend_column_detail').hide();

}
function selectData(order_by, order_type) {
    var status = $("#status option:selected").val();
    var date_from = $("#date-from").val();
    var date_to = $("#date-to").val();
    var search = $("#search").val();
    var url = '{{url()}}/sales/point/indirect/delivery-order/?order_by='+order_by+'&order_type='+order_type+'&status='+status+'&date_from='+date_from+'&date_to='+date_to+'&search='+search;
    location.href = url;
}
</script>
@stop
