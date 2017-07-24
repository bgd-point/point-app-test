@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li><a href="{{ url('purchasing/point/goods-received') }}">Goods Received</a></li>
            <li><a href="{{ url('purchasing/point/goods-received/'.$goods_received->id) }}">{{$goods_received->formulir->form_number}}</a></li>
            <li>Archived</li>
        </ul>
        <h2 class="sub-header">Goods Received </h2>
        @include('point-purchasing::app.purchasing.point.inventory.goods-received._menu')

        @include('core::app.error._alert')

        <div class="block full">
            <div class="form-horizontal form-bordered">
                @if($goods_received_archived->canceled_at != null)
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="alert alert-danger alert-dismissable">
                                <h1 class="text-center"><strong>Canceled</strong></h1>
                            </div>
                        </div>
                    </div>
                @endif

                @if($goods_received_archived->archived != null)
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="alert alert-danger alert-dismissable">
                                <h1 class="text-center"><strong>Archived</strong></h1>
                            </div>
                        </div>
                    </div>
                @endif

                @if($goods_received_archived->approval_status == 1)
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="alert alert-success alert-dismissable">
                                <h1 class="text-center"><strong>Approved</strong></h1>
                            </div>
                        </div>
                    </div>
                @endif

                @if($goods_received_archived->approval_status == -1)
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="alert alert-danger alert-dismissable">
                                <h1 class="text-center"><strong>Rejected</strong></h1>
                            </div>
                        </div>
                    </div>
                @endif

                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Formulir Goods Receive</legend>
                        </div>
                    </div>
                </fieldset>
                <div class="form-group">
                    <label class="col-md-3 control-label">Form Number</label>

                    <div class="col-md-6 content-show">
                        {{ $goods_received_archived->formulir->archived }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Form Date</label>

                    <div class="col-md-6 content-show">
                        {{ date_format_view($goods_received_archived->formulir->form_date, true) }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Warehouse</label>

                    <div class="col-md-6 content-show">
                        {{ $goods_received_archived->warehouse->codeName }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Driver</label>

                    <div class="col-md-6 content-show">
                        {{ $goods_received_archived->driver }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">License Plate</label>

                    <div class="col-md-6 content-show">
                        {{ $goods_received_archived->license_plate }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Notes</label>

                    <div class="col-md-6 content-show">
                        {{ $goods_received_archived->formulir->notes }}
                    </div>
                </div>
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Item</legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="item-datatable" class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th></th>
                                        <th>ITEM</th>
                                        <th class="text-right">QUANTITY</th>
                                        <th>UNIT</th>
                                    </tr>
                                    </thead>
                                    <tbody class="manipulate-row">
                                    @foreach($goods_received_archived->items as $goods_received_item)
                                        <tr>
                                            <td></td>
                                            <td>{{ $goods_received_item->item->codeName }}</td>
                                            <td class="text-right">{{ number_format_quantity($goods_received_item->quantity) }}</td>
                                            <td>{{ $goods_received_item->unit }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Authorized User</legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Creator</label>

                        <div class="col-md-6 content-show">
                            {{ $goods_received_archived->formulir->createdBy->name }}
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Status</legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Status</label>

                        <div class="col-md-6 content-show">
                            @if($goods_received_archived->approval_status == 0)
                                <label class="label label-warning">Pending</label>
                            @elseif($goods_received_archived->approval_status == 1)
                                <label class="label label-success">Done</label>
                            @elseif($goods_received_archived->approval_status == -1)
                                <label class="label label-danger">Canceled</label>
                            @endif
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script>
        initDatatable('#item-datatable');
    </script>
@stop
