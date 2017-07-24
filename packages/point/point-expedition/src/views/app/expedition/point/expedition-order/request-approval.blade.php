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
            @include('point-expedition::app/expedition/point/expedition-order/_breadcrumb')
            <li>Request Approval</li>
        </ul>
        <h2 class="sub-header">Expedition Order</h2>
        @include('point-expedition::app.expedition.point.expedition-order._menu')

        <form action="{{url('expedition/point/expedition-order/send-request-approval')}}" method="post">
            {!! csrf_field() !!}

            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="table-responsive">
                        {!! $list_expedition_order->render() !!}
                        <table class="table table-striped table-bordered">
                            <thead>
                            <tr>
                                <th width="100px" class="text-center">
                                    <input type="checkbox" id="check-all">
                                </th>
                                <th>Form Date</th>
                                <th>Form Number</th>
                                <th>Expedition</th>
                                <th>Order</th>
                                <th>Last Request</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list_expedition_order as $expedition_order)
                                <tr id="list-{{$expedition_order->formulir_id}}">
                                    <td class="text-center">
                                        <input type="checkbox" name="formulir_id[]" value="{{$expedition_order->formulir_id}}">
                                    </td>
                                    <td>{{date_format_view($expedition_order->formulir->form_date, true)}}</td>
                                    <td>{{$expedition_order->formulir->form_number}}</td>
                                    <td>{{ $expedition_order->expedition->codeName }}</td>
                                    <td>
                                        @foreach($expedition_order->items as $expedition_order_item)
                                            {{ $expedition_order_item->item->codeName }}
                                            = {{ number_format_quantity($expedition_order_item->quantity) }} {{ $expedition_order_item->unit }}
                                            <br/>
                                        @endforeach
                                    </td>
                                    <td>
                                        @if($expedition_order->formulir->request_approval_at)
                                            {{ date_format_view($expedition_order->formulir->request_approval_at, true) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {!! $list_expedition_order->render() !!}
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
