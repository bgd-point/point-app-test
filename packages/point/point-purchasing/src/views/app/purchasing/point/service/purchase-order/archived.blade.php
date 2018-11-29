@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.service._breadcrumb')
            <li><a href="{{ url('purhcasing/point/service/purchase-order') }}">Purchase Order</a></li>
            <li><a href="{{ url('purchasing/point/service/purchase-order/'.$purchase_order->id) }}">{{ $purchase_order->formulir->form_number}}</a></li>
            <li>Archived</li>
        </ul>
        <h2 class="sub-header">Purchase Order </h2>
        @include('point-purchasing::app.purchasing.point.service.purchase-order._menu')

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
                        {{ $purchase_order_archived->formulir->archived }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Date</label>
                    <div class="col-md-6 content-show">
                        {{ date_format_view($purchase_order_archived->formulir->form_date, true) }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Supplier</label>
                    <div class="col-md-6 content-show">
                        {!! get_url_person($purchase_order_archived->person->id) !!}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Notes</label>
                    <div class="col-md-6 content-show">
                        {{ $purchase_order_archived->notes }}
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
                                    <th>NOTES</th>
                                    <th class="text-right">QUANTITY</th>
                                    <th class="text-right">PRICE</th>
                                    <th class="text-right">DISCOUNT (%)</th>
                                    <th class="text-right">TOTAL</th>
                                </tr>
                                </thead>
                                <tbody class="manipulate-row">
                                @foreach($purchase_order_archived->services as $purchase_order_service)
                                    <tr>
                                        <td>
                                            <a href="{{ url('master/service/'.$purchase_order_service->service_id) }}">
                                                {{ $purchase_order_service->service->name }}
                                            </a>
                                        </td>
                                        <td>{{ $purchase_order_service->service_notes}}</td>
                                        <td class="text-right">
                                            {{ number_format_quantity($purchase_order_service->quantity) }} {{ $purchase_order_service->unit }}
                                        </td>
                                        <td class="text-right">
                                            {{ number_format_quantity($purchase_order_service->price) }}
                                        </td>
                                        <td class="text-right">
                                            {{ number_format_quantity($purchase_order_service->discount) }}
                                        </td>
                                        <td class="text-right">
                                            {{ number_format_quantity($purchase_order_service->quantity * $purchase_order_service->price * (100 - $purchase_order_service->discount) / 100) }}
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table  class="table">
                                <tr>
                                    <td colspan="4"></td>
                                    <td class="text-right"><strong>SUB TOTAL</strong></td>
                                    <td class="text-right">{{ number_format_quantity($purchase_order_archived->subtotal) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="4"></td>
                                    <td class="text-right"><strong>DISCOUNT (%)</strong></td>
                                    <td class="text-right">{{ number_format_quantity($purchase_order_archived->discount) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="4"></td>
                                    <td class="text-right"><strong>TAX BASE</strong></td>
                                    <td class="text-right">{{ number_format_quantity($purchase_order_archived->tax_base) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="4"></td>
                                    <td class="text-right"><strong>TAX</strong></td>
                                    <td class="text-right">{{ number_format_quantity($purchase_order_archived->tax) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="4"></td>
                                    <td class="text-right"><strong>TOTAL</strong></td>
                                    <td class="text-right">{{ number_format_quantity($purchase_order_archived->total) }}</td>
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
                            {{ $purchase_order_archived->formulir->createdBy->name }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Approval</label>

                        <div class="col-md-6 content-show">
                            {{ $purchase_order_archived->formulir->approvalTo->name }}
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
