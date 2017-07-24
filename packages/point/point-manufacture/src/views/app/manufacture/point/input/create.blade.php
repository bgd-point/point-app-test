@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-manufacture::app.manufacture.point.input._breadcrumb')
            <li>create</li>
        </ul>
        <h2 class="sub-header">{{$process->name}} | Input</h2>
        @include('point-manufacture::app.manufacture.point.input._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('manufacture/point/process-io/'. $process->id .'/input')}}" method="post"
                      class="form-horizontal form-bordered">
                    {!! csrf_field() !!}

                    <input type="hidden" name="process_id" class="form-control" value="{{$process->id}}">
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
                                   data-date-format="{{\DateHelper::formatMasking()}}"
                                   placeholder="{{\DateHelper::formatMasking()}}"
                                   value="{{date(date_format_get(), strtotime(\Carbon::now()))}}">
                        </div>
                        <div class="col-md-3">
                            <div class="input-group bootstrap-timepicker">
                                <input type="text" id="time" name="time" class="form-control timepicker"
                                       value="{{old('time')}}">
                            <span class="input-group-btn">
                                <a href="javascript:void(0)" class="btn btn-effect-ripple btn-primary"><i class="fa fa-clock-o"></i></a>
                            </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Machine *</label>

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
                            <input type="text" name="notes" class="form-control" value="{{old('notes')}}">
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
                                        @for($counter=0; $counter<count(old('product_id')); $counter++)
                                        <tr>
                                            <td><a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a></td>
                                            <td>
                                                <select id="product-id-{{$counter}}" name="product_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.."
                                                    onchange="selectGoods(this.value, {{$counter}})">
                                                        <option value="{{old('product_id.'.$counter)}}">{{Point\Framework\Models\Master\Item::find(old('product_id.'.$counter))->name}}</option>
                                                </select>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input type="text" id="product-{{$counter}}" name="product_quantity[]" class="form-control format-quantity text-right"
                                                    value="{{ old('product_quantity.'.$counter) }}">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input type="text" name="product_unit_id[]" value="{{old('product_unit_id.'.$counter)}}" class="form-control input-unit-{{$counter}}" readonly>
                                                </div>
                                            </td>
                                            <td>
                                               <select id="warehouse-id-{{$counter}}" name="product_warehouse_id[]"
                                                       class="selectize" style="width: 100%;"
                                                       data-placeholder="Choose one..">'
                                                    @foreach($list_warehouse as $warehouse)
                                                        @if (old('product_warehouse_id.'.$counter) !== '')
                                                            <option @if(old('product_warehouse_id.'.$counter) == $warehouse->id) selected @endif value="{{$warehouse->id}}">{{$warehouse->codeName}}</option>
                                                        @else
                                                            <option></option>
                                                            <option value="{{$warehouse->id}}">{{$warehouse->codeName}}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                        @endfor
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td><input type="button" id="addProductRow" class="btn btn-primary" value="Add Item"></td>
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
                                <legend><i class="fa fa-angle-right"></i> Raw material</legend>
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
                                        @for($counter=0; $counter<count(old('material_id')); $counter++)
                                        <tr>
                                            <td><a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a></td>
                                            <td>
                                                <select id="material-id-{{$counter}}" name="material_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.."
                                                    onchange="selectItem(this.value, {{$counter}})">
                                                        <option value="{{old('material_id.'.$counter)}}">{{Point\Framework\Models\Master\Item::find(old('material_id.'.$counter))->name}}</option>
                                                </select>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input type="text" name="material_quantity[]" class="form-control format-quantity text-right"
                                                    value="{{ old('material_quantity.'.$counter) }}">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input type="text" name="material_unit[]" value="{{old('material_unit.'.$counter)}}" class="form-control input-unit-{{$counter}}" readonly>
                                                </div>
                                            </td>
                                            <td>
                                               <select id="warehouse-id-{{$counter}}" name="material_warehouse_id[]"
                                                            class="selectize" style="width: 100%;"
                                                            data-placeholder="Choose one..">'
                                                        @foreach($list_warehouse as $warehouse)
                                                            @if (old('material_warehouse_id.'.$counter) !== '')
                                                                <option @if(old('material_warehouse_id.'.$counter) == $warehouse->id) selected
                                                                        @endif value="{{$warehouse->id}}">{{$warehouse->codeName}}</option>
                                                            @else
                                                                <option></option>
                                                                <option value="{{$warehouse->id}}">{{$warehouse->codeName}}</option>
                                                            @endif
                                                        @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                        @endfor
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td><input type="button" id="addItemRow" class="btn btn-primary" value="Add Item"></td>
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

                        <div class="form-group">
                            <div class="col-md-10">
                                <legend><i class="fa fa-angle-right"></i> Person in charge</legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label"> form creator</label>

                            <div class="col-md-6 content-show">
                                {{\Auth::user()->name}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Ask approval to *</label>

                            <div class="col-md-6">
                                <select id="approval-to" name="approval_to" class="selectize" style="width: 100%;"
                                        data-placeholder="Choose one..">
                                    <option></option>
                                    @foreach($list_user_approval as $user_approval)

                                        @if($user_approval->may('approval.point.manufacture.formula'))
                                            <option value="{{$user_approval->id}}"
                                                    @if(old('approval_to') == $user_approval->id) selected @endif>{{$user_approval->name}}</option>
                                        @endif

                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </fieldset>

                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-3">
                            <button type="submit" class="btn btn-effect-ripple btn-primary">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@include('framework::scripts.item')
@stop

@extends('point-manufacture::app.manufacture.point.input._script')

