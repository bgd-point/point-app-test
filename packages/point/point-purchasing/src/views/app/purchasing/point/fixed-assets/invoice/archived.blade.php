@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.fixed-assets._breadcrumb')
            <li><a href="{{ url('purchasing/point/fixed-assets/invoice') }}">Invoice</a></li>
            <li>Archived</li>
        </ul>
        <h2 class="sub-header">Invoice | Fixed Assets</h2>
        @include('point-purchasing::app.purchasing.point.fixed-assets.invoice._menu')

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
                    <label class="col-md-3 control-label">Supplier</label>

                    <div class="col-md-6 content-show">
                        {{ $invoice_archived->supplier->codeName }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Notes</label>

                    <div class="col-md-6 content-show">
                        {{ $invoice_archived->notes }}
                    </div>
                </div>
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Details</legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="item-datatable" class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th>Account</th>
                                        <th>Assets Name</th>
                                        <th>Quantity</th>
                                        <th>Unit</th>
                                        <th>Price</th>
                                        <th>Allocation</th>
                                    </tr>
                                    </thead>
                                    <tbody class="manipulate-row">
                                    @foreach($invoice_archived->details as $invoice_archived_item)
                                        <tr>
                                            <td>{{ $invoice_archived_item->coa->name }}</td>
                                            <td>{{ $invoice_archived_item->name }}</td>
                                            <td class="text-right">{{ number_format_quantity($invoice_archived_item->quantity) }}</td>
                                            <td>{{ $invoice_archived_item->unit }}</td>
                                            <td class="text-right">{{ number_format_quantity($invoice_archived_item->price) }}</td>
                                            <td>{{ $invoice_archived_item->allocation->name }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td colspan="5" class="text-right">SUB TOTAL</td>
                                        <td class="text-right">{{ number_format_quantity($invoice_archived->subtotal) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right">DISCOUNT</td>
                                        <td class="text-right">{{ number_format_quantity($invoice_archived->discount) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right">TAX BASE</td>
                                        <td class="text-right">{{ number_format_quantity($invoice_archived->tax_base) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right">TAX</td>
                                        <td class="text-right">{{ number_format_quantity($invoice_archived->tax) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right">EXPEDITION FEE</td>
                                        <td class="text-right">{{ number_format_quantity($invoice_archived->expedition_fee) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right">TOTAL</td>
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
                        <label class="col-md-3 control-label">Pembuat Form</label>

                        <div class="col-md-6 content-show">
                            {{ $invoice_archived->formulir->createdBy->name }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Meminta Persetujuan Kepada</label>

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
