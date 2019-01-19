@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-manufacture::app.manufacture.point.output._breadcrumb')
            <li>show</li>
        </ul>
        <h2 class="sub-header">{{$process->name}} | Output</h2>
        @include('point-manufacture::app.manufacture.point.output._menu')

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
                                    @include('framework::app.include._form_status_label', ['form_status' => $output->formulir->form_status])
                                </div>
                            </div>
                        </fieldset>


                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> reference</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">form date</label>

                                <div class="col-md-6 content-show">
                                    {{ \DateHelper::formatView($input->formulir->form_date, true) }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">form number</label>

                                <div class="col-md-6 content-show">
                                    <a href="{{ url('manufacture/point/process-io/'. $process->id . '/input/'. $input->id ) }}">{{ $input->formulir->form_number }}</a>
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
                                <label class="col-md-3 control-label">revision</label>

                                <div class="col-md-6 content-show">
                                    {{ $revision }}
                                </div>
                            </div>
                        @endif
                        <div class="form-group">
                            <label class="col-md-3 control-label">form number</label>

                            <div class="col-md-6 content-show">
                                {{ $output->formulir->form_number ? $output->formulir->form_number : $output->formulir->archived }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">form date</label>

                            <div class="col-md-6 content-show">
                                {{ \DateHelper::formatView($output->formulir->form_date, true) }}
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label">notes</label>

                            <div class="col-md-6 content-show">
                                {{ $output->notes }}
                            </div>
                        </div>

                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> finished goods</legend>
                                </div>
                            </div>
                        </fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table id="item-datatable" class="table table-striped">
                                        <thead>
                                        <tr>
                                            <th>item</th>
                                            <th>estimation</th>
                                            <th>quantity produced</th>
                                            <th>warehouse</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($output->product as $detail)
                                            <tr>
                                                <td>
                                                    {{ $detail->item->codeName }}
                                                </td>
                                                <td>
                                                    <?php $qty_input = Point\PointManufacture\Models\InputProduct::where('input_id',
                                                            $output->input_id)->where('product_id',
                                                            $detail->product_id)->first(); ?>
                                                    {{ \NumberHelper::formatQuantity($qty_input->quantity) }} {{ $qty_input->unit }}
                                                </td>
                                                <td>
                                                    {{ \NumberHelper::formatQuantity($detail->quantity) }} {{$detail->unit}}
                                                </td>
                                                <td>
                                                    {{$detail->warehouse->codeName}}</li>
                                                </td>
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
                                    <legend><i class="fa fa-angle-right"></i> person in charge</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">form creator</label>

                                <div class="col-md-6 content-show">
                                    {{ $output->formulir->createdBy->name }}
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>

                <div class="tab-pane" id="block-tabs-settings">
                    @if($output->formulir->form_status != -1)
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> Action</legend>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <a href="{{url('manufacture/point/process-io/'. $output->input->process_id . '/output/'. $output->id.'/edit')}}"
                                       class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger"
                                       onclick="secureCancelForm('{{url('formulir/cancel')}}' , {{$output->formulir_id}}, 'delete.point.manufacture.output')"><i
                                                class="fa fa-times"></i> Cancel</a>
                                </div>
                            </div>
                        </fieldset>
                    @endif

                    @if($list_output_archived->count() > 0)
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
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php $count = 1;?>
                                            @foreach($list_output_archived as $output_archived)
                                                <tr>
                                                    <td class="text-center">
                                                        <a href="{{ url('manufacture/point/process-io/'. $output_archived->input->process_id . '/output/'.$output_archived->formulir_id.'/archived') }}"
                                                           data-toggle="tooltip" title="Show"
                                                           class="btn btn-effect-ripple btn-xs btn-info"><i
                                                                    class="fa fa-file"></i> {{ 'Revision ' . $count++ }}
                                                        </a>
                                                    </td>
                                                    <td>{{ \DateHelper::formatView($output->formulir->form_date) }}</td>
                                                    <td>{{ $output_archived->formulir->archived }}</td>
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
