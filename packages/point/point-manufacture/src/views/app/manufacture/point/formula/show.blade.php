@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-manufacture::app.manufacture.point.formula._breadcrumb')
            <li>Show</li>
        </ul>
        <h2 class="sub-header">Manufacture | formula</h2>
        @include('point-manufacture::app.manufacture.point.formula._menu')

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
                                        'approval_status' => $formula->formulir->approval_status,
                                        'approval_message' => $formula->formulir->approval_message,
                                        'approval_at' => $formula->formulir->approval_at,
                                        'approval_to' => $formula->formulir->approvalTo->name,
                                    ])
                                    @include('framework::app.include._form_status_label', ['form_status' => $formula->formulir->form_status])
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Form</legend>
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
                            <label class="col-md-3 control-label">Form number</label>

                            <div class="col-md-6 content-show">
                                {{ $formula->formulir->form_number ? $formula->formulir->form_number : $formula->formulir->archived }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form date</label>

                            <div class="col-md-6 content-show">
                                {{ \DateHelper::formatView($formula->formulir->form_date, true) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Process</label>

                            <div class="col-md-6 content-show">
                                {{ $formula->process->name }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Name</label>

                            <div class="col-md-6 content-show">
                                {{ $formula->name }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Notes</label>

                            <div class="col-md-6 content-show">
                                {{ $formula->notes }}
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
                                    <table class="table table-striped">
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
                                        @foreach($formula->product as $product)
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

                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>


                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Raw material</legend>
                                </div>
                            </div>
                        </fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-striped">
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
                                        @foreach($formula->material as $material)
                                            <tr>
                                                <td></td>
                                                <td>{{ $material->item->codeName }}</td>
                                                <td class="text-right">{{ \NumberHelper::formatQuantity($material->quantity) }}</td>
                                                <td>{{ $material->unit }}</td>
                                                <td>{{ $material->warehouse->codeName }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                        <tfoot>

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
                                    {{ $formula->formulir->createdBy->name }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Approval to</label>

                                <div class="col-md-6 content-show">
                                    {{ $formula->formulir->approvalTo->name }}
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
                                @if(formulir_view_edit($formula->formulir, 'update.point.manufacture.formula'))
                                    <a href="{{url('manufacture/point/formula/'.$formula->id.'/edit')}}"
                                       class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                                @endif
                                @if(formulir_view_cancel($formula->formulir, 'delete.point.manufacture.formula'))
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureCancelForm('{{url('formulir/cancel')}}' ,
                                       {{$formula->formulir_id}},
                                               'delete.point.manufacture.formula')"><i class="fa fa-times"></i>
                                        cancel</a>
                                @endif
                            </div>
                        </div>
                    </fieldset>
                    @if(formulir_view_approval($formula->formulir, 'approval.point.manufacture.formula'))
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Approval Action</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-6">
                                    <form action="{{url('manufacture/point/formula/'.$formula->id.'/approve')}}"
                                          method="post">
                                        {!! csrf_field() !!}
                                        <div class="input-group">
                                            <input type="text" name="approval_message" class="form-control"
                                                   placeholder="Message">
                                    <span class="input-group-btn">
                                        <input type="submit" class="btn btn-primary" value="Approve">
                                    </span>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-md-6">
                                    <form action="{{url('manufacture/point/formula/'.$formula->id.'/reject')}}"
                                          method="post">
                                        {!! csrf_field() !!}
                                        <div class="input-group">
                                            <input type="text" name="approval_message" class="form-control"
                                                   placeholder="Message">
                                    <span class="input-group-btn">
                                        <input type="submit" class="btn btn-danger" value="Reject">
                                    </span>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </fieldset>
                    @endif


                    @if($list_formula_archived->count() > 0)
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
                                                <th>Date</th>
                                                <th>Nomer</th>
                                                <th>Created By</th>
                                                <th>Updated By</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php $count = 0;?>
                                            @foreach($list_formula_archived as $formula_archived)
                                                <tr>
                                                    <td class="text-center">
                                                        <a href="{{ url('manufacture/point/formula/'.$formula_archived->formulir->formulirable_id.'/archived') }}"
                                                           data-toggle="tooltip" title="Show"
                                                           class="btn btn-effect-ripple btn-xs btn-info"><i
                                                                    class="fa fa-file"></i> {{ 'Revision ' . $count++ }}
                                                        </a>
                                                    </td>
                                                    <td>{{ \DateHelper::formatView($formula->formulir->form_date) }}</td>
                                                    <td>{{ $formula_archived->formulir->archived }}</td>
                                                    <td>{{ $formula_archived->formulir->createdBy->name }}</td>
                                                    <td>{{ $formula_archived->formulir->updatedBy->name }}</td>
                                                    <td>{{ $formula_archived->formulir->edit_notes }}</td>
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
    <script>
        initDatatable('#item-datatable');
    </script>
@stop
