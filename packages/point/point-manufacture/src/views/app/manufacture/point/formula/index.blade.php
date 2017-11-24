@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            <li><a href="{{ url('manufacture') }}">Manufacture</a></li>
            <li>Formula</li>
        </ul>
        <h2 class="sub-header">Manufacture | Formula</h2>
        @include('point-manufacture::app.manufacture.point.formula._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{ url('manufacture/point/formula') }}" method="get" class="form-horizontal">
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
                            <div class="input-group input-daterange"
                                 data-date-format="{{\DateHelper::formatMasking()}}">
                                <input type="text" name="date_from" id="date-from" class="form-control date input-datepicker"
                                       placeholder="From"
                                       value="{{\Input::get('date_from') ? \Input::get('date_from') : ''}}">
                                <span class="input-group-addon"><i class="fa fa-chevron-right"></i></span>
                                <input type="text" name="date_to" id="date-to" class="form-control date input-datepicker"
                                       placeholder="To"
                                       value="{{\Input::get('date_to') ? \Input::get('date_to') : ''}}">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <input type="text" name="search" id="search" class="form-control" placeholder="Search..."
                                   value="{{\Input::get('search')}}"
                                   value="{{\Input::get('search') ? \Input::get('search') : ''}}">
                        </div>
                        <div class="col-sm-1">
                            <input type="hidden" name="order_by" value="{{\Input::get('order_by') ? \Input::get('order_by') : 'form_date'}}">
                            <input type="hidden" name="order_type" value="{{\Input::get('order_type') ? \Input::get('order_type') : 'desc'}}">
                            <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </form>

                <br/>

                <div class="table-responsive">
                    <?php 
                        $order_by = \Input::get('order_by') ? : 0;
                        $order_type = \Input::get('order_type') ? : 0;
                    ?>
                    {!! $list_formula->appends(['order_by'=>app('request')->get('order_by'), 'order_type'=>app('request')->get('order_type'), 'search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th style="cursor:pointer" onclick="selectData('form_date', @if($order_by == 'form_date' && $order_type == 'asc') 'desc' @elseif($order_by == 'form_date' && $order_type == 'desc') 'asc' @else 'desc' @endif)">Date <span class="pull-right"><i class="fa @if($order_by == 'form_date' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'form_date' && $order_type == 'desc') fa-sort-desc @else fa-sort-asc @endif fa-fw"></i></span></th>
                            <th style="cursor:pointer" onclick="selectData('form_number', @if($order_by == 'form_number' && $order_type == 'asc') 'desc' @elseif($order_by == 'form_number' && $order_type == 'desc') 'asc' @else 'desc' @endif)">Form Number <span class="pull-right"><i class="fa @if($order_by == 'form_number' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'form_number' && $order_type == 'desc') fa-sort-desc @else fa-sort-asc @endif fa-fw"></i></span></th>
                            <th style="cursor:pointer" onclick="selectData('point_manufacture_formula.name', @if($order_by == 'point_manufacture_formula.name' && $order_type == 'asc') 'desc' @elseif($order_by == 'point_manufacture_formula.name' && $order_type == 'desc') 'asc' @else 'desc' @endif)">Formula <span class="pull-right"><i class="fa @if($order_by == 'point_manufacture_formula.name' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'point_manufacture_formula.name' && $order_type == 'desc') fa-sort-desc @else fa-sort-asc @endif fa-fw"></i></span></th>
                            <th>finished goods</th>
                            <th>raw material</th>
                            <th>status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($list_formula as $formula)
                            <tr id="list-{{$formula->formulir_id}}">
                                <td>{{ \DateHelper::formatView($formula->formulir->form_date) }}</td>
                                <td>
                                    <a href="{{ url('manufacture/point/formula/'.$formula->id) }}">{{ $formula->formulir->form_number}}</a>
                                </td>
                                <td>{{$formula->name}}</td>
                                <td>
                                    <ol>
                                        @foreach($formula->product as $product)
                                            <li>
                                                <a href="{{url('master/item/'.$product->product_id)}}">{{$product->item->codeName}}</a>
                                                = {{\NumberHelper::formatQuantity($product->quantity)}} {{$product->unit}}
                                            </li>
                                        @endforeach
                                    </ol>
                                </td>
                                <td>
                                    <ol>
                                        @foreach($formula->material as $list_material)
                                            <li>
                                                <a href="{{url('master/item/'.$list_material->material_id)}}">{{$list_material->item->codeName}}</a>
                                                = {{\NumberHelper::formatQuantity($list_material->quantity)}} {{$list_material->unit}}
                                            </li>
                                        @endforeach
                                    </ol>
                                </td>
                                <td>
                                    @include('framework::app.include._approval_status_label', ['approval_status' => $formula->formulir->approval_status])
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $list_formula->appends(['order_by'=>app('request')->get('order_by'), 'order_type'=>app('request')->get('order_type'), 'search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
                </div>
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
    var url = '{{url()}}/manufacture/point/formula/?order_by='+order_by+'&order_type='+order_type+'&status='+status+'&date_from='+date_from+'&date_to='+date_to+'&search='+search;
    location.href = url;
}
</script>
@stop
