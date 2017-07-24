@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-manufacture::app.manufacture.point.output._breadcrumb')
            <li>Edit</li>
        </ul>
        <h2 class="sub-header">{{$process->name}} | Output</h2>
        @include('point-manufacture::app.manufacture.point.output._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('manufacture/point/process-io/'. $process->id.'/output/'. $output->id)}}"
                      method="post" class="form-horizontal form-bordered">
                    {!! csrf_field() !!}
                    <input name="_method" type="hidden" value="PUT">
                    <input type="hidden" name="process_id" class="form-control" value="{{$output->process_id}}">
                    <input type="hidden" name="input_id" class="form-control" value="{{$output->input_id}}">

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> process in</legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">form date</label>

                            <div class="col-md-6 content-show">
                                {{ \DateHelper::formatView($input->formulir->form_date, true) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">reference</label>

                            <div class="col-md-6 content-show">
                                <a href="{{ url('manufacture/point/process/' . $input->process_id . '/input/'.$input->formulir_id) }}">{{ $input->formulir->form_number }}</a>
                            </div>
                            <input type="hidden" name="formulir_formula_id" value="{{$input->id}}"/>
                        </div>
                        <input type="hidden" name="close" value="1">
                    </fieldset>

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> process out</legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <label class="col-md-3 control-label">form date *</label>

                        <div class="col-md-3">
                            <input type="text" name="form_date" class="form-control date input-datepicker"
                                   data-date-format="{{\DateHelper::formatMasking()}}"
                                   placeholder="{{\DateHelper::formatMasking()}}"
                                   value="{{ date(date_format_get(), strtotime($output->formulir->form_date)) }}">
                        </div>
                        <div class="col-md-3">
                            <div class="input-group bootstrap-timepicker">
                                <input type="text" id="time" name="time" class="form-control timepicker"
                                       value="{{ date('H:i', strtotime($output->formulir->form_date)) }}">
                            <span class="input-group-btn">
                                <a href="javascript:void(0)" class="btn btn-effect-ripple btn-primary"><i
                                            class="fa fa-clock-o"></i></a>
                            </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">machine *</label>

                        <div class="col-md-6">
                            <select id="machine_id" name="machine_id" class="selectize" style="width: 100%;"
                                    data-placeholder="Choose one..">
                                <option></option>
                                @foreach($list_machine as $machine)
                                    <option @if($machine->id == $output->machine_id) selected
                                            @endif value="{{$machine->id}}">{{$machine->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">notes</label>

                        <div class="col-md-6">
                            <input type="text" name="notes" class="form-control" value="{{$output->notes}}">
                        </div>
                    </div>


                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> finished goods</legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="item-datatable" class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th>item</th>
                                        <th>Old Value</th>
                                        <th>Revision *</th>
                                        <th>warehouse</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $counter = 0; ?>

                                    @foreach($output->product as $product)
                                        <tr>
                                            <td>{{ $product->item->codeName }}</td>
                                            <td>
                                                <div class="col-md-3">
                                                    {{ \NumberHelper::formatQuantity($product->quantity) }}
                                                </div>
                                                <div class="col-md-3 ">
                                                    {{ $product->unit }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="col-md-4">
                                                    <input type="text" name="quantity_output-{{$counter}}"
                                                           class="form-control format-quantity"
                                                           value="{{$product->quantity }}">
                                                </div>
                                                <div class="col-md-2 content-show">
                                                    {{$product->unit}}
                                                </div>
                                            </td>
                                            <td>{{$product->warehouse->name}}</td>
                                        </tr>
                                        <?php $counter++;?>
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
                                {{\Auth::user()->name}}
                                <input type="hidden" name="approval_to" value="{{$output->formulir->approval_to}}"/>
                            </div>
                        </div>
                    </fieldset>

                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-3">
                            <button type="submit" class="btn btn-effect-ripple btn-primary">submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
