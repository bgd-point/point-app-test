@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-manufacture::app.manufacture.point.input._breadcrumb')
            <li>show</li>
        </ul>
        <h2 class="sub-header">{{$process->name}} | Input</h2>
        @include('point-manufacture::app.manufacture.point.input._menu')

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
                                        'approval_status' => $input->formulir->approval_status,
                                        'approval_message' => $input->formulir->approval_message,
                                        'approval_at' => $input->formulir->approval_at,
                                        'approval_to' => $input->formulir->approvalTo->name,
                                    ])
                                    @include('framework::app.include._form_status_label', ['form_status' => $input->formulir->form_status])

                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Input form</legend>
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
                                {{ $input->formulir->form_number ? $input->formulir->form_number : $input->formulir->archived }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form date</label>

                            <div class="col-md-6 content-show">
                                {{ \DateHelper::formatView($input->formulir->form_date, true) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Machine</label>

                            <div class="col-md-6 content-show">
                                {{ $input->machine->name }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Notes</label>

                            <div class="col-md-6 content-show">
                                {{ $input->formulir->notes }}
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Finished goods</legend>
                            </div>
                        </div>
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
                                        @foreach($input->product as $product)
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

                                        </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Raw material</legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                        <tr>
                                            <th></th>
                                            <th>Item</th>
                                            <th class="text-right">quantity</th>
                                            <th>unit</th>
                                            <th>warehouse</th>
                                        </tr>
                                        </thead>
                                        <tbody class="manipulate-row">
                                        @foreach($input->material as $material)
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
                                        <tr>

                                        </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> person in charge</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">form creator</label>

                                <div class="col-md-6 content-show">
                                    {{ $input->formulir->createdBy->name }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">approval to</label>

                                <div class="col-md-6 content-show">
                                    {{ $input->formulir->approvalTo->name }}
                                </div>
                            </div>
                        </fieldset>

                    </div>
                </div>

                <div class="tab-pane" id="block-tabs-settings">
                    @if($input->form_status != -1)
                        @if(formulir_is_not_locked($input->formulir_id))
                            <fieldset>
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <legend><i class="fa fa-angle-right"></i> Action</legend>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <!-- <a href="{{url('manufacture/point/process-io/'. $input->process_id .'/input/'.$input->id.'/edit')}}"
                                           class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                                        <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                           onclick="secureCancelForm('{{url('formulir/cancel')}}',
                                           {{$input->formulir_id}},'delete.point.manufacture.input')">
                                            <i class="fa fa-times"></i> Cancel</a> -->
                                    </div>
                                </div>
                            </fieldset>
                        @endif
                        @if(formulir_view_approval($input->formulir, 'approval.point.manufacture.input'))
                            <fieldset>
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <legend><i class="fa fa-angle-right"></i> Approval Action</legend>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-md-6">
                                        <form action="{{url('manufacture/point/input/'.$input->id.'/approve')}}"
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
                                        <form action="{{url('manufacture/point/input/'.$input->id.'/reject')}}"
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
                    @endif

                    @if($list_input_archived->count() > 0)
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
                                                <th>form date</th>
                                                <th>form number</th>
                                                <th>Created By</th>
                                                <th>Updated By</th>
                                                <th>Reason</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php $count = 1;?>
                                            @foreach($list_input_archived as $input_archived)
                                                <tr>
                                                    <td class="text-center">
                                                        <a href="{{ url('manufacture/point/process-io/'. $input->process_id .'/input/'.$input_archived->id.'/archived') }}"
                                                           data-toggle="tooltip" title="Show"
                                                           class="btn btn-effect-ripple btn-xs btn-info"><i
                                                                    class="fa fa-file"></i> {{ 'Revision ' . $count++ }}
                                                        </a>
                                                    </td>
                                                    <td>{{ \DateHelper::formatView($input->formulir->form_date) }}</td>
                                                    <td>{{ $input_archived->formulir->archived }}</td>
                                                    <td>{{ $input_archived->formulir->createdBy->name }}</td>
                                                    <td>{{ $input_archived->formulir->updatedBy->name }}</td>
                                                    <td>{{ $input_archived->formulir->edit_notes }}</td>
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
