@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-sales::app.sales.point.sales._breadcrumb')
            <li><a href="{{ url('sales/point/indirect/invoice') }}">Invoice</a></li>
            <li>Retur</li>
        </ul>
        <h2 class="sub-header">Invoice Retur</h2>
        @include('point-sales::app.sales.point.sales.invoice._menu')

        @include('core::app.error._alert')

        <div class="block full">
            <!-- Block Tabs Title -->
            <div class="block-title">
                <ul class="nav nav-tabs" data-toggle="tabs">
                    <li class="active"><a href="#block-tabs-home">Form</a></li>
                    <li><a href="#block-tabs-settings"><i class="gi gi-settings"></i></a></li>
                </ul>
            </div>
            <!-- END Block Tabs Title -->

            <!-- Tabs Content -->
            <div class="tab-content">
                <div class="tab-pane active" id="block-tabs-home">
                    <form class="form-horizontal form-bordered" method="POST" action="{{ url('sales/point/indirect/invoice/'. $invoice->id.'/retur') }}">
                        {!! csrf_field() !!}
                        <fieldset>
                            <div class="form-group pull-right">
                                <div class="col-md-12">
                                    @include('framework::app.include._form_status_label', ['form_status' => $invoice->formulir->form_status])
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Formulir</legend>
                                </div>
                            </div>
                        </fieldset>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Date</label>

                            <div class="col-md-3">
                                <input readonly type="text" name="form_date" class="form-control date"
                                        data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}"
                                        value="{{ date(date_format_get(), strtotime(\Carbon::now())) }}">
                            </div>
                            <div class="col-md-3">
                                <div class="input-group bootstrap-timepicker">
                                    <input type="text" id="time" name="time" class="form-control timepicker">
                                    <span class="input-group-btn">
                                <a href="javascript:void(0)" class="btn btn-effect-ripple btn-primary"><i
                                            class="fa fa-clock-o"></i></a>
                            </span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">FORM NUMBER</label>
                            <div class="col-md-6 content-show">
                                {{ $invoice->formulir->form_number }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Date</label>
                            <div class="col-md-6 content-show">
                                {{ date_format_view($invoice->formulir->form_date, true) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Due Date</label>
                            <div class="col-md-6 content-show">
                                {{ date_format_view($invoice->due_date, true) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">DELIVERY ORDER NUMBER</label>
                            <div class="col-md-6 content-show">
                                @foreach($invoice->lockingForm as $lockingForm)
                                    {!! formulir_url($lockingForm->lockedForm) !!}
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Customer</label>
                            <div class="col-md-6 content-show">
                                {!! get_url_person($invoice->person_id) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Notes</label>
                            <div class="col-md-6 content-show">
                                {{ $invoice->formulir->notes }}
                            </div>
                        </div>
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Item</legend>
                                </div>
                            </div>
                        </fieldset>

                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table id="item-datatable" class="table table-striped">
                                        <thead>
                                        <tr>
                                            <th>ITEM</th>
                                            <th class="text-right">QUANTITY</th>
                                            <th class="text-right">QTY RETUR</th>
                                        </tr>
                                        </thead>
                                        <tbody class="manipulate-row">
                                        @foreach($invoice->items as $invoice_item)
                                            <?php
                                                $remaining = Point\Framework\Helpers\ReferHelper::remaining(get_class($invoice), $invoice->formulir_id, $invoice->total);
                                            ?>
                                            <tr>
                                                <td>
                                                    <a href="{{ url('master/item/'.$invoice_item->item_id) }}">{{ $invoice_item->item->codeName }}</a>
                                                    <input type="hidden" name="item_id[]"
                                                            value="{{$invoice_item->item_id}}"/>
                                                </td>
                                                <td class="text-right">{{ number_format_quantity($invoice_item->quantity) }} {{ $invoice_item->unit }}</td>
                                                <td class="text-right">
                                                    <input id="item-quantity"
                                                            name="item_quantity[]"
                                                            class="form-control format-quantity text-right calculate"
                                                            value="{{ $invoice_item->quantity }}"/>
                                                    <input type="hidden" name="item_unit[]" value="{{ $invoice_item->unit }}">
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Person In Charge</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Form Creator</label>
                                <div class="col-md-6 content-show">
                                    {{ $invoice->formulir->createdBy->name }}
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
            <!-- END Tabs Content -->
        </div>
    </div>
@stop

@section('scripts')
    <script>
        var item_table = initDatatable('#item-datatable');
    </script>
@stop
