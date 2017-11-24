@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-sales::app.sales.point.sales._breadcrumb')
            <li><a href="{{ url('sales/point/indirect/sales-order') }}">Sales Order</a></li>
            <li>Create step 1</li>
        </ul>
        <h2 class="sub-header">Sales Order</h2>
        @include('point-sales::app.sales.point.sales.sales-order._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                <a href="{{ url('sales/point/indirect/sales-order/create') }}" class="btn btn-info">
                    Create New Sales Order
                </a>
                @if($list_sales_quotation->count())
                <br><br>
                <h3>Create From Sales Quotation</h3>
                <div class="table-responsive">
                    {!! $list_sales_quotation->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th width="100px" class="text-center"></th>
                            <th>Sales Quotation</th>
                            <th>Notes</th>
                            <th>Customer</th>
                            <th>Order</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($list_sales_quotation as $sales_quotation)
                            <tr>
                                <td class="text-center">
                                    <a href="{{ url('sales/point/indirect/sales-order/create-step-2/'.$sales_quotation->id) }}"
                                       class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-external-link"></i>
                                        Create Sales Order</a>
                                </td>
                                <td>
                                    {{ date_format_view($sales_quotation->formulir->form_date) }}
                                    <br/> <a href="{{ url('sales/point/indirect/sales-quotation/'.$sales_quotation->id) }}">{{ $sales_quotation->formulir->form_number}}</a>
                                </td>
                                <td>{{ $sales_quotation->formulir->notes }}</td>
                                <td>
                                    {!! get_url_person($sales_quotation->person_id) !!}
                                </td>
                                <td>
                                    @foreach($sales_quotation->items as $sales_quotation_item)
                                        {{ $sales_quotation_item->item->codeName }}
                                        = {{ number_format_quantity($sales_quotation_item->quantity) }} {{ $sales_quotation_item->unit }}
                                        <br/>
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $list_sales_quotation->render() !!}
                </div>
                @endif
            </div>
        </div>
    </div>
@stop
