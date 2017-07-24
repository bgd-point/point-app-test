@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-manufacture::app.manufacture.point.formula._breadcrumb')
            <li>Create</li>
        </ul>
        <h2 class="sub-header">Manufacture | Formula</h2>
        @include('point-manufacture::app.manufacture.point.formula._menu')

        @include('core::app.error._alert')
        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('manufacture/point/formula')}}" method="post" class="form-horizontal form-bordered">
                    {!! csrf_field() !!}
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
                                <a href="javascript:void(0)" class="btn btn-effect-ripple btn-primary"><i
                                            class="fa fa-clock-o"></i></a>
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
                                    <option @if($process->id == old('process_id')) selected
                                            @endif value="{{$process->id}}">{{$process->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Name *</label>

                        <div class="col-md-6">
                            <input type="text" name="name" class="form-control" value="{{old('name')}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>

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
                                        <th ></th>
                                        <th >Item *</th>
                                        <th >Quantity *</th>
                                        <th >Unit *</th>
                                        <th >Warehouse *</th>
                                    </tr>
                                    </thead>
                                    <tbody class="manipulate-row">
                                        @for($i=0; $i<count(old('product_id')); $i++)
                                        @if(old('product_id')[$i])
                                        <tr>
                                            <td><a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a></td>
                                            <td>
                                                <select id="product-id-{{$i}}" name="product_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectGoods(this.value, {{$i}})">
                                                    <option value="{{old('product_id')[$i]}}">{{Point\Framework\Models\Master\Item::find(old('product_id')[$i])->codeName}}</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" id="product-{{$i}}" name="product_quantity[]" class="form-control format-quantity text-right" value="{{ old('product_quantity')[$i] }}">
                                            </td>
                                            <td>
                                                <input type="text" name="product_unit_id[]" class="form-control input-unit-{{$i}}" value="{{ old('product_unit_id')[$i] }}" readonly>
                                            </td>
                                            <td>
                                                <select id="warehouse-id-{{$i}}" name="product_warehouse_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
                                                    @foreach($list_warehouse as $warehouse)
                                                        @if (old('product_warehouse_id')[$i] !== '')
                                                            <option @if(old('product_warehouse_id')[$i] == $warehouse->id) selected @endif value="{{$warehouse->id}}">{{$warehouse->codeName}}</option>
                                                        @else
                                                            <option></option>
                                                            <option value="{{$warehouse->id}}">{{$warehouse->codeName}}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                        @endif
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
                                        @for($i=0; $i < count(old('material_id')); $i++)
                                        @if(old('material_id')[$i])
                                        <tr>
                                            <td><a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a></td>
                                            <td>
                                                <select id="material-id-{{$i}}" name="material_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectItem(this.value, {{$i}})">
                                                    <option value="{{ old('material_id')[$i] }}">{{Point\Framework\Models\Master\Item::find(old('material_id')[$i])->codeName}}</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" name="material_quantity[]" class="form-control format-quantity text-right" value="{{ old('material_quantity')[$i] }}">
                                            </td>
                                            <td>
                                                <input type="text" name="material_unit[]" class="form-control input-unit-{{$i}}" value="{{ old('material_unit')[$i] }}"readonly>
                                            </td>
                                            <td>
                                                <select id="warehouse-id-{{$i}}" name="material_warehouse_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
                                                    @foreach($list_warehouse as $warehouse)
                                                        @if (old('material_warehouse_id')[$i] !== '')
                                                            <option @if(old('material_warehouse_id')[$i] == $warehouse->id) selected @endif value="{{$warehouse->id}}">{{$warehouse->codeName}}</option>
                                                        @else
                                                            <option></option>
                                                            <option value="{{$warehouse->id}}">{{$warehouse->codeName}}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                        @endif
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
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Authorized User</legend>
                        </div>
                    </div>  
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Creator</label>
                        <div class="col-md-6 content-show">
                            {{auth()->user()->name}}
                        </div>
                    </div>                  
                    <div class="form-group">
                        <label class="col-md-3 control-label">Request Approval To *</label>
                        <div class="col-md-6">
                            <select name="approval_to" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                <option ></option>
                                @foreach($list_user_approval as $user_approval)
                                    <option value="{{$user_approval->id}}" @if(old('approval_to') == $user_approval->id) selected @endif>{{$user_approval->name}}</option>
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

@extends('point-manufacture::app.manufacture.point.formula._script')
