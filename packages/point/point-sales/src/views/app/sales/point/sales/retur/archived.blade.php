@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-sales::app.sales.point.sales._breadcrumb')
            <li><a href="{{ url('sales/point/indirect/retur') }}">Return</a></li>
            <li>Archived</li>
        </ul>
        <h2 class="sub-header">Return </h2>
        @include('point-sales::app.sales.point.sales.retur._menu')

        @include('core::app.error._alert')

        <div class="block full">
            <!-- Block Tabs Title -->
            <div class="block-title">
                <ul class="nav nav-tabs" data-toggle="tabs">
                    <li class="active"><a href="#block-tabs-home">Form</a></li>
                </ul>
            </div>
            <!-- END Block Tabs Title -->

            <!-- Tabs Content -->
            <div class="tab-content">
                <div class="tab-pane active" id="block-tabs-home">
                    <div class="form-horizontal form-bordered">
                        <fieldset>
                            <div class="form-group pull-right">
                                <div class="col-md-12">
                                    @include('framework::app.include._approval_status_label', [
                                        'approval_status' => $retur->formulir->approval_status,
                                        'approval_message' => $retur->formulir->approval_message,
                                        'approval_at' => $retur->formulir->approval_at,
                                        'approval_to' => $retur->formulir->approvalTo->name,
                                    ])
                                    @include('framework::app.include._form_status_label', ['form_status' => $retur->formulir->form_status])
                                </div>
                            </div>
                        </fieldset>
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
                                    <legend><i class="fa fa-angle-right"></i> INFO REFERENCE</legend>
                                </div>
                            </div>
                        </fieldset>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Date</label>

                            <div class="col-md-6 content-show">
                                {{date_Format_view($invoice->formulir->form_date, true)}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Number</label>

                            <div class="col-md-6 content-show">
                                <a href="{{url('sales/point/indirect/invoice/'.$invoice->id)}}">{{$invoice->formulir->form_number}}</a>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Customer</label>

                            <div class="col-md-6 content-show">
                                <input type="hidden" name="person_id" value="{{$invoice->person_id}}">
                                <a href="{{url('master/contact/person/'.$invoice->person_id)}}">{{$invoice->person->codeName}}</a>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Notes</label>

                            <div class="col-md-6 content-show">
                                {{$invoice->formulir->notes}}
                            </div>
                        </div>

                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Return Form</legend>
                                </div>
                            </div>
                        </fieldset>

                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Number</label>

                            <div class="col-md-6 content-show">
                                {{ $retur->formulir->form_number }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Date</label>

                            <div class="col-md-6 content-show">
                                {{ date_Format_view($retur->formulir->form_date, true) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Supplier</label>

                            <div class="col-md-6 content-show">
                                <a href="{{url('master/contact/person/'.$retur->person_id)}}">{{$retur->person->codeName}}</a>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Notes</label>

                            <div class="col-md-6 content-show">
                                {{ $retur->formulir->notes }}
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
                                                <th class="text-right">QUANTITY RECEIVED</th>
                                                <th class="text-right">QUANTITY RETURNED</th>
                                                <th>UNIT</th>
                                                <th class="text-right">PRICE</th>
                                                <th class="text-right">DISCOUNT</th>
                                                <th class="text-right">TOTAL</th>
                                            </tr>
                                            </thead>
                                            <tbody class="manipulate-row">
                                            @foreach($invoice->items as $invoice_item)
                                                <?php
                                                $refer_to = \Point\Framework\Helpers\ReferHelper::getReferTo(get_class($invoice_item),
                                                        $invoice_item->id,
                                                        get_class($retur),
                                                        $retur->id);
                                                ?>
                                                <tr>
                                                    <td>
                                                        <a href="{{ url('master/item/'.$invoice_item->item_id) }}">{{ $invoice_item->item->codeName }}</a>
                                                        <input type="hidden" name="item_id[]"
                                                               value="{{$invoice_item->item_id}}"/>
                                                    </td>
                                                    <td class="text-right">{{ number_format_quantity($invoice_item->quantity) }}</td>
                                                    <td class="text-right">{{ number_format_quantity($refer_to->quantity) }}</td>
                                                    <td>{{ $refer_to->unit }}</td>
                                                    <td class="text-right">{{ number_format_quantity($refer_to->price) }}</td>
                                                    <td class="text-right">{{ number_format_quantity($refer_to->discount) }}</td>
                                                    <td class="text-right">{{ number_format_quantity(($refer_to->quantity * $refer_to->price) - ($refer_to->quantity * $refer_to->discount)) }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                            <tfoot>
                                            <tr>
                                                <td colspan="6" class="text-right">SUB TOTAL</td>
                                                <td class="text-right">{{ number_format_quantity($retur->subtotal) }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="6" class="text-right">DISCOUNT</td>
                                                <td class="text-right">{{ number_format_quantity($retur->discount) }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="6" class="text-right">TAX BASE</td>
                                                <td class="text-right">{{ number_format_quantity($retur->tax_base) }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="6" class="text-right">TAX</td>
                                                <td class="text-right">{{ number_format_quantity($retur->tax) }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="6" class="text-right">EXPEDITION FEE</td>
                                                <td class="text-right">{{ number_format_quantity($retur->expedition_fee) }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="6" class="text-right">TOTAL</td>
                                                <td class="text-right">{{ number_format_quantity($retur->total) }}</td>
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
                                    <legend><i class="fa fa-angle-right"></i> PERSON IN CHARGE</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">FORM CREATOR</label>

                                <div class="col-md-6 content-show">
                                    {{ $retur->formulir->createdBy->name }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">ARK APPROVAL TO</label>

                                <div class="col-md-6 content-show">
                                    {{ $retur->formulir->approvalTo->name }}
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
            </div>
            <!-- END Tabs Content -->
        </div>
    </div>
@stop

@section('scripts')
    <style>
        tbody.manipulate-row:after {
            content: '';
            display: block;
            height: 100px;
        }
    </style>
    <script>
        var item_table = $('#item-datatable').DataTable({
            bSort: false,
            bPaginate: false,
            bInfo: false,
            bFilter: false,
            bScrollCollapse: false,
            scrollX: true
        });
    </script>
@stop
