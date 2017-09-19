@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-sales::app/sales/point/pos/_breadcrumb')
            <li>Report</li>
        </ul>
        <h2 class="sub-header">Point of Sales | Report</h2>
        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{ url('sales/point/pos/sales-report') }}" method="get" class="form-horizontal">
                    <div class="form-group">
                        <div class="col-sm-6">
                            <div class="input-group input-daterange" data-date-format="{{ date_format_masking()}}">
                                <input type="text" name="date_from" id="date-from" class="form-control date input-datepicker"  placeholder="Date from" value="{{\Input::get('date_from') ? \Input::get('date_from') : ''}}">
                                <span class="input-group-addon"><i class="fa fa-chevron-right"></i></span>
                                <input type="text" name="date_to" id="date-to" class="form-control date input-datepicker" placeholder="Date to" value="{{\Input::get('date_to') ? \Input::get('date_to') : ''}}">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <input type="text" name="search" id="search" class="form-control" value="{{\Input::get('search') ? \Input::get('search') : ''}}"  placeholder="Form Number / Item / Customer / Code of item or customer ...">
                        </div>
                        <div class="col-sm-3">
                            <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search</button>
                        </div>
                    </div>
                </form>
                <div class="table-responsive">
                    {!! $list_sales->appends(['search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
                    @if(auth()->user()->may('export.point.sales.pos.report'))
                    <a href="{{ url('sales/point/pos/sales-report/export?date_from='.\Input::get('date_from').'&date_to='.\Input::get('date_to').'&search='.\Input::get('search')) }}" class="btn btn-info">
                        Export to excel
                    </a>
                    @endif
                    @if(auth()->user()->may('read.point.sales.pos.report'))
                    <a class="btn btn-effect-ripple btn-effect-ripple btn-info button-export" id="btn-pdf" onclick="exportPDF()"> export to PDF</a>
                    @endif
                    <br><br>
                    <table class="table tabble-striped table-bordered" cellpadding="0" cellspacing="0" border="0" >
                        <thead>
                        <tr>
                            <th>Form Number</th>
                            <th>Form Date</th>
                            <th>Customer</th>
                            <th>Sales</th>
                            <th class="text-right">Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $total_sales = 0;?>
                        @foreach($list_sales as $sales)
                            <tr id="list-{{$sales->id}}" @if($sales->formulir->form_status == -1) style="text-decoration: line-through;" @endif>
                                <td><a href="{{ url('sales/point/pos/'.$sales->id) }}" data-toggle="tooltip" title="Show">{{ $sales->formulir->form_number }}</a></td>
                                <td>{{ date_format_view($sales->formulir->form_date, true) }}</td>
                                <td>{{ $sales->customer->codeName }}</td>
                                <td>{{ $sales->formulir->createdBy->name }}</td>
                                <td class="text-right">{{ number_format_accounting($sales->total) }}</td>
                            </tr>
                            @if($sales->formulir->form_status != -1)
                                <?php $total_sales += $sales->total;?>
                            @endif
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="4" class="text-right"><strong>Total</strong></td>
                            <td class="text-right"><strong>{{ number_format_accounting($total_sales) }}</strong></td>
                        </tr>
                        </tfoot>
                    </table>
                    {!! $list_sales->appends(['search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
<script type="text/javascript">
    function exportPDF() {
        var date_from = $("#date-from").val();
        var date_to = $("#date-to").val();
        var search = $("#search").val();
        var loader = '<i class="fa fa-spinner fa-spin" style="font-size:22px;"></i>';
        $('#btn-pdf').html(loader);
        $(".button-export").addClass('disabled');
        $.ajax({
            url: '{{url("sales/point/pos/sales-report/pdf")}}',
            data: {
                date_from: date_from,
                date_to: date_to,
                search: search
            },
            success: function(result) {
                console.log(result);
                $('#btn-pdf').html('Export to PDF');
                $(".button-export").removeClass('disabled');
                notification('export to PDF data success, please check your email in a few moments');
            }, error:  function (result) {
                $('#btn-pdf').html('Export to PDF');
                $(".button-export").removeClass('disabled');
                notification('export to PDF data failed, please try again');
            }

        });
    }
</script>
@stop
