@extends('core::app.layout')

@section('content')
<div id="page-content">
    <h2 class="sub-header">Point of Sales</h2>

    @include('point-sales::app.sales.point.pos._menu')

    <div class="panel panel-default">
        <div class="panel-body" id="posview">
            <div class="form-horizontal row">
                <div class="col-xs-12 col-md-4">
                    <img src="{{url_logo()}}" height="80px" width="auto" class="img pull-left" style="margin-left: 10px">
                    <div class="pull-left text-left v-center">
                        <div class="h4 text-primary"><strong>{{$warehouse_profiles->store_name}}</strong></div>
                        <p><b>{{$warehouse_profiles->address}}<br> {{$warehouse_profiles->phone}}</b></p>
                    </div>
                </div>

                <div class="col-xs-12 col-md-8">
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-3 control-label">Customer</label>
                        <div class="col-xs-12 col-sm-3 col-md-9 content-show" id="content-customer">
                            {{$pos->customer->name}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-3 control-label">Warehouse</label>
                        <div class="col-xs-12 col-sm-9 col-md-9 content-show">
                            {{ $warehouse->name }}
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12 col-sm-3 col-md-3 control-label">
                            <strong>Date</strong>
                        </div>
                        <div class="col-xs-12 col-sm-9 col-md-9 content-show">
                            {{ date_format_view(date('Y-m-d'))}}
                        </div>
                    </div>
                </div>
            </div>
            <form action="{{ url('sales/point/pos/'.$pos->id.'/retur') }}" method="post" class="form-horizontal row">
                {!! csrf_field() !!}
                <input name="_method" type="hidden" value="PUT">
                <input type="hidden" name="form_date" value="{{ date('Y-m-d') }}" />
                <input type="hidden" readonly name="customer_id" id="customer_id" value="{{ session('customer_id') }}">
                <input type="hidden" readonly name="pos_id" id="pos_id" value="{{ $pos->id }}">
                <input type="hidden" readonly name="warehouse_id" id="warehouse_id" value="{{ $warehouse->id }}">
                <div class="table-responsive">
                    <table id="item-datatable" class="table table-striped">
                        <thead>
                        <tr>
                            <th width="55%">ITEM</th>
                            <th width="15%" class="text-right">QUANTITY</th>
                            <th width="15%" class="text-right">RETUR</th>
                            <th width="15%" class="text-right">TOTAL</th>
                        </tr>
                        </thead>
                        <tbody class="">
                            <?php $index = 0 ?>
                            @foreach($pos->items as $detail)
                                <tr>
                                    <td style="vertical-align:middle">
                                        <div style="margin-top:5px" id="item-name-{{$index}}">{{ $detail->item->codeName }}</div>
                                        <input type="hidden" id="item-id-{{$index}}" name="item_id[]" value="{{$detail->item->id}}"/>
                                        <input type="hidden" name="price[]" readonly id="item-price-{{$index}}" class="form-control format-quantity calculate text-right" value="{{ $detail->quantity * $detail->price }}">
                                    </td>
                                    <td><input type="text" name="quantity[]" readonly id="item-quantity-{{$index}}" class="form-control format-quantity text-right" value="{{ $detail->quantity }}" autofocus="false"></td>
                                    <td><input type="text" name="quantity_retur[]" id="item-quantity-retur-{{$index}}" class="form-control format-quantity calculate text-right" value="0"></td>
                                    <td><input type="text" name="total[]" id="item-total-{{$index}}" class="form-control format-price text-right" readonly value="0" autofocus="false"/></td>
                                </tr>
                                <?php $index++ ?>
                            @endforeach
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="3"></td>
                            <td><input type="text" name="total[]" id="total" class="form-control format-price text-right" readonly value="0"/></td>
                        </tr>
                        <tr>
                            <td colspan="3"></td>
                            <td><button class="btn btn-primary btn-block">Retur</button></td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </form>
        </div>
    </div>  
</div>

@include('framework::scripts.item')
@stop

@section('scripts')
<style type="text/css">
    .form-group {
        margin-bottom: 0;
    }

    .form-group input[type="checkbox"] {
        display: none;
    }

    .form-group input[type="checkbox"] + .btn-group > label span {
        width: 20px;
    }

    .form-group input[type="checkbox"] + .btn-group > label span:first-child {
        display: none;
    }
    .form-group input[type="checkbox"] + .btn-group > label span:last-child {
        display: inline-block;
    }

    .form-group input[type="checkbox"]:checked + .btn-group > label span:first-child {
        display: inline-block;
    }
    .form-group input[type="checkbox"]:checked + .btn-group > label span:last-child {
        display: none;
    }
</style>
<script>
    initDatatable('#item-datatable');
    var counter = $("#item-datatable").dataTable().fnGetNodes().length;

    $('.calculate').keyup(function(){
        calculate();
    });

    function calculate() {
        var total = 0;
        console.log('counter: ' + counter);
        for(var i=0; i<counter; i++) {
            var price = dbNum($('#item-quantity-retur-'+i).val());
            var qty = dbNum($('#item-price-'+i).val());
            var total_per_row = price * qty;
            $('#item-total-'+i).val(appNum(total_per_row));
            total += total_per_row;
        }
        $('#total').val(appNum(total_per_row));
    }
</script>
@stop
