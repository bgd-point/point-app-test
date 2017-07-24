@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-finance::app.finance.point.payment-order._breadcrumb')
        <li><a href="{{ url('finance/point/payment-order/'.$payment_order->id) }}">{{ $payment_order->formulir->form_number }}</a></li>
        <li>Archived</li>
    </ul>
    <h2 class="sub-header"> Payment Order</h2>
    @include('point-finance::app.finance.point.payment-order._menu')

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
                <label class="col-md-3 control-label">Payment date</label>
                <div class="col-md-6 content-show">
                    {{date_format_view($payment_order_archived->formulir->form_date, true)}}
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">Form Number</label>
                <div class="col-md-6 content-show">
                    {{ $payment_order_archived->formulir->archived }}
                </div>  
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">Payment to</label>
                <div class="col-md-6 content-show">
                    {{$payment_order_archived->person->codeName}}
                </div>
            </div>
            <fieldset>
                <div class="form-group">
                    <div class="col-md-12">
                        <div class="table-responsive"> 
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th style="min-width: 115px">Account</th>
                                        <th style="min-width: 115px">Notes</th>
                                        <th style="min-width: 115px" class="text-right">Amount</th>
                                        <th style="min-width: 150px" class="text-center">Allocation</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php $total=0;?>
                                @foreach($payment_order_archived->detail as $payment_order_archived_detail)
                                    <?php $total += $payment_order_archived_detail->amount;?>
                                    <tr>
                                        <td>
                                            {{$payment_order_archived_detail->coa->account}} <br/>
                                        </td>
                                        <td>{{$payment_order_archived_detail->notes_detail}}</td>
                                        <td class="text-right">{{number_format_quantity($payment_order_archived_detail->amount)}}</td>
                                        <td class="text-center">{{$payment_order_archived_detail->allocation->name}}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="2" class="text-right"><h4><b>Total Payment</b></h4></td>
                                    <td class="text-right"><h4><strong>{{ number_format_quantity($total)}}</strong></h4></td>
                                    <td></td>
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
                    <label class="col-md-3 control-label">Form creator</label>
                    <div class="col-md-6 content-show">
                        {{ $payment_order_archived->formulir->createdBy->name }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Ask approval to</label>
                    <div class="col-md-6 content-show">
                        {{ $payment_order_archived->formulir->approvalTo->name }}
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
