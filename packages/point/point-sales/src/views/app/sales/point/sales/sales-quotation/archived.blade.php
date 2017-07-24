@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-sales::app.sales.point.sales._breadcrumb')
            <li><a href="{{ url('sales/point/indirect/sales-quotation') }}">Sales Quotation</a></li>
            <li><a href="{{ url('sales/point/indirect/sales-quotation/'.$sales_quotation->id) }}">{{$sales_quotation->formulir->form_date}}</a></li>
            <li>Archived</li>
        </ul>
        <h2 class="sub-header">Sales Quotation</h2>
        @include('point-sales::app.sales.point.sales.sales-quotation._menu')

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
                            <legend><i class="fa fa-angle-right"></i> Form</legend>
                        </div>
                    </div>
                </fieldset>
                <div class="form-group">
                    <label class="col-md-3 control-label">Form Number</label>

                    <div class="col-md-6 content-show">
                        {{ $sales_quotation_archived->formulir->archived }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Form Date</label>
                    <div class="col-md-6 content-show">
                        {{ date_format_view($sales_quotation_archived->formulir->form_date, true) }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Customer</label>
                    <div class="col-md-6 content-show">
                        {!! get_url_person($sales_quotation_archived->person->id) !!}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Notes</label>
                    <div class="col-md-6 content-show">
                        {{ $sales_quotation_archived->formulir->notes }}
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
                                    <tr>
                                        <th>ITEM</th>
                                        <th>ALLOCATION</th>
                                        <th>QUANTITY</th>
                                        <th>UNIT</th>
                                        <th>PRICE</th>
                                        <th>DISCOUNT (%)</th>
                                        <th>TOTAL</th>
                                    </tr>
                                    </tr>
                                    </thead>
                                    <tbody class="manipulate-row">
                                    @foreach($sales_quotation_archived->items as $sales_quotation_item)
                                        <tr>
                                            <td>{{ $sales_quotation_item->item->codeName }}</td>
                                            <td>{{ $sales_quotation_item->allocation_id ? $sales_quotation_item->allocation->name : 'no allocation'}}</td>
                                            <td>{{ number_format_quantity($sales_quotation_item->quantity) }} </td>
                                            <td>{{ $sales_quotation_item->unit }}</td>
                                            <td>{{ number_format_quantity($sales_quotation_item->price) }}</td>
                                            <td>{{ number_format_quantity($sales_quotation_item->discount) }}</td>
                                            <td>{{ number_format_quantity($sales_quotation_item->quantity * $sales_quotation_item->price - $sales_quotation_item->quantity * $sales_quotation_item->price/100 * $sales_quotation_item->discount) }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td colspan="5" class="text-right">SUBTOTAL</td>
                                        <td class="text-right">{{ number_format_quantity($sales_quotation_archived->subtotal) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right">DISCOUNT (%)</td>
                                        <td class="text-right">{{ number_format_quantity($sales_quotation_archived->discount) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right">TAX BASE</td>
                                        <td class="text-right">{{ number_format_quantity($sales_quotation_archived->tax_base) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right">TAX
                                            ({{ $sales_quotation_archived->type_of_tax }})
                                        </td>
                                        <td class="text-right">{{ number_format_quantity($sales_quotation_archived->tax) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right">EXPEDITION FEE</td>
                                        <td class="text-right">{{ number_format_quantity($sales_quotation_archived->expedition_fee) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right">TOTAL</td>
                                        <td class="text-right">{{ number_format_quantity($sales_quotation_archived->total) }}</td>
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
                            <legend><i class="fa fa-angle-right"></i> Authorized User</legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Creator</label>

                        <div class="col-md-6 content-show">
                            {{ $sales_quotation_archived->formulir->createdBy->name }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Request Approval To</label>

                        <div class="col-md-6 content-show">
                            {{ $sales_quotation_archived->formulir->approvalTo->name }}
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Status</legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Approval Status</label>

                        <div class="col-md-6 content-show">
                            @include('framework::app.include._approval_status_label_detailed', [
                                        'approval_status' => $sales_quotation_archived->formulir->approval_status,
                                        'approval_message' => $sales_quotation_archived->formulir->approval_message,
                                        'approval_at' => $sales_quotation_archived->formulir->approval_at,
                                        'approval_to' => $sales_quotation_archived->formulir->approvalTo->name,
                                    ])
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Status</label>

                        <div class="col-md-6 content-show">
                            @include('framework::app.include._form_status_label', ['form_status' => $sales_quotation_archived->formulir->form_status])
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
