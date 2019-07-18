@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-sales::app.sales.point.sales._breadcrumb')
            <li>Retur Report</li>
        </ul>
        <h2 class="sub-header">Retur Report</h2>

        <div class="panel panel-default">
            <div class="panel-body">
                <div class="table-responsive">
                    {!! $listRetur->appends(['status'=>app('request')->get('status'), 'search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr class="th-head">
                            <th>Date</th>
                            <th>#</th>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($listRetur as $retur)
                            @foreach ($retur->items as $detail)
                             <tr class="row-detail" id="row_detail_{{$retur->id}}">
                                <td>{{ date_format_view($retur->formulir->form_date) }}</td>
                                <td>
                                    <a href="{{ url('sales/point/indirect/invoice/'.$retur->point_sales_invoice_id) }}">{{ $retur->formulir->form_number }}</a>
                                </td>
                                 <td>
                                     <a href="{{ url('sales/point/indirect/invoice/'.$retur->point_sales_invoice_id) }}">{{ $retur->invoice->formulir->form_number }}</a>
                                 </td>
                                <td>
                                    {!! get_url_person($retur->person_id) !!}
                                </td>
                                <td>
                                    [{{ $detail->item->code }}] {{ $detail->item->name }}
                                </td>
                                 <td>
                                     {{ number_format($detail->quantity) }}
                                 </td>
                                 <td>
                                     {{ number_format($detail->price) }}
                                 </td>
                                 <td>
                                     {{ number_format($detail->quantity * $detail->price) }}
                                 </td>
                            </tr>
                            @endforeach
                        @endforeach
                        </tbody>
                    </table>
                    {!! $listRetur->appends(['status'=>app('request')->get('status'), 'search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop

