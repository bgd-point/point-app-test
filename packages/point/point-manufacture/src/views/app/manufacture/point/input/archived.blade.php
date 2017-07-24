@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-manufacture::app.manufacture.point.input._breadcrumb')
            <li>Archived</li>
        </ul>
        <h2 class="sub-header"> Manufacture | Process In</h2>
        @include('point-manufacture::app.manufacture.point.input._menu')

        @include('core::app.error._alert')

        <div class="block full">
            <div class="form-horizontal form-bordered">
                <div class="form-group">
                    <div class="col-md-12">
                        <div class="alert alert-danger alert-dismissable">
                            <h1 class="text-center"><strong>Archived</strong></h1>
                        </div>
                    </div>
                </div>
                
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Form</legend>
                        </div>
                    </div>
                </fieldset>
                <div class="form-group">
                    <label class="col-md-3 control-label">Form number</label>

                    <div class="col-md-6 content-show">
                        {{ $input_archived->formulir->archived }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Form date</label>

                    <div class="col-md-6 content-show">
                        {{ \DateHelper::formatView($input_archived->formulir->form_date, false) }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Machine</label>

                    <div class="col-md-6 content-show">
                        {{ $input_archived->machine->name }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Notes</label>

                    <div class="col-md-6 content-show">
                        {{ $input_archived->formulir->notes }}
                    </div>
                </div>

                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Finished goods</legend>
                        </div>
                    </div>
                </fieldset>
                <div class="form-group">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table id="item-datatable" class="table table-striped">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th>Item</th>
                                    <th class="text-right">Quantity</th>
                                    <th>Unit</th>
                                    <th>Warehouse</th>
                                </tr>
                                </thead>
                                <tbody class="manipulate-row">
                                @foreach($input_archived->product as $product)
                                    <tr>
                                        <td></td>
                                        <td>{{ $product->item->codeName }}</td>
                                        <td class="text-right">{{ \NumberHelper::formatQuantity($product->quantity) }}</td>
                                        <td>{{ $product->unit }}</td>
                                        <td>{{ $product->warehouse->codeName }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> raw material</legend>
                        </div>
                    </div>
                </fieldset>

                <div class="form-group">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table id="item-datatable" class="table table-striped">
                                <thead>
                                <tr>
                                    <th>Item</th>
                                    <th class="text-right">quantity</th>
                                    <th>Unit</th>
                                    <th>Warehouse</th>
                                </tr>
                                </thead>
                                <tbody class="manipulate-row">
                                @foreach($input_archived->material as $material)
                                    <tr>
                                        <td>{{ $material->item->codeName }}</td>
                                        <td class="text-right">{{ \NumberHelper::formatQuantity($material->quantity) }}</td>
                                        <td>{{ $material->unit }}</td>
                                        <td>{{ $material->warehouse->codeName }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Person in charge</legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form creator</label>

                        <div class="col-md-6 content-show">
                            {{ $input_archived->formulir->createdBy->name }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Approval to</label>

                        <div class="col-md-6 content-show">
                            {{ $input_archived->formulir->approvalTo->name }}
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
                        <label class="col-md-3 control-label">Approval Status</label>

                        <div class="col-md-6 content-show">
                            @if($input_archived->formulir->approval_status == -1)
                                <label class="label label-danger">Rejected</label> <i
                                        class="fa fa-calendar"></i> {{ date('d M Y', strtotime($input_archived->formulir->approval_at)) }}
                                <hr>
                                {{$input_archived->formulir->approval_message}}
                            @elseif($input_archived->formulir->approval_status == 0)
                                <label class="label label-warning">Pending</label>
                            @elseif($input_archived->formulir->approval_status == 1)
                                <label class="label label-success">Approved</label> <i
                                        class="fa fa-calendar"></i> {{ date('d M Y', strtotime($input_archived->formulir->approval_at)) }}
                                <hr>
                                {{$input_archived->formulir->approval_message}}
                            @endif
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Status</label>

                        <div class="col-md-6 content-show">
                            @if($input_archived->formulir->approval_status == 0)
                                <label class="label label-warning">Pending</label>
                            @elseif($input_archived->formulir->approval_status == 1)
                                <label class="label label-success">Done</label>
                            @elseif($input_archived->formulir->approval_status == -1)
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
