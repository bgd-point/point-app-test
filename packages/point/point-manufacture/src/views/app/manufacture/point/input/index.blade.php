@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            <li><a href="{{ url('manufacture') }}">Manufacture</a></li>
            <li><a href="{{ url('manufacture/point/process-io/'. $process->id) }}">Process</a></li>
            <li>Input</li>
        </ul>
        <h2 class="sub-header">{{$process->name}} | Input</h2>
        @include('point-manufacture::app.manufacture.point.input._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{ url('manufacture/point/process-io/'. $process->id .'/input') }}" method="get" class="form-horizontal">
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
                            <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> search
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
                    {!! $list_input->appends(['order_by'=>app('request')->get('order_by'), 'order_type'=>app('request')->get('order_type'), 'search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th style="cursor:pointer" onclick="selectData('form_date', @if($order_by == 'form_date' && $order_type == 'asc') 'desc' @elseif($order_by == 'form_date' && $order_type == 'desc') 'asc' @else 'desc' @endif)">Date <span class="pull-right"><i class="fa @if($order_by == 'form_date' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'form_date' && $order_type == 'desc') fa-sort-desc @else fa-sort-asc @endif fa-fw"></i></span></th>
                            <th style="cursor:pointer" onclick="selectData('form_number', @if($order_by == 'form_number' && $order_type == 'asc') 'desc' @elseif($order_by == 'form_number' && $order_type == 'desc') 'asc' @else 'desc' @endif)">Form Number <span class="pull-right"><i class="fa @if($order_by == 'form_number' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'form_number' && $order_type == 'desc') fa-sort-desc @else fa-sort-asc @endif fa-fw"></i></span></th>
                            <th style="cursor:pointer" onclick="selectData('point_manufacture_machine.name', @if($order_by == 'point_manufacture_machine.name' && $order_type == 'asc') 'desc' @elseif($order_by == 'point_manufacture_machine.name' && $order_type == 'desc') 'asc' @else 'desc' @endif)">Machine <span class="pull-right"><i class="fa @if($order_by == 'point_manufacture_machine.name' && $order_type == 'asc') fa-sort-asc @elseif($order_by == 'point_manufacture_machine.name' && $order_type == 'desc') fa-sort-desc @else fa-sort-asc @endif fa-fw"></i></span></th>
                            <th>notes</th>
                            <th>status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($list_input as $input)
                            <tr id="list-{{$input->formulir_id}}">
                                <td>{{ \DateHelper::formatView($input->formulir->form_date) }}</td>
                                <td>
                                    <a href="{{ url('manufacture/point/process-io/'. $input->process_id .'/input/'. $input->id) }}">{{ $input->formulir->form_number}}</a>
                                </td>
                                <td>{{$input->machine->name}}</td>
                                <td>{{$input->formulir->notes}}</td>
                                <td>
                                    @include('framework::app.include._approval_status_label', ['approval_status' => $input->formulir->approval_status])
                                    @include('framework::app.include._form_status_label', ['form_status' => $input->formulir->form_status])
                                </td>
                            </tr>
                            <tr>
                                <th></th>
                                <th></th>
                                <th>Finished Goods</th>
                                <th>Quantity produced</th>
                                <th>Warehouse</th>
                            </tr>
                            @foreach($input->product as $product)
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td>
                                        <a href="{{url('master/item/'.$product->product_id)}}">{{$product->item->codeName}}</a>
                                    </td>
                                    <td>{{\NumberHelper::formatQuantity($product->quantity)}} {{$product->unit}}</td>
                                    <td>{{$product->warehouse->name}}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <th></th>
                                <th></th>
                                <th>Raw Material</th>
                                <th>Quantity used</th>
                                <th>Warehouse</th>
                            </tr>
                            @foreach($input->material as $list_material)
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td>
                                        <a href="{{url('master/item/'.$list_material->material_id)}}">{{$list_material->item->codeName}}</a>
                                    </td>
                                    <td>{{\NumberHelper::formatQuantity($list_material->quantity)}} {{$list_material->unit}}</td>
                                    <td>{{$list_material->warehouse->name}}</td>
                                </tr>
                            @endforeach

                        @endforeach
                        </tbody>
                    </table>
                    {!! $list_input->appends(['order_by'=>app('request')->get('order_by'), 'order_type'=>app('request')->get('order_type'), 'search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
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
    var url = '{{url()}}/manufacture/point/process-io/{{$process->id}}/input?order_by='+order_by+'&order_type='+order_type+'&status='+status+'&date_from='+date_from+'&date_to='+date_to+'&search='+search;
    location.href = url;
}
</script>
@stop
