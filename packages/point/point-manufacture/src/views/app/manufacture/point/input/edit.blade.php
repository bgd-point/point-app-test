@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-manufacture::app.manufacture.point.input._breadcrumb')
            <li>Edit</li>
        </ul>
        <h2 class="sub-header">{{$input->process->name}} | Input</h2>
        @include('point-manufacture::app.manufacture.point.input._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('manufacture/point/process-io/'. $process->id .'/input/'.$input->id)}}"
                      method="post" class="form-horizontal form-bordered">
                    {!! csrf_field() !!}
                    <input name="_method" type="hidden" value="PUT">

                    <input type="hidden" name="process_id" class="form-control" value="{{$input->process_id}}">
                    <input type="hidden" name="formula_id" class="form-control" value="{{$input->formula_id ? : ''}}">
                    <input type="hidden" name="action" value="edit">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Reason edit *</label>
                        <div class="col-md-6">
                            <input type="text" name="edit_notes" class="form-control" value="" autofocus>
                            <input type="hidden" name="action" value="edit">
                        </div>
                    </div>
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Edit Input</legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <label class="col-md-3 control-label">form date</label>

                        <div class="col-md-3">
                            <input type="text" name="form_date" class="form-control date input-datepicker"
                                   data-date-format="{{\DateHelper::formatGet()}}"
                                   placeholder="{{\DateHelper::formatGet()}}"
                                   value="{{ date(\DateHelper::formatGet(), strtotime($input->formulir->form_date)) }}">
                        </div>
                        <div class="col-md-3">
                            <div class="input-group bootstrap-timepicker">
                                <input type="text" id="time" name="time" class="form-control timepicker"
                                       value="{{ date('H:i', strtotime($input->formulir->form_date)) }}">
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
                                @foreach(Point\PointManufacture\Models\Machine::all() as $machine)
                                    <option @if($machine->id == $input->machine_id) selected
                                            @endif value="{{$machine->id}}">{{$machine->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">notes</label>

                        <div class="col-md-6">
                            <input type="text" name="notes" class="form-control" value="{{$input->formulir->notes}}">
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
                                    <?php $counter_product = 1;?>
                                    @foreach($input->product as $product)
                                        <tr>
                                            <td style="width:30px;">
                                            @if($input->formula_id == null)
                                                <a href="javascript:void(0)" class="remove-row btn btn-danger">
                                                <i class="fa fa-trash"></i></a>
                                                @endif
                                            </td>
                                            <td>
                                                <select id="product-id-{{$counter_product}}" name="product_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectProduct(this.value, {{$counter_product}})">
                                                    <option value="{{$product->product_id}}">
                                                        <?php echo Point\Framework\Models\Master\Item::find($product->product_id)->codeName;?>
                                                    </option>
                                                </select>
                                            </td>
                                            <td style="width:30px;" class="text-right"><input type="text"
                                                                                              name="product_quantity[]"
                                                                                              class="form-control format-quantity text-right"
                                                                                              value="{{ $product->quantity }}"/>
                                            </td>
                                            <td style="width:150px;">
                                                <select id="measure-id-{{$counter_product}}" name="product_unit_id[]"
                                                        class="selectize" style="width: 100%;"
                                                        data-placeholder="Choose one..">
                                                    @foreach(\Point\Framework\Models\Master\ItemUnit::where('item_id','=',$product->product_id)->get() as $unit)
                                                        <option value="{{$unit->id}}"
                                                                @if($unit->name == $product->unit) selected @endif>{{$unit->name}}</option>
                                                    @endforeach
                                                </select>
                                                <input type="hidden" name="product_unit_converter[]"
                                                       value="{{ $product->converter }}">
                                                <input type="hidden" name="product_unit_name[]"
                                                       value="{{ $product->unit }}">
                                            </td>
                                            <td style="width:150px;">
                                                <select id="warehouse-product-id-{{$counter_product}}"
                                                        name="product_warehouse_id[]" class="selectize"
                                                        style="width: 100%;" data-placeholder="Choose one..">'
                                                    @foreach($list_warehouse as $warehouse)
                                                        <option @if($product->warehouse_id == $warehouse->id) selected
                                                                @endif value="{{$warehouse->id}}">{{$warehouse->codeName}}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                        <?php $counter_product++;?>
                                    @endforeach
                                    </tbody>
                                    <tfoot>

                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    @if($input->formula_id == null)
                    <div class="form-group">
                        <div class="col-md-12">
                            <input type="button" id="addProductRow" class="btn btn-primary pull-left" value="Add item">
                        </div>
                    </div>
                    @endif
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
                                <table id="material-datatable" class="table table-striped">
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
                                    <?php $counter_material = 1;?>
                                    @foreach($input->material as $material)
                                        <tr>
                                            <td style="width:30px;">
                                            @if($input->formula_id == null)
                                                <a href="javascript:void(0)" class="remove-row btn btn-danger">
                                                <i class="fa fa-trash"></i></a>
                                                @endif
                                            </td>
                                            <td>
                                                <select id="material-id-{{$counter_material}}" name="material_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectGoods(this.value, {{$counter_material}})">
                                                    <option value="{{$material->material_id}}">
                                                        <?php echo Point\Framework\Models\Master\Item::find($material->material_id)->codeName;?>
                                                    </option>
                                                </select>
                                            </td>
                                            <td style="width:30px;" class="text-right"><input type="text"
                                                                                              name="material_quantity[]"
                                                                                              class="form-control format-quantity text-right"
                                                                                              value="{{ $material->quantity }}"/>
                                            </td>
                                            <td style="width:150px;">
                                                <input type="text" name="material_unit[]"
                                                       value="{{ $material->unit }}" class="form-control input-unit-{{$counter_material}}" readonly="">
                                            </td>
                                            <td style="width:150px;">
                                                <select id="warehouse-id-{{$counter_material}}" name="material_warehouse_id[]"
                                                        class="selectize" style="width: 100%;"
                                                        data-placeholder="Choose one..">'
                                                    @foreach($list_warehouse as $warehouse)
                                                        <option @if($material->warehouse_id == $warehouse->id) selected
                                                                @endif value="{{$warehouse->id}}">{{$warehouse->codeName}}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                        <?php $counter_material++;?>
                                    @endforeach
                                    </tbody>
                                    <tfoot>

                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    @if($input->formula_id == null)
                    <div class="form-group">
                        <div class="col-md-12">
                            <input type="button" id="addItemRow" class="btn btn-primary pull-left" value="Add Item">
                        </div>
                    </div>
                    @endif
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
                            <label class="col-md-3 control-label">Ask approval to *</label>

                            <div class="col-md-6">
                                <select id="approval-to" name="approval_to" class="selectize" style="width: 100%;"
                                        data-placeholder="Choose one..">
                                    <option value="{{$input->formulir->approval_to}}">{{ $input->formulir->approvalTo->name }}</option>
                                    @foreach($list_user_approval as $user_approval)

                                        @if($user_approval->may('approval.point.manufacture.input'))

                                            @if($input->approval_to != $user_approval->id)
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

@include('framework::scripts.item')
@extends('point-manufacture::app.manufacture.point.input._script-edit')

