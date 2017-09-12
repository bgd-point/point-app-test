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
                <form action="{{ url('facility/bumi-shares/report/sell') }}" method="get" class="form-horizontal no-print">
                    <div class="form-group">
                        <div class="col-md-5">
                            <div class="input-group input-daterange" data-date-format="{{date_format_masking()}}">
                                <input type="text" name="date_from" id="date-from" class="form-control date input-datepicker"
                                       placeholder="From" value="{{\Input::get('date_from') ? \Input::get('date_from') : ''}}">
                                <span class="input-group-addon"><i class="fa fa-chevron-right"></i></span>
                                <input type="text" name="date_to" id="date-to" class="form-control date input-datepicker"
                                       placeholder="To" value="{{\Input::get('date_to') ? \Input::get('date_to') : ''}}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select name="shares_id" id="shares-id" class="selectize" style="width: 100%;" data-placeholder="Filter by Shares ..">
                                <option value="0">All</option>
                                @foreach($list_shares as $shares_select)
                                    <option @if(app('request')->input('shares_id') == $shares_select->id) selected @endif value="{{$shares_select->id}}">{{$shares_select->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-3">
                            <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search</button>
                            <a href="javascript:void(0)" id="export" onclick="generateExcel()" class="btn btn-info">Export to excel</a>
                        </div>
                    </div>
                </form>
                <br><br>
                {!! $list_stock_fifo->render() !!}
                <div class="table-responsive">
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
            var shares_id = $('#shares-id option:selected').val();
            var date_from = $("#date-from").val();
            var date_to = $("#date-to").val();
            $('#export').html('<i class="fa fa-spinner fa-spin" style="font-size:24px;"></i>');
            $('#export').addClass('disabled');
            $.ajax({
                url:'{{url("facility/bumi-shares/report/sell/export")}}?shares_id='+shares_id+'&date_from='+date_from+'&date_to='+date_to,
                success: function (result) {
                    $('#export').html('Export to excel');
                    $('#export').removeClass('disabled');
                    if (result.status == 'success') {
                        notification('success, please check your email in few minutes');
                    }

                    console.log(result);
                },
                error: function (e) {
                    console.log(e);
                    $('#export').html('Export to excel');
                    $('#export').removeClass('disabled');
                    notification('failed, please try again');
                }
            })
        }
    </script>
@stop
