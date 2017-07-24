@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('bumi-shares::app/facility/bumi-shares/_breadcrumb')
            <li><a href="{{url('facility/bumi-shares/report/stock')}}">Stock Report</a></li>
            <li>Estimate of Selling Price</li>
        </ul>
        <h2 class="sub-header">Stock Report</h2>

        <div class="panel panel-default">
            <div class="panel-body">
                <div class="table-responsive">
                    <form action="{{url('facility/bumi-shares/report/stock/estimate-of-selling-price')}}" method="post">
                        {!! csrf_field() !!}
                        <table class="table table-striped table-bordered" style="white-space: nowrap; ">
                            <thead>
                            <tr>
                                <th class="text-right">Shares</th>
                                <th>Estimate of Selling Price</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list_stock_shares as $stock_shares)
                                <?php
                                $price=0;
                                $perkiraan = Point\BumiShares\Models\SellingPrice::where('shares_id', '=', $stock_shares->id)->first();
                                if ($perkiraan) {
                                    $price = $perkiraan->price;
                                }
                                ?>

                                <tr>
                                    <td class="text-right">{{$stock_shares->name}}</td>
                                    <td class="text-right">
                                        <input type="hidden" name="shares_id[]" class="form-control format-price" value="{{ $stock_shares->id }}">
                                        <input type="text" name="price[]" class="form-control format-price" value="{{ $price }}">
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="2"><button type="submit" class="btn btn-primary pull-right">Submit</button></td>
                            </tr>
                            </tfoot>
                        </table>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
