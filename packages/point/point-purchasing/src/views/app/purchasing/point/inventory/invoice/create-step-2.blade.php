@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li><a href="{{ url('purchasing/point/invoice') }}">Invoice</a></li>
            <li>Create step 2</li>
        </ul>
        <h2 class="sub-header">Invoice</h2>
        @include('point-purchasing::app.purchasing.point.inventory.invoice._menu')
        <input type="hidden" name="supplier_id" id="supplier-id" value="{{$supplier_id}}"/>
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="table-responsive">
                    {!! $list_goods_received->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th width="100px" class="text-center"></th>
                            <th>DATE</th>
                            <th>FORM NUMBER</th>
                            <th>SUPPLIER</th>
                            <th>WAREHOUSE</th>
                            <th>ITEMS</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $i=0; ?>
                        @foreach($list_goods_received as $goods_received)
                            <tr id="list-{{$goods_received->formulir_id}}">
                                <td class="text-center">
                                    <input type="checkbox" name="goods_received_id[]" id="goods-received-id-{{$i}}" value="{{$goods_received->formulir_id}}">
                                </td>
                                <td>{{ date_format_view($goods_received->formulir->form_date) }}</td>
                                <td>
                                    <a href="{{ url('purchasing/point/goods-received/'.$goods_received->id) }}">{{ $goods_received->formulir->form_number}}</a>
                                </td>
                                <td>
                                    {!! get_url_person($goods_received->supplier_id) !!}
                                </td>
                                <td>
                                    <a href="{{ url('master/warehouse/'.$goods_received->warehouse_id) }}">{{ $goods_received->warehouse->codeName}}</a>
                                </td>
                                <td>
                                    @foreach($goods_received->items as $goods_received_item)
                                        {{ $goods_received_item->item->codeName }}
                                        = {{ number_format_quantity($goods_received_item->quantity) }} {{ $goods_received_item->unit }}
                                        <br/>
                                    @endforeach
                                </td>
                            </tr>
                        <?php $i++; ?>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $list_goods_received->render() !!}
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <button onclick="next()" class="btn btn-effect-ripple btn-primary">Next</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
@section('scripts')
<script type="text/javascript">
    function next() {
        var goods_received_id = [];
        for (var i = 0; i < {{$i}}; i++) {
            if ($("#goods-received-id-"+i).is(":checked")) {    
                goods_received_id.push($("#goods-received-id-"+i).val());
            }
        };

        var supplier_id = $("#supplier-id").val();
        var url = '{{url()}}/purchasing/point/invoice/create-step-3/?supplier_id='+supplier_id+'&goods_received_id='+goods_received_id;
        location.href = url;
    }

</script>
@stop
