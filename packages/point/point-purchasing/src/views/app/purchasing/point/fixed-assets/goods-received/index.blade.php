@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.fixed-assets._breadcrumb')
            <li>Goods Received</li>
        </ul>
        <h2 class="sub-header">Goods Received | Fixed Assets</h2>
        @include('point-purchasing::app.purchasing.point.fixed-assets.goods-received._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{ url('purchasing/point/fixed-assets/goods-received') }}" method="get" class="form-horizontal">
                    <div class="form-group">
                        <div class="col-sm-3">
                            <select class="selectize" name="status" id="status" onchange="selectData()">
                                <option value="0" @if(\Input::get('status') == 0) selected @endif>open</option>
                                <option value="1" @if(\Input::get('status') == 1) selected @endif>closed</option>
                                <option value="-1" @if(\Input::get('status') == -1) selected @endif>canceled</option>
                            </select>
                        </div>
                        <div class="col-sm-3">
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
                        <div class="col-sm-3">
                            <input type="text" name="search" id="search" class="form-control" placeholder="Search..."
                                   value="{{\Input::get('search')}}"
                                   value="{{\Input::get('search') ? \Input::get('search') : ''}}">
                        </div>
                        <div class="col-sm-1">
                            <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i
                                        class="fa fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </form>

                <br/>

                <div class="table-responsive">
                    {!! $list_goods_received->appends(['status'=>app('request')->get('status'), 'search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th>Form Date</th>
                            <th>Form Number</th>
                            <th>Warehouse</th>
                            <th>Supplier</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($list_goods_received as $goods_received)
                            <tr id="list-{{$goods_received->formulir_id}}">
                                <td>{{ date_format_view($goods_received->formulir->form_date) }}</td>
                                <td>
                                    <a href="{{ url('purchasing/point/fixed-assets/goods-received/'.$goods_received->id) }}">{{ $goods_received->formulir->form_number}}</a>
                                </td>
                                <td>
                                    <a href="{{ url('master/warehouse/'.$goods_received->warehouse_id) }}">{{ $goods_received->warehouse->codeName}}</a>
                                </td>
                                <td>
                                    <a href="{{ url('master/contact/supplier/'.FormulirHelper::getLockedModel($goods_received->formulir_id)->supplier->id) }}">{{ FormulirHelper::getLockedModel($goods_received->formulir_id)->supplier->codeName}}</a>
                                </td>
                                <td>
                                    @include('framework::app.include._form_status_label', ['form_status' => $goods_received->formulir->form_status])
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $list_goods_received->appends(['status'=>app('request')->get('status'), 'search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
<script>
function selectData() {
    var status = $("#status option:selected").val();
    var date_from = $("#date-from").val();
    var date_to = $("#date-to").val();
    var search = $("#search").val();
    var url = '{{url()}}/purchasing/point/fixed-assets/goods-received/?status='+status+'&date_from='+date_from+'&date_to='+date_to+'&search='+search;
    location.href = url;
}
</script>
@stop
