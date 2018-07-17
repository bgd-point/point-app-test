@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-sales::app/sales/point/pos/_breadcrumb')
        <li>Daily Sales</li>
    </ul>
    <h2 class="sub-header">Point of Sales | Daily Sales</h2>
    @include('point-sales::app.sales.point.pos._menu')

    <div class="panel panel-default">
        <div class="panel-body">
            <div class="table-responsive">
                {!! $list_sales->render() !!}
                @if(auth()->user()->may('export.point.sales.pos.daily.report'))
                <a href="{{ url('sales/point/pos/daily-sales/export') }}" class="btn btn-info">
                    Export to excel
                </a>
                @endif
                @if(auth()->user()->may('read.point.sales.pos.daily.report'))
                <a class="btn btn-effect-ripple btn-effect-ripple btn-info button-export" id="btn-pdf" href="{{url('sales/point/pos/daily-sales/pdf')}}"> export to PDF</a>
                @endif
                <br><br>
                <table class="table tabble-striped table-bordered" cellpadding="0" cellspacing="0" border="0" >
                    <thead>
                        <tr>
                            <th></th>
                            <th>Form Number</th>
                            <th>Form Date</th>
                            <th>Customer</th>
                            <th>Sales</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $total_sales = 0;?>
                        @if( !count($list_sales) )
                        <tr>
                            <td colspan="5" class="text-center"><i>No Record of Sales</i></td>
                        </tr>
                        @endif

                        @foreach($list_sales as $sales)
                        <tr id="list-{{$sales->id}}" @if($sales->formulir->form_status == -1) style="text-decoration: line-through;" @endif>
                            <td class="text-center">
                                <a href="#" onclick="pagePrint('/sales/point/pos/print/{{$sales->id}}');" data-toggle="tooltip" title="Print" class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-print"></i></a>
                            </td>
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
                        @foreach($list_retur as $retur)
                            <tr>
                                <td class="text-center"><button class="btn btn-danger btn-xs">Retur</button></td>
                                <td><a href="{{ url('sales/point/pos/'.$retur->pos->id) }}" data-toggle="tooltip" title="Show">{{ $retur->pos->formulir->form_number }}</a></td>
                                <td>{{ date_format_view($retur->form_date, true) }}</td>
                                <td>{{ $retur->customer->codeName }}</td>
                                <td>{{ $retur->createdBy->name }}</td>
                                <td class="text-right">- {{ number_format_accounting($retur->total) }}</td>
                            </tr>
                        @endforeach
                    </tbody> 
                    <tfoot>
                        @if($total_retur > 0)
                            <tr>
                                <td colspan="5" class="text-right"><strong>TOTAL SALES</strong></td>
                                <td class="text-right"><strong>{{ number_format_accounting($total_sales) }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-right"><strong>Total Retur</strong></td>
                                <td class="text-right"><strong>- {{ number_format_accounting($total_retur) }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-right"><strong>Total</strong></td>
                                <td class="text-right"><strong>{{ number_format_accounting($total_sales - $total_retur) }}</strong></td>
                            </tr>
                        @else
                            <tr>
                                <td colspan="5" class="text-right"><strong>TOTAL</strong></td>
                                <td class="text-right"><strong>{{ number_format_accounting($total_sales) }}</strong></td>
                            </tr>
                        @endif
                    </tfoot>
                </table>
                {!! $list_sales->render() !!}
            </div>
        </div>
    </div>  
</div>
@stop

@section('scripts')
<script>
    function pagePrint(url){
        var printWindow = window.open( url, 'Print', 'left=200, top=200, width=950, height=500, toolbar=0, resizable=0');
        printWindow.addEventListener('load', function(){
            printWindow.print();
            printWindow.close();
        }, true);
    }
</script>
@stop
