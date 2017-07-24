@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-manufacture::app.manufacture.point.formula._breadcrumb')
            <li>Archived</li>
        </ul>
        <h2 class="sub-header">Formula </h2>
        @include('point-manufacture::app.manufacture.point.formula._menu')

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
                        {{ $formula_archived->formulir->form_number ? $formula_archived->formulir->form_number : $formula_archived->formulir->archived }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Form date</label>

                    <div class="col-md-6 content-show">
                        {{ \DateHelper::formatView($formula_archived->formulir->form_date, true) }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Process</label>

                    <div class="col-md-6 content-show">
                        {{ $formula_archived->process->name }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Name</label>

                    <div class="col-md-6 content-show">
                        {{ $formula_archived->name }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Notes</label>

                    <div class="col-md-6 content-show">
                        {{ $formula_archived->notes }}
                    </div>
                </div>

                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Finished Goods</legend>
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
                                @foreach($formula_archived->product as $product)
                                    <tr>
                                        <td></td>
                                        <td>{{ $product->item->codeName }}</td>
                                        <td class="text-right">{{ number_format_quantity($product->quantity) }}</td>
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
                            <legend><i class="fa fa-angle-right"></i> Raw Material</legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="item-datatable" class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th class="text-right">Quantity</th>
                                        <th>Unit</th>
                                        <th>Warehouse</th>
                                    </tr>
                                    </thead>
                                    <tbody class="manipulate-row">
                                    @foreach($formula_archived->material as $material)
                                        <tr>
                                            <td>{{ $material->item->codeName }}</td>
                                            <td class="text-right">{{ number_format_quantity($material->quantity) }}</td>
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
                </fieldset>

                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Person in charge</legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Creator</label>

                        <div class="col-md-6 content-show">
                            {{ $formula_archived->formulir->createdBy->name }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Ask approval to</label>

                        <div class="col-md-6 content-show">
                            {{ $formula_archived->formulir->approvalTo->name }}
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
                            @include('framework::app.include._approval_status_label_detailed', [
                                'approval_status' => $formula_archived->formulir->approval_status,
                                'approval_message' => $formula_archived->formulir->approval_message,
                                'approval_at' => $formula_archived->formulir->approval_at,
                                'approval_to' => $formula_archived->formulir->approvalTo->name,
                            ])
                        </div>                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Status</label>
                        <div class="col-md-6 content-show">
                            @include('framework::app.include._form_status_label', ['form_status' => $formula_archived->formulir->form_status])
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
