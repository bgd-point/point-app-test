@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-inventory::app/inventory/point/transfer-item/_breadcrumb')
        <li><a href="{{ url('inventory/point/transfer-item/send/'.$transfer_item->id) }}">{{ $transfer_item->formulir->form_number }}</a></li>
        <li>Received Item</li>
    </ul>
    <h2 class="sub-header">Receive Item</h2>
    @include('point-inventory::app.inventory.point.transfer-item._menu')
    @include('core::app.error._alert')

    <div class="panel panel-default">
        <div class="panel-body">
            <form action="{{ url('inventory/point/transfer-item/received/store/'.$transfer_item->id) }}" method="POST" class="form-horizontal form-bordered">
                {!! csrf_field() !!}

                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Form</legend>
                        </div>
                    </div>
                </fieldset>
                <div class="form-group">
                    <label class="col-md-3 control-label">Received Date *</label>
                    <div class="col-md-3">
                        <input readonly type="text" id="form_date" name="form_date" class="form-control date input-datepicker" data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}" value="{{ date(date_format_get(), strtotime($transfer_item->formulir->form_date)) }}">
                    </div>
                    <div class="col-md-3">
                        <div class="input-group bootstrap-timepicker">
                            <input type="text" id="time" name="time" class="form-control timepicker" value="{{date('H:i', strtotime($transfer_item->formulir->form_date))}}">
                            <span class="input-group-btn">
                                <a href="javascript:void(0)" class="btn btn-effect-ripple btn-primary"><i class="fa fa-clock-o"></i></a>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">From Warehouse *</label>
                    <div class="col-md-6 content-show">
                        {{ Point\Framework\Models\Master\Warehouse::find($transfer_item->warehouse_sender_id)->codeName}}
                        <input type="hidden" id="warehouse-id" name="warehouse_id" value="{{ $transfer_item->warehouse_sender_id }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label">To Warehouse *</label>
                    <div class="col-md-6 content-show">
                        {{ Point\Framework\Models\Master\Warehouse::find($transfer_item->warehouse_receiver_id)->codeName}}
                        <input type="hidden" id="warehouse-id" name="warehouse_receive_id" value="{{ $transfer_item->warehouse_sender_id }}">
                    </div>
                </div>
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Detail Item</legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="item-datatable" class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th style="min-width:250px;">ITEM *</th>
                                            <th style="min-width:160px;">QUANTITY SEND  *</th>
                                            <th style="min-width:160px;">QUANTITY RECEIVE  *</th>
                                        </tr>
                                    </thead>
                                    <tbody class="manipulate-row">
                                        <?php $counter=0; ?>
                                        @foreach($transfer_item->items as $transfer_item_detail)
                                        <tr>
                                            <td>     
                                             <input type="hidden" name="item_id[]" class="form-control" value="{{$transfer_item_detail->item_id}}"/>
                                             <input type="hidden" name="cogs[]" class="form-control" value="{{$transfer_item_detail->cogs}}"/>
                                             <span>{{$transfer_item_detail->item->codeName}}</span>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input type="text" name="quantity_send" class="form-control format-quantity text-right" value="{{$transfer_item_detail->qty_send }}" readonly/>
                                                    <span id="unit-id2-{{$counter}}" class="input-group-addon">{{ $transfer_item_detail->unit }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input type="text" name="quantity_transfer[]" class="form-control format-quantity text-right" value="0" />
                                                    <span id="unit-id2-{{$counter}}" class="input-group-addon">{{ $transfer_item_detail->unit }}</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php $counter++; ?>
                                        @endforeach
                                    </tbody>
                                   
                                </table>
                            </div>
                        </div>
                    </div>
                </fieldset>
                <div class="form-group">
                    <div class="col-md-6 col-md-offset-3">
                        <button type="submit" class="btn btn-effect-ripple btn-primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@stop


