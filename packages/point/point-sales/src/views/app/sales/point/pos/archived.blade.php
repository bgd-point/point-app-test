@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-sales::app/sales/point/pos/_breadcrumb')
        <li>Archived</li>
    </ul>
    <h2 class="sub-header">Point of Sales | Archived</h2>
    @include('point-sales::app.sales.point.pos._menu')

    @include('core::app.error._alert')
    <div class="block full">  
        <div class="form-horizontal form-bordered">
            <fieldset>
                <div class="col-md-12">
                    <div class="alert alert-danger alert-dismissable">
                        <h1 class="text-center"><strong>Archived</strong></h1>
                    </div>
                </div>
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i>Point Of Sales</legend>
                        </div>
                    </div>
                </fieldset>
                <div class="form-group">
                    <label class="col-md-3 control-label">POS DATE</label>
                    <div class="col-md-6 content-show">
                        {{ date_format_view($pos_archived->formulir->form_date, true) }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">POS NUMBER</label>
                    <div class="col-md-6 content-show">
                        {{ $pos_archived->formulir->form_number ? $pos_archived->formulir->form_number : $pos_archived->formulir->archived }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">CUSTOMER</label>
                    <div class="col-md-6 content-show">
                        {{ $pos_archived->customer->codeName }}
                    </div>
                </div>
            </fieldset>
            <fieldset>
                <div class="form-group">
                    <div class="col-md-12">
                        <legend><i class="fa fa-angle-right"></i> Details</legend>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <div class="form-horizontal">
                            <div class="table-responsive">
                                <table id="item-datatable" class="table table-striped" min-width="1280px">
                                    <thead>
                                        <tr>
                                            <th>ITEM</th>
                                            <th class="text-right">QUANTITY</th>
                                            <th class="text-right">PRICE</th>
                                            <th class="text-right">DISCOUNT</th>
                                            <th class="text-right">TOTAL</th>
                                        </tr>
                                    </thead>
                                    <tbody class="manipulate-row">
                                        @foreach($pos_archived->items as $sales_item)
                                        <tr>
                                            <td>{{ $sales_item->item->codeName }}</td>
                                            <td class="text-right"> {{number_format_accounting($sales_item->quantity)}}
                                                {{$sales_item->unit}}
                                            </td>
                                            <td class="text-right"> {{number_format_accounting($sales_item->price)}} </td>
                                            <td class="text-right"> {{number_format_accounting($sales_item->discount)}} </td>
                                            <td class="text-right"> {{number_format_accounting($sales_item->quantity * $sales_item->price - $sales_item->discount)}} </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="4" class="text-right"><b>SUB TOTAL</b></td>
                                            <td class="text-right"> {{number_format_quantity($pos_archived->subtotal)}} </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-right"><b>DISCOUNT</b></td>
                                            <td class="text-right"> {{number_format_quantity($pos_archived->discount)}} </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-right">TAX BASE</td>
                                            <td class="text-right">{{number_format_quantity($pos_archived->tax_base)}}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-right">TAX</td>
                                            <td class="text-right">{{number_format_quantity($pos_archived->tax)}}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="4"></td>
                                            <td>

                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-right"><b>TOTAL</b></td>
                                            <td class="text-right"> {{number_format_quantity($pos_archived->total)}} </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-right"><b>MONEY RECEIVED</b></td>
                                            <td class="text-right"> {{number_format_quantity($pos_archived->money_received)}} </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-right"><b>CHANGE</b></td>
                                            <td class="text-right"> {{number_format_quantity($pos_archived->money_received - $pos_archived->total)}} </td>
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
                            {{ $pos_archived->formulir->createdBy->name }}
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
