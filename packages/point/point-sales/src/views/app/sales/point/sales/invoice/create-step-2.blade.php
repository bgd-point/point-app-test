@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-sales::app.sales.point.sales._breadcrumb')
            <li><a href="{{ url('sales/point/indirect/invoice') }}">Invoice</a></li>
            <li>Create step 2</li>
        </ul>
        <h2 class="sub-header">Invoice</h2>
        @include('point-sales::app.sales.point.sales.invoice._menu')
        <input type="hidden" name="person_id" id="person-id" value="{{$person_id}}"/>
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="table-responsive">
                    {!! $list_delivery_order->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th width="100px" class="text-center"></th>
                            <th>Date</th>
                            <th>Delivered Number</th>
                            <th>Customer</th>
                            <th>Outgoing Warehouse</th>
                            <th>Item</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $i = 0;?>
                        @foreach($list_delivery_order as $delivery_order)

                            <tr id="list-{{$delivery_order->formulir_id}}">
                                <td class="text-center">
                                    <input type="checkbox" name="delivery_order_id[]" id="delivery-order-id-{{$i}}" value="{{$delivery_order->formulir_id}}">
                                </td>
                                <td>{{ date_format_view($delivery_order->formulir->form_date) }}</td>
                                <td>
                                    <a href="{{ url('sales/point/indirect/delivery-order/'.$delivery_order->id) }}">{{ $delivery_order->formulir->form_number}}</a>
                                </td>
                                <td>
                                    {!! get_url_person($delivery_order->person_id) !!}
                                </td>
                                <td>
                                    <a href="{{ url('master/warehouse/'.$delivery_order->warehouse_id) }}">{{ $delivery_order->warehouse->codeName}}</a>
                                </td>
                                <td>
                                    @foreach($delivery_order->items as $delivery_order_item)
                                        {{ $delivery_order_item->item->codeName }}
                                        = {{ number_format_quantity($delivery_order_item->quantity) }} {{ $delivery_order_item->unit }}
                                        <br/>
                                    @endforeach
                                </td>
                            </tr>
                        <?php $i++;?>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $list_delivery_order->render() !!}
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
        var delivery_order_id = [];
        for (var i = 0; i < {{$i}}; i++) {
            if ($("#delivery-order-id-"+i).is(":checked")) {    
                delivery_order_id.push($("#delivery-order-id-"+i).val());
            }
        };

        var person_id = $("#person-id").val();
        var url = '{{url()}}/sales/point/indirect/invoice/create-step-3/?person_id='+person_id+'&delivery_order_id='+delivery_order_id;
        location.href = url;
    }

</script>
@stop
