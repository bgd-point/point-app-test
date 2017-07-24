@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-sales::app.sales.point.service._breadcrumb')
            <li><a href="{{ url('sales/point/service/invoice') }}">Invoice</a></li>
            <li><a href="{{ url('sales/point/service/invoice/'.$invoice->id) }}">{{$invoice->formulir->form_number}}</a></li>
            <li>Archived</li>
        </ul>
        <h2 class="sub-header">Invoice </h2>
        @include('point-sales::app.sales.point.service.invoice._menu')

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
                    <label class="col-md-3 control-label">Customer</label>
                    <div class="col-md-6 content-show">
                        {!! get_url_person($invoice_archived->person->id) !!}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Notes</label>

                    <div class="col-md-6 content-show">
                        {{ $invoice_archived->notes }}
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <legend><i class="fa fa-angle-right"></i> Service</legend>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <!-- SERVICE DATA -->
                            <table id="item-datatable" class="table table-striped">
                                <thead>
                                <tr>
                                    <th>SERVICE</th>
                                    <th>ALLOCATION</th>
                                    <th>NOTES</th>
                                    <th class="text-right">QUANTITY</th>
                                    <th class="text-right">PRICE</th>
                                    <th class="text-right">DISCOUNT (%)</th>
                                    <th class="text-right">TOTAL</th>
                                </tr>
                                </thead>
                                <tbody class="manipulate-row">
                                @foreach($invoice->services as $invoice_service)
                                    <tr>
                                        <td>
                                            <a href="{{ url('master/item/'.$invoice_service->item_id) }}">{{ $invoice_service->service->name }}</a>
                                        </td>
                                        <td>{{$invoice_service->allocation->name}}</td>
                                        <td>{{$invoice_service->service_notes}}</td>
                                        <td class="text-right">{{ number_format_quantity($invoice_service->quantity) }} {{ $invoice_service->unit }}</td>
                                        <td class="text-right">{{ number_format_quantity($invoice_service->price) }}</td>
                                        <td class="text-right">{{ number_format_quantity($invoice_service->discount) }}</td>
                                        <td class="text-right">{{ number_format_quantity(($invoice_service->quantity * $invoice_service->price) - ($invoice_service->quantity * $invoice_service->discount)) }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
                @if(count($invoice_archived->items) > 0)
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
                                        <th>ALLOCATION</th>
                                        <th>NOTES</th>
                                        <th class="text-right">QUANTITY</th>
                                        <th class="text-right">PRICE</th>
                                        <th class="text-right">DISCOUNT</th>
                                        <th class="text-right">TOTAL</th>
                                    </tr>
                                    </thead>
                                    <tbody class="manipulate-row">
                                    @foreach($invoice_archived->items as $invoice_archived_item)
                                        <tr>
                                            <td>
                                                <a href="{{ url('master/item/'.$invoice_archived_item->item_id) }}">{{ $invoice_archived_item->item->codeName }}</a>
                                                <input type="hidden" name="item_id[]"
                                                       value="{{$invoice_archived_item->item_id}}"/>
                                            </td>
                                            <td>{{$invoice_archived_item->allocation->name}}</td>
                                            <td>{{$invoice_archived_item->item_notes}}</td>
                                            <td class="text-right">{{ number_format_quantity($invoice_archived_item->quantity) }} {{ $invoice_archived_item->unit }}</td>
                                            <td class="text-right">{{ number_format_quantity($invoice_archived_item->price) }}</td>
                                            <td class="text-right">{{ number_format_quantity($invoice_archived_item->discount) }}</td>
                                            <td class="text-right">{{ number_format_quantity(($invoice_archived_item->quantity * $invoice_archived_item->price) - ($invoice_archived_item->quantity * $invoice_archived_item->discount)) }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </fieldset>
                @endif
                <div class="form-group">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table  class="table">
                                <tr>
                                    <td colspan="5"></td>
                                    <td class="text-right"><strong>SUB TOTAL</strong></td>
                                    <td class="text-right">{{ number_format_quantity($invoice_archived->subtotal) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="5"></td>
                                    <td class="text-right"><strong>DISCOUNT (%)</strong></td>
                                    <td class="text-right">{{ number_format_quantity($invoice_archived->discount) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="5"></td>
                                    <td class="text-right"><strong>TAX BASE</strong></td>
                                    <td class="text-right">{{ number_format_quantity($invoice_archived->tax_base) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="5"></td>
                                    <td class="text-right"><strong>TAX</strong></td>
                                    <td class="text-right">{{ number_format_quantity($invoice_archived->tax) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="5"></td>
                                    <td class="text-right"><strong>TOTAL</strong></td>
                                    <td class="text-right">{{ number_format_quantity($invoice_archived->total) }}</td>
                                </tr>
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
                            {{ $invoice_archived->formulir->createdBy->name }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Approval</label>

                        <div class="col-md-6 content-show">
                            {{ $invoice_archived->formulir->approvalTo->name }}
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
