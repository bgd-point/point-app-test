@extends('core::app.layout')

@section('scripts')
    <script>
        $("#check-all").change(function () {
            $("input:checkbox").prop('checked', $(this).prop("checked"));
        });
    </script>
@stop

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-sales::app.sales.point.sales._breadcrumb')
            <li><a href="{{ url('sales/point/indirect/invoice') }}">Invoice</a></li>
            <li>Request approval</li>
        </ul>
        <h2 class="sub-header">Invoice</h2>
        @include('point-sales::app.sales.point.sales.invoice._menu')

        <form action="{{url('sales/point/indirect/invoice/send-invoice')}}" method="post">
            {!! csrf_field() !!}

            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="table-responsive">
                        {!! $list_invoice->render() !!}
                        <table class="table table-striped table-bordered">
                            <thead>
                            <tr>
                                <th width="100px" class="text-center">
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
                            @foreach($list_invoice as $invoice)
                                <tr id="list-{{$invoice->formulir_id}}">
                                    <td class="text-center">
                                        <input type="checkbox" name="formulir_id[]" value="{{$invoice->formulir_id}}">
                                    </td>
                                    <td>{{ date_format_view($invoice->formulir->form_date) }}</td>
                                    <td>
                                        <a href="{{ url('sales/point/indirect/invoice/'.$invoice->id) }}">{{ $invoice->formulir->form_number}}</a>
                                    </td>
                                    <td>
                                        <a href="{{ url('master/contact/person/'.$invoice->person_id) }}">{{ $invoice->person->codeName}}</a>
                                    </td>
                                    <td>
                                        @foreach($invoice->items as $invoice_item)
                                            {{ $invoice_item->item->codeName }}
                                            = {{ number_format_quantity($invoice_item->quantity) }} {{ $invoice_item->unit }}
                                            <br/>
                                        @endforeach
                                    </td>
                                    <td>
                                        @if($invoice->send_mail_at != null)
                                            {{ date_format_view($invoice->send_mail_at, true) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {!! $list_invoice->render() !!}
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-effect-ripple btn-primary">Send Invoice</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@stop
