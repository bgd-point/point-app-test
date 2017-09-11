@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('bumi-shares::app/facility/bumi-shares/_breadcrumb')
            <li>Stock Report</li>
        </ul>
        <h2 class="sub-header">Sell Report</h2>

        <div class="panel panel-default">
            <div class="panel-body">
                <div class="col-sm-12">
                    <a href="javascript:void(0)" id="export" onclick="generateExcel()" class="btn btn-info">Export to excel</a>
                </div>
                <br><br>
                {!! $list_stock_fifo->render() !!}
                <div class="table-responsive col-sm-12">
                    <table class="table table-striped table-bordered" style="white-space: nowrap; ">
                        <tbody>
                            <tr>
                                <td style="font-weight: bold">Shares Name</td>
                                <td style="font-weight: bold">Purchase Date</td>
                                <td class="text-right" style="font-weight: bold">Quantity</td>
                                <td class="text-right" style="font-weight: bold">Ex Sale</td>
                                <td class="text-right" style="font-weight: bold">Nominal Purchase</td>
                                <td style="font-weight: bold">Broker</td>
                                <td style="font-weight: bold">Sale Date</td>
                                <td class="text-right" style="font-weight: bold">Quantity</td>
                                <td class="text-right" style="font-weight: bold">Price</td>
                                <td class="text-right" style="font-weight: bold">Total + Fee</td>
                                <td class="text-right" style="font-weight: bold">Profit / Lost</td>
                            </tr>
                            @foreach($list_stock_fifo as $stock)
                            <?php
                            $sell = Point\BumiShares\Models\Sell::where('formulir_id', $stock->shares_out_id)->first();
                            $buy = Point\BumiShares\Models\Buy::where('formulir_id', $stock->shares_in_id)->first();
                            if (!$stock->quantity) {
                                continue;
                            }
                            $total_plus_fee = $stock->price * $stock->quantity + ($stock->price * $stock->quantity * $sell->fee / 100);
                            ?>
                            <tr>
                                <td>{{ $sell->shares->name}}</td>
                                <td>{{ date_format_view($buy->formulir->form_date) }}</td>
                                <td class="text-right">{{ number_format_quantity($stock->quantity) }}</td>
                                <td class="text-right">{{ number_format_quantity($stock->average_price) }}</td>
                                <td class="text-right">{{ number_format_quantity($stock->quantity * $buy->price) }}</td>
                                <td>{{ $sell->broker->name}}</td>
                                <td>{{ date_format_view($sell->formulir->form_date) }}</td>
                                <td class="text-right">{{ number_format_quantity($stock->quantity) }}</td>
                                <td class="text-right">{{ number_format_quantity($stock->price) }}</td>
                                <td class="text-right">{{ number_format_quantity($total_plus_fee) }}</td>
                                <td class="text-right">{{ number_format_quantity($total_plus_fee - $stock->quantity * $buy->price)}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {!! $list_stock_fifo->render() !!}
            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script>
        function generateExcel(){
            $('#export').html('<i class="fa fa-spinner fa-spin" style="font-size:24px;"></i>');
        }
    </script>
@stop
