@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-expedition::app/expedition/point/invoice/_breadcrumb')
            <li><a href="{{ url('expedition/point/invoice/'.$invoice->id) }}">{{$invoice->formulir->form_date}}</a></li>
            <li>Archived</li>
        </ul>
        <h2 class="sub-header">Invoice </h2>
        @include('point-expedition::app.expedition.point.invoice._menu')

        @include('core::app.error._alert')

        <div class="block full">
            <div class="form-horizontal form-bordered">
                <div class="form-group">
                    <div class="col-md-12">
                        <div class="alert alert-danger alert-dismissable">
                            <h1 class="text-center"><strong>Archived</strong></h1>
                        </div>
                    </div>
                </div>

                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Formulir</legend>
                        </div>
                    </div>
                </fieldset>
                <div class="form-group">
                    <label class="col-md-3 control-label">FORM NUMBER</label>
                    <div class="col-md-6 content-show">
                        {{ $invoice_archived->formulir->archived }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Date</label>
                    <div class="col-md-6 content-show">
                        {{ date_format_view($invoice_archived->formulir->form_date, true) }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Expedition</label>
                    <div class="col-md-6 content-show">
                        {!! get_url_person($invoice_archived->expedition_id) !!}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Notes</label>
                    <div class="col-md-6 content-show">
                        {{ $invoice_archived->formulir->notes }}
                    </div>
                </div>
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Item</legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="item-datatable" class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th>ITEM</th>
                                        <th class="text-right">QUANTITY</th>

                                    </tr>
                                    </thead>
                                    <tbody class="manipulate-row">
                                    @foreach($invoice_archived->items as $invoice_archived_item)
                                        <tr>
                                            <td>
                                                <a href="{{ url('master/item/'.$invoice_archived_item->item_id) }}">{{ $invoice_archived_item->item->codeName }}</a>
                                                <input type="hidden" name="item_id[]" value="{{$invoice_archived_item->item_id}}"/>
                                            </td>
                                            <td class="text-right">{{ number_format_quantity($invoice_archived_item->quantity) }} {{ $invoice_archived_item->unit }}</td>

                                        </tr>
                                    @endforeach
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td class="text-right">SUB TOTAL</td>
                                        <td class="text-right">{{ number_format_quantity($invoice_archived->subtotal) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-right">DISCOUNT</td>
                                        <td class="text-right">{{ number_format_quantity($invoice_archived->discount) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-right">TAX BASE</td>
                                        <td class="text-right">{{ number_format_quantity($invoice_archived->tax_base) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-right">TAX</td>
                                        <td class="text-right">{{ number_format_quantity($invoice_archived->tax) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-right">TOTAL</td>
                                        <td class="text-right">{{ number_format_quantity($invoice_archived->total) }}</td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Person In Charge</legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Creator</label>
                        <div class="col-md-6 content-show">
                            {{ $invoice_archived->formulir->createdBy->name }}
                        </div>
                    </div>

                </fieldset>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script>
        initDatatable('#item-datatable');
    </script>
@stop
