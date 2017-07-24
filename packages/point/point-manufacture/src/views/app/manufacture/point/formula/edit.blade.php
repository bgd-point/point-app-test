@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-manufacture::app.manufacture.point.formula._breadcrumb')
            <li>Edit</li>
        </ul>
        <h2 class="sub-header">Manufacture | Formula</h2>
        @include('point-manufacture::app.manufacture.point.formula._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('manufacture/point/formula/'.$formula->formulir_id)}}" method="post"
                      class="form-horizontal form-bordered">
                    {!! csrf_field() !!}
                    <input name="_method" type="hidden" value="PUT">
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
                                <input type="text" id="time" name="time" class="form-control timepicker" value="{{ date('H:i', strtotime($formula->formulir->form_date)) }}">
                                <span class="input-group-btn">
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-primary"><i class="fa fa-clock-o"></i></a>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Process *</label>

                        <div class="col-md-6">
                            <select name="process_id" class="selectize" style="width: 100%;"
                                    data-placeholder="Choose one..">
                                @foreach($list_process as $process)
                                    <option @if($process->id == $formula->process_id) selected
                                            @endif value="{{$process->id}}">{{$process->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Name *</label>

                        <div class="col-md-6">
                            <input type="text" name="name" class="form-control" value="{{$formula->name}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>

                        <div class="col-md-6">
                            <input type="text" name="notes" class="form-control" value="{{$formula->notes}}">
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
                                    <?php $counter_product = 0;?>
                                    @foreach($formula->product as $product)
                                        <tr>
                                            <td>
                                                <a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a>
                                            </td>
                                            <td>
                                                <select id="product-id-{{$counter_product}}" name="product_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectProduct(this.value, {{$counter_product}})">
                                                    <option selected value="{{$product->product_id}}">{{$product->item->codeName}}</option>
                                                </select>
                                            </td>
                                            <td class="text-right">
                                                <input type="text" name="product_quantity[]" class="form-control text-right format-quantity" value="{{ $product->quantity }}"/>
                                            </td>
                                            <td style="width:150px;">
                                                <input type="hidden" name="product_unit_converter[]" value="{{ $product->converter }}">
                                                <input type="text" name="product_unit_id[]" id="product-unit-{{$counter_product}}" value="{{ $product->unit }}" class="form-control" readonly="">
                                            </td>
                                            <td>
                                                <select id="warehouse-product-id-{{$counter_product}}" name="product_warehouse_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
                                                    @foreach($list_warehouse as $warehouse)
                                                        <option @if($product->warehouse_id == $warehouse->id) selected @endif value="{{$warehouse->id}}">{{$warehouse->codeName}}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                        <?php $counter_product++;?>
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
                    <div class="form-group">
                        <div class="col-md-12">
                            <input type="button" id="addProductRow" class="btn btn-primary pull-left" value="Add Item">
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
                                    <?php $counter_material = 0;?>
                                    @foreach($formula->material as $material)
                                        <tr>
                                            <td><a href="javascript:void(0)" class="remove-row btn btn-danger"><i
                                                            class="fa fa-trash"></i></a></td>
                                            <td>
                                                <select id="material-id-{{$counter_material}}" name="material_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectItem(this.value, {{$counter_material}})">
                                                    <option selected value="{{$material->material_id}}">{{$material->item->codeName}}</option>
                                                </select>
                                            </td>
                                            <td class="text-right">
                                                <input type="text" name="material_quantity[]" class="form-control format-quantity text-right" value="{{ $material->quantity }}"/>
                                            </td>
                                            <td>
                                                <input type="hidden" name="item_unit_converter[]" value="{{ $material->converter }}">
                                                <input type="text" name="material_unit[]" id="material-unit-{{$counter_product}}" value="{{ $material->unit }}" class="form-control" readonly="">
                                            </td>
                                            <td>
                                                <select id="warehouse-material-id-{{$counter_material}}" name="material_warehouse_id[]"
                                                        class="selectize" style="width: 100%;"
                                                        data-placeholder="Choose one..">'
                                                    @foreach($list_warehouse as $warehouse)
                                                        <option @if($material->warehouse_id == $warehouse->id) selected @endif value="{{$warehouse->id}}">{{$warehouse->codeName}}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                        <?php $counter_material++;?>
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
                    <div class="form-group">
                        <div class="col-md-12">
                            <input type="button" id="addItemRow" class="btn btn-primary pull-left" value="Add Item">
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
                            <label class="col-md-3 control-label">Ask approval to *</label>

                            <div class="col-md-6">
                                <select id="approval-to" name="approval_to" class="selectize" style="width: 100%;"
                                        data-placeholder="Choose one..">
                                    <option value="{{$formula->formulir->approval_to}}">{{ $formula->formulir->approvalTo->name }}</option>
                                    @foreach($list_user_approval as $user_approval)
                                        @if($user_approval->may('approval.point.manufacture.formula'))
                                            @if($formula->formulir->approval_to != $user_approval->id)
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
                            <button type="submit" class="btn btn-effect-ripple btn-primary">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@extends('point-manufacture::app.manufacture.point.formula._script-edit')
