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
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li><a href="{{ url('purchasing/point/cash-advance') }}">Cash Advance</a></li>
            <li>Request approval</li>
        </ul>
        <h2 class="sub-header">Cash Advance</h2>
        @include('point-purchasing::app.purchasing.point.inventory.cash-advance._menu')

        <form action="{{url('purchasing/point/cash-advance/send-request-approval')}}" method="post">
            {!! csrf_field() !!}

            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="table-responsive">
                        {!! $list_cash_advance->render() !!}
                        <table class="table table-striped table-bordered table-vcenter">
                            <thead>
                            <tr>
                                <th class="text-center">
                                    <input type="checkbox" id="check-all">
                                </th>
                                <th>Form Date</th>
                                <th>Form Number</th>
                                <th>Employee</th>
                                <th>Amount</th>
                                <th>Last Request</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list_cash_advance as $cash_advance)
                                <tr id="list-{{$cash_advance->formulir_id}}">
                                    <td class="text-center">
                                        <input type="checkbox" name="formulir_id[]" value="{{$cash_advance->formulir_id}}">
                                    </td>
                                    <td>{{ date_format_view($cash_advance->formulir->form_date) }}</td>
                                    <td>
                                        <a href="{{ url('purchasing/point/cash-advance/'.$cash_advance->id) }}">{{ $cash_advance->formulir->form_number}}</a>
                                    </td>
                                    <td>{!! get_url_person($cash_advance->employee->id) !!}</a>
                                    </td>
                                    <td>{{ number_format_quantity($cash_advance->amount) }}</td>
                                    <td>
                                        @if($cash_advance->formulir->request_approval_at != null)
                                            {{ date_format_view($cash_advance->formulir->request_approval_at, true) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {!! $list_cash_advance->render() !!}
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
