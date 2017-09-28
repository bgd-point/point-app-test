@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-sales::app/sales/point/pos/_breadcrumb')
        <li>{{ $pos->formulir->form_number }}</li>
    </ul>
    <h2 class="sub-header">Point of Sales</h2>
    <div style="float:right">
        <a href="javascript:void(0)" onclick="pagePrint('/sales/point/pos/print/{{$pos->id}}');" class="btn btn-effect-ripple btn-effect-ripple btn-danger btn-block"><i class="fa fa-print"></i> Print</a>
    </div>
    @include('point-sales::app.sales.point.pos._menu')

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
                <div class="form-horizontal form-bordered">
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Form</legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <label class="col-xs-3 control-label">Date</label>
                        <div class="col-xs-9 content-show">
                            {{ date_format_view($pos->formulir->form_date) }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-3 control-label">Form Number</label>
                        <div class="col-xs-9 content-show">
                            {{ $pos->formulir->form_number }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-3 control-label">Form Status</label>
                        <div class="col-xs-9 content-show">
                            @include('framework::app.include._form_status_label', ['form_status' => $pos->formulir->form_status])
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-3 control-label">Customer</label>
                        <div class="col-xs-9 content-show">
                            {{ $pos->customer->name }}
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <legend><i class="fa fa-angle-right"></i> Details</legend>                    
                </div>
                <!-- <div class="table-responsive"> -->
                    <table id="item-datatable" class="table table-striped">
                        <thead>
                            <tr >
                                <th>ITEM</th>
                                <th class="text-right">QUANTITY</th>
                                <th class="text-right">PRICE</th>
                                <th class="text-right">DISCOUNT</th>
                                <th class="text-right">TOTAL</th>
                            </tr>
                        </thead>
                        <tbody >
                            @foreach($pos->items as $pos_item)
                            <tr>
                                <td>{{ $pos_item->item->codeName }}</td>
                                <td class="text-right"> {{number_format_quantity($pos_item->quantity)}}
                                    {{$pos_item->unit}}
                                </td>
                                <td class="text-right"> {{number_format_quantity($pos_item->price)}} </td>
                                <td class="text-right"> {{number_format_quantity($pos_item->discount)}} </td>
                                <td class="text-right"> {{number_format_quantity($pos_item->quantity * $pos_item->price - ($pos_item->discount / 100 * $pos_item->quantity * $pos_item->price))}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-right"><b>SUB TOTAL</b></td>
                                <td class="text-right"> {{number_format_quantity($pos->subtotal)}} </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-right"><b>DISCOUNT</b></td>
                                <td class="text-right"> {{number_format_quantity($pos->discount)}} </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-right">TAX BASE</td>
                                <td class="text-right">{{number_format_quantity($pos->tax_base)}}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-right">TAX ({{$pos->tax_type}})</td>
                                <td class="text-right">{{number_format_quantity($pos->tax)}}</td>
                            </tr>
                            <tr>
                                <td colspan="4"></td>
                                <td>

                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-right"><b>TOTAL</b></td>
                                <td class="text-right"> {{number_format_quantity($pos->total)}} </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-right"><b>MONEY RECEIVED</b></td>
                                <td class="text-right"> {{number_format_quantity($pos->money_received)}} </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-right"><b>CHANGE</b></td>
                                <td class="text-right"> {{number_format_quantity($pos->money_received - $pos->total)}} </td>
                            </tr>
                        </tfoot>
                    </table>
                <!-- </div> -->
            </div>
            <div class="tab-pane" id="block-tabs-settings">
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Action</legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                @if($pos->formulir->form_status == 0)
                                @if(formulir_view_edit($pos->formulir, 'update.point.sales.order'))
                                    <a href="{{url('sales/point/pos/'.$pos->id.'/edit#posview')}}"
                                           class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                                @endif
                                @endif
                                @if(formulir_view_cancel($pos->formulir, 'delete.point.sales.order'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureCancelForm('{{url('formulir/cancel')}}',
                                               '{{ $pos->formulir_id }}',
                                               'delete.point.sales.order')"><i class="fa fa-times"></i> Cancel Form</a>
                                @endif
                                @if(formulir_view_close($pos->formulir, 'update.point.sales.order'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureCloseForm({{$pos->formulir_id}},'{{url('formulir/close')}}')">Close
                                        Form</a>
                                @endif
                                @if(formulir_view_reopen($pos->formulir, 'update.point.sales.order'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureReopenForm({{$pos->formulir_id}},'{{url('formulir/reopen')}}')">Reopen
                                        Form</a>
                                @endif
                                @if($pos->formulir->approval_status == 1 && $pos->formulir->form_status == 0 && auth()->user()->may('create.point.sales.downpayment') && $pos->is_cash == 1)
                                    <a href="{{ url('sales/point/pos/downpayment/insert/' . $pos->id) }}"
                                       class="btn btn-effect-ripple  btn-info"><i class="fa fa-external-link"></i>
                                        Downpayment</a>
                                @endif
                            </div>
                        </div>
                    </fieldset>

                    @if($list_pos_archived->count() > 0)
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Archived Form</legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12 content-show">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                        <tr>
                                            <th></th>
                                            <th>Form Date</th>
                                            <th>Form Number</th>
                                            <th>Created By</th>
                                            <th>Updated By</th>
                                            <th>Reason</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php $count = 0;?>
                                        @foreach($list_pos_archived as $pos_archived)
                                            <tr>
                                                <td class="text-center">
                                                    <a href="{{ url('sales/point/pos/'.$pos_archived->formulirable_id.'/archived') }}"
                                                       data-toggle="tooltip" title="Show"
                                                       class="btn btn-effect-ripple btn-xs btn-info">
                                                       <i class="fa fa-file"></i> {{ 'Revision ' . $count++ }}
                                                    </a>
                                                </td>
                                                <td>{{ date_format_view($pos->formulir->form_date) }}</td>
                                                <td>{{ $pos_archived->formulir->archived }}</td>
                                                <td>{{ $pos_archived->formulir->createdBy->name }}</td>
                                                <td>{{ $pos_archived->formulir->updatedBy->name }}</td>
                                                <td>{{ $pos_archived->formulir->edit_notes }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    @endif
            </div>
        </div>
    </div>
    @stop

    @section('scripts')
  <script>
    initDatatable("#item-datatable");
    function pagePrint(url){
        var printWindow = window.open( url, 'Print', 'left=200, top=200, width=950, height=500, toolbar=0, resizable=0');
        printWindow.addEventListener('load', function(){
            printWindow.print();
            //printWindow.close();
        }, true);
    }
</script>
@stop
