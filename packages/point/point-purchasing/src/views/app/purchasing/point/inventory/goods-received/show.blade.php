@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li><a href="{{ url('purchasing/point/goods-received') }}">Goods Received</a></li>
            <li>Show</li>
        </ul>
        <h2 class="sub-header">Goods Received</h2>
        @include('point-purchasing::app.purchasing.point.inventory.goods-received._menu')

        @include('core::app.error._alert')

        <div class="block full">
            <!-- Block Tabs Title -->
            <div class="block-title">
                <ul class="nav nav-tabs" data-toggle="tabs">
                    <li class="active"><a href="#block-tabs-home">Form</a></li>
                    <li><a href="#block-tabs-settings"><i class="gi gi-settings"></i></a></li>
                </ul>
            </div>
            <!-- END Block Tabs Title -->

            <!-- Tabs Content -->
            <div class="tab-content">
                <div class="tab-pane active" id="block-tabs-home">
                    <div class="form-horizontal form-bordered">

                        <fieldset>
                            <div class="form-group pull-right">
                                <div class="col-md-12">
                                    @include('framework::app.include._approval_status_label', [
                                        'approval_status' => $goods_received->formulir->approval_status,
                                        'approval_message' => $goods_received->formulir->approval_message,
                                        'approval_at' => $goods_received->formulir->approval_at,
                                        'approval_to' => $goods_received->formulir->approvalTo->name,
                                    ])
                                    @include('framework::app.include._form_status_label', ['form_status' => $goods_received->formulir->form_status])
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Info Reference</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Form Date</label>

                                <div class="col-md-6 content-show">
                                    {{ date_format_view($reference->formulir->form_date, true) }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Form Number</label>

                                <div class="col-md-6 content-show">
                                    {{ $reference->formulir->form_number }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Supplier</label>
                                <div class="col-md-6 content-show">
                                    {!! get_url_person($goods_received->supplier_id) !!}
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Goods Received Form</legend>
                                </div>
                            </div>
                        </fieldset>
                        @if($revision)
                            <div class="form-group">
                                <label class="col-md-3 control-label">Revision</label>

                                <div class="col-md-6 content-show">
                                    {{ $revision }}
                                </div>
                            </div>
                        @endif
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Number</label>

                            <div class="col-md-6 content-show">
                                {{ $goods_received->formulir->form_number }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Date</label>

                            <div class="col-md-6 content-show">
                                {{ date_format_view($goods_received->formulir->form_date, true) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Warehouse</label>

                            <div class="col-md-6 content-show">
                                {{ $goods_received->warehouse->codeName }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Driver</label>

                            <div class="col-md-6 content-show">
                                {{ $goods_received->driver }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">License Plate</label>

                            <div class="col-md-6 content-show">
                                {{ $goods_received->license_plate }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Notes</label>

                            <div class="col-md-6 content-show">
                                {{ $goods_received->formulir->notes }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Referenced By</label>
                            <div class="col-md-6 content-show">
                                @foreach($list_referenced as $referenced)
                                    <?php
                                    $model = $referenced->locking->formulirable_type;
                                    $url = $model::showUrl($referenced->locking->formulirable_id);
                                    ?>
                                    <a href="{{ url($url) }}">{{ $referenced->locking->form_number }}</a> <br/>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Reference</label>
                            <div class="col-md-6 content-show">
                                @foreach($list_reference as $reference)
                                    {!! formulir_url($reference->lockedForm) !!}<br/>
                                @endforeach
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
                                                <th>ITEM</th>
                                                <th class="text-right">QUANTITY</th>
                                                <th>UNIT</th>
                                            </tr>
                                            </thead>
                                            <tbody class="manipulate-row">
                                            @foreach($goods_received->items as $goods_received_item)
                                                <tr>
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
                                    {{ $goods_received->formulir->createdBy->name }} ({{ date_format_view($goods_received->formulir->created_at, true) }})
                                </div>
                            </div>
                        </fieldset>

                    </div>
                </div>

                <div class="tab-pane" id="block-tabs-settings">

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Action</legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                @if(formulir_view_edit($goods_received->formulir, 'update.point.purchasing.goods.received'))
                                    <a href="{{url('purchasing/point/goods-received/'.$goods_received->id.'/edit')}}"
                                       class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                                @endif
                                @if(formulir_view_cancel($goods_received->formulir, 'delete.point.purchasing.goods.received'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureCancelForm('{{url('formulir/cancel')}}',
                                               '{{ $goods_received->formulir_id }}',
                                               'delete.point.purchasing.goods.received')"><i class="fa fa-times"></i> Cancel
                                        Form</a>
                                @endif
                                @if(formulir_view_close($goods_received->formulir, 'update.point.purchasing.goods.received'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureCloseForm({{$goods_received->formulir_id}},'{{url('formulir/close')}}')">Close
                                        Form</a>
                                @endif
                                @if(formulir_view_reopen($goods_received->formulir, 'update.point.purchasing.goods.received'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureReopenForm({{$goods_received->formulir_id}},'{{url('formulir/reopen')}}')">Reopen
                                        Form</a>
                                @endif
                            </div>
                        </div>
                    </fieldset>

                    @if(formulir_view_approval($goods_received->formulir, 'approval.point.purchasing.goods.received'))
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Approval Action</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-6">
                                    <form action="{{url('formulir/'.$goods_received->formulir_id.'/approve')}}"
                                          method="post">
                                        {!! csrf_field() !!}
                                        <input type="text" name="approval_message" class="form-control"
                                               placeholder="Message">
                                        <hr/>
                                        <input type="submit" class="btn btn-primary" value="Approve">
                                    </form>
                                </div>
                                <div class="col-md-6">
                                    <form action="{{url('formulir/'.$goods_received->formulir_id.'/reject')}}"
                                          method="post">
                                        {!! csrf_field() !!}
                                        <input type="text" name="approval_message" class="form-control"
                                               placeholder="Message">
                                        <hr/>
                                        <input type="submit" class="btn btn-danger" value="Reject">
                                    </form>
                                </div>
                            </div>
                        </fieldset>
                    @endif

                    @if($list_goods_received_archived->count() > 0)
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Archived Form</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-12 content-show">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                            <tr>
                                                <th></th>
                                                <th>Form Date</th>
                                                <th>Form Number</th>
                                                <th>Created By</th>
                                                <th>Updated By</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php $count = 0;?>
                                            @foreach($list_goods_received_archived as $goods_received_archived)
                                                <tr>
                                                    <td class="text-center">
                                                        <a href="{{ url('purchasing/point/goods-received/'.$goods_received_archived->formulirable_id.'/archived') }}"
                                                           data-toggle="tooltip" title="Show"
                                                           class="btn btn-effect-ripple btn-xs btn-info"><i
                                                                    class="fa fa-file"></i> {{ 'Revision ' . $count++ }}
                                                        </a>
                                                    </td>
                                                    <td>{{ date_format_view($goods_received->formulir->form_date) }}</td>
                                                    <td>{{ $goods_received_archived->formulir->archived }}</td>
                                                    <td>{{ $goods_received_archived->formulir->createdBy->name }}</td>
                                                    <td>{{ $goods_received_archived->formulir->updatedBy->name }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    @endif
                </div>
            </div>
            <!-- END Tabs Content -->
        </div>
    </div>
@stop

@section('scripts')
    <style>
        tbody.manipulate-row:after {
            content: '';
            display: block;
            height: 100px;
        }
    </style>
    <script>
        var item_table = $('#item-datatable').DataTable({
            bSort: false,
            bPaginate: false,
            bInfo: false,
            bFilter: false,
            bScrollCollapse: false,
            scrollX: true
        });
    </script>
@stop
