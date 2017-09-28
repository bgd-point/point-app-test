@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.service._breadcrumb')
            <li>Report</li>
        </ul>
        <h2 class="sub-header">Report</h2>

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{ url('purchasing/point/service/report') }}" method="get" class="form-horizontal">
                    <div class="form-group">
                        <div class="col-sm-4">
                            <div class="input-group input-daterange" data-date-format="{{date_format_masking()}}">
                                <input type="text" name="date_from" id="date-from" class="form-control date input-datepicker"
                                        placeholder="From"
                                        value="{{\Input::get('date_from') ? \Input::get('date_from') : ''}}">
                                <span class="input-group-addon"><i class="fa fa-chevron-right"></i></span>
                                <input type="text" name="date_to" id="date-to" class="form-control date input-datepicker"
                                        placeholder="To"
                                        value="{{\Input::get('date_to') ? \Input::get('date_to') : ''}}">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <input type="text" name="search" id="search" class="form-control"
                                    placeholder="Search Form Number / Supplier..." value="{{\Input::get('search')}}"
                                    value="{{\Input::get('search') ? \Input::get('search') : ''}}">
                        </div>
                        <div class="col-sm-3">
                            <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search </button>
                            @if(access_is_allowed_to_view('export.point.purchasing.service.report'))
                            <a class="btn btn-effect-ripple btn-effect-ripple btn-info button-export" onclick="exportExcel()">Export to excel</a>
                            @endif
                        </div>
                    </div>
                </form>

                <br/>

                <div class="table-responsive">
                    {!! $list_invoice->appends(['search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Form Number</th>
                            <th>Supplier</th>
                            <th>Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $total = 0; ?>
                        @foreach($list_invoice as $invoice)
                            <?php $total += $invoice->total; ?>
                            <tr id="list-{{$invoice->formulir_id}}">
                                <td>{{ date_format_view($invoice->formulir->form_date) }}
                                    <br>
                                    <i class="fa fa-caret-down"></i>
                                    <a data-toggle="collapse" href="#collapse{{$invoice->formulir_id}}">
                                        <small>Details</small>
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ url('purchasing/point/service/invoice/'.$invoice->id) }}">{{ $invoice->formulir->form_number}}</a>
                                </td>
                                <td>
                                    <a href="{{ url('master/contact/'. $invoice->person->type->name . '/' . $invoice->person_id) }}">{{ $invoice->person->codeName}}</a>
                                </td>
                                <td class="text-right">{{number_format_quantity($invoice->total, 0)}}</td>
                            </tr>
                            <tr>
                                <td colspan="5" style="border-top: none;">
                                    <div id="collapse{{$invoice->formulir_id}}"
                                         class="panel-collapse collapse">
                                        <b>Details</b>
                                        <ul class="list-group">
                                            <?php
                                            $invoice_service = Point\PointPurchasing\Models\Service\InvoiceService::where('point_purchasing_service_invoice_id', $invoice->point_purchasing_service_invoice_id)->get();
                                            ?>
                                            @foreach($invoice_service as $service)
                                                <li class="list-group-item">
                                                    <small>{{ $service->service->name }}
                                                        # {{ number_format_quantity($service->quantity, 0) }}
                                                        <span class="pull-right">{{ number_format_quantity($service->price * $service->quantity, 0) }}</span>
                                                    </small>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="4" class="text-right"><h4><strong>Total of purchasing ({{count($list_invoice)}})</strong></h4></td>
                            <td class="text-right"><h4><strong>{{number_format_quantity($total)}}</strong></h4></td>
                        </tr>
                        </tbody>
                    </table>
                    {!! $list_invoice->appends(['search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
<script type="text/javascript">
    function exportExcel() {
        var spinner = ' <i class="fa fa-spinner fa-spin" style="font-size:16px;"></i>';
        var date_from = $("#date-from").val();
        var date_to = $("#date-to").val();
        var search = $("#search").val();
        $(".button-export").html(spinner);
        $(".button-export").addClass('disabled');
        $.ajax({
            url: '{{url("purchasing/point/service/report/export")}}',
            data: {
                date_from: date_from,
                date_to: date_to,
                search: search
            },
            success: function(result) {
                console.log(result);
                $(".button-export").removeClass('disabled');
                $(".button-export").html('Export to excel');
                notification('export data success, please check your email in a few moments');
            }, error:  function (result) {
                $(".button-export").removeClass('disabled');
                $(".button-export").html('Export to excel');
                notification('export data failed, please try again');
            }

        });
    }
</script>
@stop
