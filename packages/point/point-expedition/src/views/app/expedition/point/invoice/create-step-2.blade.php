@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-expedition::app/expedition/point/invoice/_breadcrumb')
            <li>Create Step 2</li>
        </ul>
        <h2 class="sub-header">Invoice</h2>
        @include('point-expedition::app.expedition.point.invoice._menu')


        <div class="panel panel-default form-horizontal form-bordered" style="margin:5px">
            <div class="panel-body">
                <!-- LIST DATA EXPEDITION ORDE FORM SALES ORDER -->
                @if(count($list_invoice_sales) > 0)
                    <form action="{{url('expedition/point/invoice/create-step-3')}}" method="post" class="">
                        {!! csrf_field() !!}
                        <input type="hidden" name="expedition_id" value="{{$expedition_id}}"/>
                        <h4><strong>SALES - EXPEDITION ORDER</strong></h4>
                        <div class="table-responsive">

                            <table class="table">
                                <thead>
                                <tr>
                                    <th width="100px" class="text-center"></th>
                                    <th>FORM DATE</th>
                                    <th>FORM NUMBER</th>
                                    <th>EXPEDITION</th>
                                    <th class="text-right">AMOUNT</th>
                                </tr>
                                </thead>
                                <tbody>
                                @for($i=0;$i<count($list_invoice_sales);$i++)
                                    @for($y=0;$y<count($list_invoice_sales[$i]);$y++)
                                        <tr id="list-{{$i}}">
                                            <td class="text-center" rowspan="2">
                                                <input type="checkbox" name="expedition_order_id[]" value="{{$list_invoice_sales[$i]->formulir_id}}">
                                            </td>
                                            <td>{{ date_format_view($list_invoice_sales[$i]->formulir->form_date) }} <br>
                                                <i class="fa fa-caret-down"></i>
                                                <a data-toggle="collapse" href="#collapse{{$list_invoice_sales[$i]->formulir_id}}">
                                                    <small>Detail</small>
                                                </a>
                                            </td>
                                            <td>
                                                <a href="{{ url('expedition/point/expedition-order/'.$list_invoice_sales[$i]->id) }}">{{$list_invoice_sales[$i]->formulir->form_number}}
                                            </td>
                                            <td>
                                                <a href="{{ url('master/contact/expedition/'.$list_invoice_sales[$i]->expedition_id) }}">{{$list_invoice_sales[$i]->expedition->codeName}} </a>
                                            </td>
                                            <td class="text-right">{{ number_format_quantity($list_invoice_sales[$i]->total) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="5" style="border-top: none;">
                                                <div id="collapse{{$list_invoice_sales[$i]->formulir_id}}"
                                                     class="panel-collapse collapse">
                                                    <b>Description</b>
                                                    <ul class="list-group">
                                                        @foreach($list_invoice_sales[$i]->items as $expedition_order_item)
                                                            <li class="list-group-item">
                                                                <small>{{ $expedition_order_item->item->codeName }}
                                                                    # {{ number_format_quantity($expedition_order_item->quantity) }} {{ $expedition_order_item->unit }}
                                                                    <span class="pull-right">{{ number_format_quantity($expedition_order_item->item_fee) }}</span>
                                                                </small>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endfor
                                @endfor
                                </tbody>
                            </table>

                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-effect-ripple btn-primary">Next</button>
                            </div>
                        </div>
                    </form>
                @endif

            <!-- LIST DATA EXPEDITION ORDE FORM PURCHASE ORDER -->
                @if(count($list_invoice_purchase) > 0)
                    <form action="{{url('expedition/point/invoice/create-step-3')}}" method="post" class="">
                        {!! csrf_field() !!}
                        <input type="hidden" name="expedition_id" value="{{$expedition_id}}"/>
                        <h4><strong>PURCHASE - EXPEDITION ORDER</strong></h4>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th width="100px" class="text-center"></th>
                                    <th>FORM DATE</th>
                                    <th>FORM NUMBER</th>
                                    <th>EXPEDITION</th>
                                    <th class="text-right">AMOUNT</th>
                                </tr>
                                </thead>
                                <tbody>
                                @for($i=0;$i<count($list_invoice_purchase);$i++)
                                    @for($y=0;$y<count($list_invoice_purchase[$i]);$y++)
                                        <tr id="list-{{$i}}">
                                            <td class="text-center" rowspan="2">
                                                <input type="checkbox" name="expedition_order_id[]"
                                                       value="{{$list_invoice_purchase[$i]->formulir_id}}">
                                            </td>
                                            <td>{{ date_format_view($list_invoice_purchase[$i]->formulir->form_date) }}
                                                <br>
                                                <i class="fa fa-caret-down"></i> 
                                                <a data-toggle="collapse" href="#collapse{{$list_invoice_purchase[$i]->formulir_id}}">
                                                    <small>Detail</small>
                                                </a>
                                            </td>
                                            <td>
                                                <a href="{{ url('expedition/point/expedition-order/'.$list_invoice_purchase[$i]->id) }}">{{$list_invoice_purchase[$i]->formulir->form_number}}
                                            </td>
                                            <td>
                                                <a href="{{ url('master/contact/expedition/'.$list_invoice_purchase[$i]->expedition_id) }}">{{$list_invoice_purchase[$i]->expedition->codeName}} </a>
                                            </td>
                                            <td class="text-right">{{ number_format_quantity($list_invoice_purchase[$i]->total) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="5" style="border-top: none;">
                                                <div id="collapse{{$list_invoice_purchase[$i]->formulir_id}}"
                                                     class="panel-collapse collapse">
                                                    <b>Description</b>
                                                    <ul class="list-group">
                                                        @foreach($list_invoice_purchase[$i]->items as $expedition_order_item)
                                                            <li class="list-group-item">
                                                                <small>{{ $expedition_order_item->item->codeName }}
                                                                    # {{ number_format_quantity($expedition_order_item->quantity) }} {{ $expedition_order_item->unit }}
                                                                    <span class="pull-right">{{ number_format_quantity($expedition_order_item->item_fee) }}</span>
                                                                </small>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endfor
                                @endfor
                                </tbody>
                            </table>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-effect-ripple btn-primary">Next</button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
@stop
