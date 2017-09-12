@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('bumi-shares::app/facility/bumi-shares/_breadcrumb')
            <li>Stock Report</li>
        </ul>
        <h2 class="sub-header">Stock Report</h2>

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{ url('facility/bumi-shares/report/stock') }}" method="get" class="form-horizontal no-print">
                    <div class="form-group">
                        <div class="col-md-3">
                            <select name="group_id" name="group-id" class="selectize" style="width: 100%;" data-placeholder="Filter by Group ..">
                                <option value="0">All</option>
                                @foreach($list_owner_group as $owner_group_select)
                                    <option @if(app('request')->input('group_id') == $owner_group_select->id) selected @endif value="{{$owner_group_select->id}}">{{$owner_group_select->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="shares_id" id="shares-id" class="selectize" style="width: 100%;" data-placeholder="Filter by Shares ..">
                                <option value="0">All</option>
                                @foreach($list_shares as $shares_select)
                                    <option @if(app('request')->input('shares_id') == $shares_select->id) selected @endif value="{{$shares_select->id}}">{{$shares_select->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-1">
                            <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search</button>
                        </div>
                        <div class="col-sm-5 text-right">
                            @if(auth()->user()->may('export.bumi.shares.report'))
                            <a href="javascript:void(0)" id="export" onclick="generateExcel()" class="btn btn-info">Export to excel</a>
                            <a href="javascript:void(0)" onclick="pagePrint('stock/print?group_id={{app('request')->input('group_id')}}&shares_id={{app('request')->input('shares_id')}}');" class="btn btn-info">Print</a>
                            @endif
                            <a href="{{ url('facility/bumi-shares/report/stock/estimate-of-selling-price') }}" class="btn btn-info">Estimate of Selling Price</a>
                        </div>
                    </div>
                </form>

                <br/>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered" style="white-space: nowrap; ">
                        <?php
                        $groups = $list_stock_shares->groupBy('shares_id')->get();
                        ?>
                        <tbody>
                        @foreach($groups as $report_group)
                        <?php
                            $acc_remaining_quantity=0;
                            $acc_subtotal=0;
                            $acc_total=0;
                        ?>

                            <tr>
                                <td class="text-center" colspan="10">Shares "{{ $report_group->shares->name }}"</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold">Form Date</td>
                                <td style="font-weight: bold">Group</td>
                                <td style="font-weight: bold">Owner</td>
                                <td style="font-weight: bold">Broker</td>
                                <td class="text-right" style="font-weight: bold">Fee</td>
                                <td class="text-right" style="font-weight: bold">Quantity</td>
                                <td class="text-right" style="font-weight: bold">Price</td>
                                <td class="text-right" style="font-weight: bold">Ex Sale</td>
                                <td class="text-right" style="font-weight: bold">Total</td>
                                <td class="text-right" style="font-weight: bold">Total + Fee</td>
                            </tr>
                            @foreach(\Point\BumiShares\Models\Stock::where(function($q) use ($report_group, $group) {
                                $q->where('shares_id','=',$report_group->shares_id);
                                if($group) {
                                    $q->where('owner_group_id','=',$group->id);
                                }
                            })->get() as $stock)

                            <?php
                                $subtotal = $stock->remaining_quantity * $stock->price;
                                $total = $stock->remaining_quantity * $stock->price + ($stock->remaining_quantity * $stock->price * $stock->fee / 100);
                                $acc_remaining_quantity += $stock->remaining_quantity; // total remaining quantity
                                $acc_subtotal += $subtotal; // subtotal is price * quantity
                                $acc_total += $total; // total is subtotal + fee
                            ?>
                            <tr>
                                <td><a href="{{url('facility/bumi-shares/report/stock/detail/'.$stock->formulir_id.'/'.$report_group->shares_id)}}"> {{ date_format_view($stock->date) }} </a></td>
                                <td>{{ $stock->ownerGroup->name }}</td>
                                <td>{{ $stock->owner->name }}</td>
                                <td>{{ $stock->broker->name }}</td>
                                <td class="text-right">{{ number_format_quantity($stock->reference($stock->formulir_id)->fee) }}</td>
                                <td class="text-right">{{ number_format_quantity($stock->remaining_quantity) }}</td>
                                <td class="text-right">{{ number_format_quantity($stock->price) }}</td>
                                <td class="text-right">{{ number_format_quantity($stock->average_price) }}</td>
                                <td class="text-right">{{ number_format_quantity($subtotal) }}</td>
                                <td class="text-right">{{ number_format_quantity($total) }}</td>
                            </tr>
                            @endforeach
                        <?php $average_price = $acc_total / $acc_remaining_quantity; ?>
                        <tr>
                            <td colspan="5"></td>
                            <td class="text-right"><b>{{ number_format_quantity($acc_remaining_quantity) }}</b></td>
                            <td class="text-right"><b>{{ number_format_quantity($average_price) }}</b></td>
                            <td></td>
                            <td class="text-right"><b>{{ number_format_quantity($acc_subtotal) }}</b></td>
                            <td class="text-right"><b>{{ number_format_quantity($acc_total) }}</b></td>
                        </tr>
                        <tr>
                            <td colspan="10">
                                <?php
                                $total_quantity += $acc_remaining_quantity;
                                $total_value += $acc_total;
                                $estimate_price = 0;
                                $estimate = Point\BumiShares\Models\SellingPrice::where('shares_id', '=', $stock->shares_id)->first();

                                if ($estimate) {
                                    $estimate_price = $estimate->price;
                                }
                                $total_sales = $acc_remaining_quantity * $estimate_price;
                                $profit_and_loss = $total_sales - $acc_total;
                                $estimation_of_selling_value += $total_sales;
                                $estimation_of_profit_and_loss += $profit_and_loss;
                                ?>

                                <b>Estimation of Profit and Loss</b> <br/>
                                Estimation of Selling Price : {{ number_format_quantity($estimate_price) }} <br/>
                                Estimation of Sales : {{ number_format_quantity($total_sales) }} <br/>
                                Profit & Loss : {{ number_format_quantity($profit_and_loss) }}
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-right" style="font-size: 20px;font-weight: bold;">Total Quantity</td>
                                <td class="text-right" style="font-size: 20px;font-weight: bold;">{{ number_format_quantity($total_quantity) }}</td>
                                <td colspan="3" class="text-right" style="font-size: 20px;font-weight: bold;">Total + Fee</td>
                                <td class="text-right" style="font-size: 20px;font-weight: bold;">{{ number_format_quantity($total_value) }}</td>
                            </tr>
                            <tr>
                                <td colspan="9" class="text-right">Total Estimation of Sales</td>
                                <td class="text-right">{{ number_format_quantity($estimation_of_selling_value) }}</td>
                            </tr>
                            <tr>
                                <td colspan="9" class="text-right">Profit And Loss</td>
                                <td class="text-right">{{ number_format_quantity($estimation_of_profit_and_loss) }}</td>
                            </tr>
                        </tfoot>
                    </table>
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
            }, true);
        }

        function generateExcel(){
            var shares_id = $('#shares-id option:selected').val();
            var group_id = $('#group-id option:selected').val();
            $('#export').html('<i class="fa fa-spinner fa-spin" style="font-size:24px;"></i>');
            $('#export').addClass('disabled');
            $.ajax({
                url:'{{url("facility/bumi-shares/report/stock/export")}}?shares_id='+shares_id+'&group_id='+group_id,
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
