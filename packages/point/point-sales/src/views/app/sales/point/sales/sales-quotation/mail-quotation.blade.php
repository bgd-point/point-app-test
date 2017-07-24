@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-sales::app.sales.point.sales._breadcrumb')
            <li><a href="{{ url('sales/point/indirect/sales-quotation') }}">Sales Quotation</a></li>
            <li>Mail Quotation</li>
        </ul>
        <h2 class="sub-header">Sales Quotation</h2>
        @include('point-sales::app.sales.point.sales.sales-quotation._menu')

        <form action="{{url('sales/point/indirect/sales-quotation/send-mail-quotation')}}" method="post">
            {!! csrf_field() !!}

            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="table-responsive">
                        {!! $list_sales_quotation->render() !!}
                        <table class="table table-striped table-bordered">
                            <thead>
                            <tr>
                                <th class="text-center">
                                    <input type="checkbox" id="check-all">
                                </th>
                                <th>Form Date</th>
                                <th>Form Number</th>
                                <th>Customer</th>
                                <th>Order</th>
                                <th>Last Sent</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list_sales_quotation as $sales_quotation)
                                <tr id="list-{{$sales_quotation->formulir_id}}">
                                    <td class="text-center">
                                        <input type="checkbox" name="formulir_id[]"
                                               value="{{$sales_quotation->formulir_id}}">
                                    </td>
                                    <td>{{ date_format_view($sales_quotation->formulir->form_date) }}</td>
                                    <td>
                                        <a href="{{ url('sales/point/indirect/sales-quotation/'.$sales_quotation->id) }}">{{ $sales_quotation->formulir->form_number}}</a>
                                    </td>
                                    <td>
                                        <a href="{{ url('master/contact/customer/'.$sales_quotation->person_id) }}">{{ $sales_quotation->person->codeName}}</a>
                                    </td>
                                    <td>
                                        @foreach($sales_quotation->items as $sales_quotation_item)
                                            {{ $sales_quotation_item->item->codeName }}
                                            = {{ number_format_quantity($sales_quotation_item->quantity) }} {{ $sales_quotation_item->unit }}
                                            <br/>
                                        @endforeach
                                    </td>
                                    <td>
                                        @if($sales_quotation->send_mail_at != null)
                                            {{ date_format_view($sales_quotation->send_mail_at, true) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {!! $list_sales_quotation->render() !!}
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-effect-ripple btn-primary">Send Mail</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@stop
