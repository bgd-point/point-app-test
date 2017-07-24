@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-manufacture::app.manufacture.point.input._breadcrumb')
            <li>Use Formula</li>
        </ul>
        <h2 class="sub-header">{{$process->name}} | Input</h2>
        @include('point-manufacture::app.manufacture.point.input._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('manufacture/point/process-io/'. $process->id .'/input')}}" method="post" class="form-horizontal form-bordered">
                    {!! csrf_field() !!}

                    <input type="hidden" name="process_id" class="form-control" value="{{$process->id}}">
                    <input type="hidden" name="formula_id" value="{{ $formula->id }}">
                    <input type="hidden" name="action" value="create">

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Form</legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form date *</label>

                        <div class="col-md-3">
                            <input type="text" name="form_date" class="form-control date input-datepicker"
                                   data-date-format="{{\DateHelper::formatGet()}}"
                                   placeholder="{{\DateHelper::formatGet()}}"
                                   value="{{ date(\DateHelper::formatGet(), strtotime($formula->formulir->form_date)) }}">
                        </div>
                        <div class="col-md-3">
                            <div class="input-group bootstrap-timepicker">
                                <input type="text" id="time" name="time" class="form-control timepicker"
                                       value="{{ date('H:i', strtotime($formula->formulir->form_date)) }}">
                                <span class="input-group-btn">
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-primary">
                                        <i class="fa fa-clock-o"></i>
                                    </a>
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
                                @foreach(Point\PointManufacture\Models\Machine::all() as $machine)
                                    <option @if($machine->id == old('machine_id')) selected @endif value="{{$machine->id}}">{{$machine->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">notes</label>

                        <div class="col-md-6">
                            <input type="text" name="notes" class="form-control" value="{{$formula->formulir->notes}}">
                        </div>
                    </div>

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Finished Goods</legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group" style="height:220px; overflow:auto;">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="product-datatable" class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th style="width: 2%"></th>
                                        <th style="width: 25%">Item *</th>
                                        <th style="width: 25%">Quantity *</th>
                                        <th style="width: 23%">Unit *</th>
                                        <th style="width: 25%">Warehouse *</th>
                                    </tr>
                                    </thead>
                                    <tbody class="manipulate-row">
                                    @foreach($formula->product as $product)
                                        <tr>
                                            <td></td>
                                            <td>
                                                <input type="hidden" name="product_id[]" value="{{ $product->item->id }}">
                                                {{ $product->item->codeName }}
                                            </td>
                                            <td class="text-right">
                                                <input type="text"
                                                       name="product_quantity[]"
                                                       class="form-control format-quantity text-right"
                                                       value="{{ $product->quantity }}"/>
                                            </td>
                                            <td>
                                                <input type="hidden" name="product_unit[]" value="{{ \Point\Framework\Models\Master\Item::defaultUnit($product->product_id)->name }}">
                                                {{ \Point\Framework\Models\Master\Item::defaultUnit($product->product_id)->name }}
                                            </td>
                                            <td>
                                                <input type="hidden" name="product_warehouse_id[]" value="{{ $product->warehouse_id }}">
                                                {{ $product->warehouse->codeName }}
                                            </td>
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
                                <legend><i class="fa fa-angle-right"></i> material</legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group" style="height:220px; overflow:auto;">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="item-datatable" class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th style="width: 2%"></th>
                                        <th style="width: 25%">Item *</th>
                                        <th style="width: 25%">Quantity *</th>
                                        <th style="width: 23%">Unit *</th>
                                        <th style="width: 25%">Warehouse *</th>
                                    </tr>
                                    </thead>
                                    <tbody class="manipulate-row">
                                    @foreach($formula->material as $material)
                                        <tr>
                                            <td></td>
                                            <td>
                                                <input type="hidden" name="material_id[]" value="{{ $material->item->id }}">
                                                {{ $material->item->codeName }}
                                            </td>
                                            <td class="text-right">
                                                <input type="text"
                                                       name="material_quantity[]"
                                                       class="form-control format-quantity text-right"
                                                       value="{{ $material->quantity }}"/>
                                            </td>
                                            <td>
                                                <input type="hidden" name="material_unit[]" value="{{ \Point\Framework\Models\Master\Item::defaultUnit($material->material_id)->name }}">
                                                {{ \Point\Framework\Models\Master\Item::defaultUnit($material->material_id)->name }}
                                            </td>
                                            <td>
                                                <input type="hidden" name="material_warehouse_id[]" value="{{ $material->warehouse_id }}">
                                                {{ $material->warehouse->codeName }}
                                            </td>
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
                                <legend><i class="fa fa-angle-right"></i> Person in charge</legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">form creator</label>

                            <div class="col-md-6 content-show">
                                {{ auth()->user()->name }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Ask approval to *</label>

                            <div class="col-md-6">
                                <select id="approval-to" name="approval_to" class="selectize" style="width: 100%;"
                                        data-placeholder="Choose one..">
                                    <option value="{{$formula->formulir->approval_to}}">{{ $formula->formulir->approvalTo->name }}</option>
                                    @foreach($list_user_approval as $user_approval)
                                        @if($user_approval->may('approval.point.manufacture.input'))
                                            @if($formula->approval_to != $user_approval->id)
                                                <option value="{{$user_approval->id}}">{{$user_approval->name}}</option>
                                            @endif
                                        @endif
                                    @endforeach
                                </select>
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

@extends('point-manufacture::app.manufacture.point.input._script')

