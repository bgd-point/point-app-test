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
            @include('point-purchasing::app.purchasing.point.service._breadcrumb')
            <li><a href="{{ url('purchasing/point/service/invoice') }}">Invoice</a></li>
            <li>Request approval</li>
        </ul>
        <h2 class="sub-header">Invoice</h2>
        @include('point-purchasing::app.purchasing.point.service.invoice._menu')

        <form action="{{url('purchasing/point/service/invoice/send-request-approval')}}" method="post">
            {!! csrf_field() !!}

            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="table-responsive">
                        {!! $list_invoice->render() !!}
                        <table class="table table-striped table-bordered">
                            <thead>
                            <tr>
                                <th class="text-center">
                                    <input type="checkbox" id="check-all">
                                </th>
                                <th>Form Date</th>
                                <th>Form Number</th>
                                <th>Supplier</th>
                                <th>Amount</th>
                                <th>Last Request</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list_invoice as $invoice)
                                <tr id="list-{{$invoice->formulir_id}}">
                                    <td class="text-center">
                                        <input type="checkbox" name="formulir_id[]"
                                               value="{{$invoice->formulir_id}}">
                                    </td>
                                    <td>{{ date_format_view($invoice->formulir->form_date) }}</td>
                                    <td>
                                        <a href="{{ url('purchasing/point/service/invoice/'.$invoice->id) }}">
                                            {{ $invoice->formulir->form_number}}
                                        </a>
                                    </td>
                                    <td>
                                        {!! get_url_person($invoice->person->id) !!}
                                    </td>
                                    <td>{{ number_format_quantity($invoice->total) }}</td>
                                    <td>
                                        @if($invoice->formulir->request_approval_at != null)
                                            {{ date_format_view($invoice->formulir->request_approval_at, true) }}
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
                            <button type="submit" class="btn btn-effect-ripple btn-primary">Send Request</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@stop
