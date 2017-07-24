@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-manufacture::app.manufacture.point.output._breadcrumb')
            <li>Create step 2</li>
        </ul>
        <h2 class="sub-header">Manufacture | process out</h2>
        @include('point-manufacture::app.manufacture.point.output._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('manufacture/point/process-io/'. $process->id.'/output' )}}" method="post" class="form-horizontal form-bordered">
                    {!! csrf_field() !!}

                    <input type="hidden" name="process_id" class="form-control" value="{{$process->id}}">

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
                                <a href="{{ url('manufacture/point/process-io/'. $input->process_id .'/input/'.$input->id) }}">{{ $input->formulir->form_number }}</a>
                            </div>
                            <input type="hidden" name="input_id" value="{{$input->id}}"/>
                        </div>
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
                                   value="{{date(date_format_get(), strtotime(\Carbon::now()))}}">
                        </div>
                        <div class="col-md-3">
                            <div class="input-group bootstrap-timepicker">
                                <input type="text" id="time" name="time" class="form-control timepicker">
                                <span class="input-group-btn">
                                    <a href="javascript:void(0)"
                                       class="btn btn-effect-ripple btn-primary">
                                        <i class="fa fa-clock-o"></i>
                                    </a>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">machine *</label>

                        <div class="col-md-6">
                            <select id="machine-id" name="machine_id" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                <option></option>
                                @foreach($list_machine as $machine)
                                    <option @if($machine->id == $input->machine_id) selected @endif value="{{$machine->id}}">
                                        {{$machine->name}}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">notes</label>

                        <div class="col-md-6">
                            <input type="text" name="notes" class="form-control" value="{{$input->notes}}">
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
                                            <th>Item</th>
                                            <th>Estimation</th>
                                            <th>Output *</th>
                                            <th>Warehouse</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($input->product as $product)
                                        <tr>
                                            <td>{{ $product->item->codeName }}</td>
                                            <td>
                                                {{ \NumberHelper::formatQuantity($product->quantity) }} {{ $product->unit }}
                                            </td>
                                            <td>
                                                <input type="text" name="quantity_output[]" class="form-control format-quantity" value="{{$product->quantity}}">
                                            </td>
                                            <td>{{$product->warehouse->codeName}}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
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
                                <input type="hidden" name="approval_to" value="{{$input->formulir->approval_to}}"/>
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
