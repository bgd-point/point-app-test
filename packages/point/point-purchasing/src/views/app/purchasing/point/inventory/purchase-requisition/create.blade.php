@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li><a href="{{ url('purchasing/point/purchase-requisition') }}">Purchase Requisition</a></li>
            <li>Create</li>
        </ul>
        <h2 class="sub-header">Purchase Requisition</h2>
        @include('point-purchasing::app.purchasing.point.inventory.purchase-requisition._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('purchasing/point/purchase-requisition')}}" method="post" class="form-horizontal form-bordered">
                    {!! csrf_field() !!}
                    <input type="hidden" name="employee_checking" value="required">
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Form</legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Required Date *</label>

                        <div class="col-md-3">
                            <input type="text" name="required_date" class="form-control date input-datepicker" data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}" value="{{date(date_format_get(), strtotime(\Carbon::now()))}}">
                        </div>
                        <div class="col-md-3">
                            <div class="input-group bootstrap-timepicker">
                                <input type="text" id="time" name="time" class="form-control timepicker">
                                <span class="input-group-addon">
                                    <a href="javascript:void(0)" href="btn btn-effect-ripple btn-primary">
                                        <i class="fa fa-clock-o"></i>
                                    </a>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Employee *</label>
                        <div class="col-md-6">
                            <?php $employee = Point\Framework\Models\Master\Person::find(old('employee_id')); ?>                        
                            <select id="employee-id" name="employee_id" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                <option value="{{ old('employee_id') }}">{{ $employee ? $employee->codeName : ''}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Supplier</label>
                        <div class="col-md-6">
                            <?php $supplier = Point\Framework\Models\Master\Person::find(old('supplier_id')); ?>
                            @if(auth()->user()->may('create.supplier')) <div class="input-group bootstrap-timepicker"> @endif
                                <select id="contact_id" name="supplier_id" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                    <option value="{{ old('supplier_id') }}">{{ $supplier ? $supplier->codeName : ''}}</option>
                                </select>

                                @if(auth()->user()->may('create.supplier'))
                                <span class="input-group-addon">
                                    <a href="#modal-contact" class="btn-effect-ripple" data-toggle="modal">
                                        <i class="fa fa-plus"></i>
                                    </a>
                                </span>
                                @endif
                            @if(auth()->user()->may('create.supplier')) </div> @endif
                        </div>
                    </div>
                    {{--<div class="form-group">--}}
                        {{--<label class="col-md-3 control-label">Include Cash Advance</label>--}}
                        {{--<div class="col-md-6 content-show">--}}
                            <input type="hidden" id="include-cash-advance" name="include_cash_advance" value="false">
                            {{--<span class="help-block">Check for create cash advance</span>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>
                        <div class="col-md-6">
                            <input type="text" name="notes" class="form-control" value="{{old('notes')}}">
                        </div>
                    </div>
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Item</legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table id="item-datatable" class="table table-striped">
                                        <thead>
                                        <tr>
                                            <th style="width: 50px"></th>
                                            <th style="min-width: 220px">ITEM *</th>
                                            <th style="min-width: 120px">QUANTITY</th>
                                            <th style="min-width: 220px">PRICE *</th>
                                            <th style="min-width: 220px">ALLOCATION *</th>
                                            <th style="min-width: 220px">NOTES</th>
                                        </tr>
                                        </thead>
                                        <tbody class="manipulate-row">
                                        <?php $counter = 0;?>
                                        @if(count(old('item_id')) > 0)
                                            @for($counter; $counter < count(old('item_id')); $counter++ )
                                                <tr>
                                                    <?php $item = Point\Framework\Models\Master\Item::find(old('item_id')[$counter]);?>
                                                    <td><a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a></td>
                                                    <td>
                                                        <div class="@if(access_is_allowed_to_view('create.item')) input-group @endif">
                                                            <select id="item-id-{{$counter}}" name="item_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectItem(this.value, {{$counter}})">
                                                                <option value="{{(old('item_id')[$counter])}}">
                                                                    {{ $item ? $item->codeName : ''}}
                                                                </option>
                                                            </select>
                                                            @if(access_is_allowed_to_view('create.item'))
                                                            <span class="input-group-btn">
                                                                <a href="#modal-item" onclick="openModalItem('selectize', '#item-id-{{$counter}}', 'codeName')" class="btn btn-effect-ripple btn-primary" data-toggle="modal">
                                                                    <i class="fa fa-plus"></i>
                                                                </a>
                                                            </span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="input-group">
                                                            <input style="min-width: 120px" id="item-quantity-{{$counter}}"
                                                                   type="text" name="item_quantity[]"
                                                                   class="form-control format-quantity text-right calculate"
                                                                   value="{{number_format_db(old('item_quantity')[$counter]) }}"/><span
                                                                    id="span-unit-{{$counter}}"
                                                                    class="input-group-addon">{{old('item_unit')[$counter]}}</span></div>
                                                        <input type="hidden" readonly id="item-unit-{{$counter}}"
                                                               name="item_unit[]"
                                                               class="form-control text-right"
                                                               value="{{old('item_unit')[$counter]}}"/></td>
                                                    <td>
                                                        <input type="text" id="item-price-{{$counter}}" name="item_price[]"
                                                               class="form-control format-price calculate text-right"
                                                               value="{{number_format_db(old('item_price')[$counter])}}"/>
                                                    </td>
                                                    <td>
                                                        <select id="allocation-id-{{$counter}}" name="allocation_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                                        @foreach($list_allocation as $allocation)
                                                        <option value="{{$allocation->id}}" @if(old('allocation_id')[$counter] == $allocation->id) selected @endif>{{$allocation->name}}</option>
                                                        @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" id="notes-{{$counter}}" name="item_notes[]">
                                                    </td>
                                                </tr>
                                            @endfor
                                        @endif
                                        </tbody>
                                        <tfoot>
                                        <tr>
                                            <td>
                                                <input type="button" id="addItemRow" class="btn btn-primary" value="Add Item">
                                            </td>
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
                    </fieldset>

                    <fieldset>
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
                                    @foreach($list_user_approval as $user_approval)
                                        @if($user_approval->may('approval.point.purchasing.requisition'))
                                            <option value="{{$user_approval->id}}"
                                                    @if(old('approval_to') == $user_approval->id) selected @endif>{{$user_approval->name}}
                                            </option>
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
    @include('framework::app.master.contact.__create', ['person_type' => 'supplier'])
    @include('framework::app.master.item._create')

@stop

@section('scripts')
    @include('framework::scripts.item')
    @include('framework::scripts.person')
    <script>
        $(function() {
            populateJsonItem();
        });

        var item_table = initDatatable('#item-datatable');
        var counter = {{$counter}} ? {{$counter}} : 0;

        $('#addItemRow').on('click', function () {
            item_table.row.add([
                '<a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a>',
                '<div class="@if(access_is_allowed_to_view("create.item")) input-group @endif"><select id="item-id-' + counter + '" name="item_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectItem(this.value, ' + counter + ')">'
                + '<option ></option>'
                + '</select>'
                +'@if(access_is_allowed_to_view("create.item"))<span class="input-group-btn"><a href="#modal-item" onclick=\'openModalItem("selectize", "#item-id-'+counter+'", "codeName")\' class="btn btn-effect-ripple btn-primary" data-toggle="modal">'
                    +'<i class="fa fa-plus"></i>'
                +'</a></span>@endif</div>',
                '<div class="input-group"><input type="text" id="item-quantity-' + counter + '" name="item_quantity[]" class="form-control format-quantity text-right calculate" value="0" /><span id="span-unit-' + counter + '" class="input-group-addon"></span></div><input type="hidden"  id="item-unit-' + counter + '" name="item_unit[]" class="form-control format-quantity text-right" value="" />',
                '<input type="text" id="item-price-' + counter + '" name="item_price[]" class="form-control format-quantity calculate text-right" value="0" />',
                '<select id="allocation-id-' + counter + '" name="allocation_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
                @foreach($list_allocation as $allocation)
                + '<option value="{{$allocation->id}}">{{$allocation->name}}</option>'
                @endforeach
                + '</select>',
                '<input type="text" name="item_notes[]" id="notes-'+counter+'" class="form-control">'
            ]).draw(false);

            initSelectize('#item-id-' + counter);
            initSelectize('#allocation-id-' + counter);
            reloadItem('#item-id-' + counter);
            initFormatNumber();

            $("textarea").on("click", function () {
                $(this).select();
            });
            $("input[type='text']").on("click", function () {
                $(this).select();
            });
            counter++;
        });

        $('#item-datatable tbody').on('click', '.remove-row', function () {
            item_table.row($(this).parents('tr')).remove().draw();
        });

        $(document).on("keypress", 'form', function (e) {
            var code = e.keyCode || e.which;
            if (code == 13) {
                e.preventDefault();
                return false;
            }
        });

        function selectItem(item_id, counter) {
            getItemUnit(item_id, "#span-unit-"+counter, "html");
            getItemUnit(item_id, "#item-unit-"+counter, "input");
            getPrice(item_id, "#item-price-"+counter, "input");
        }

        // reload data item with ajax
        if (counter > 0) {
            for(var i=0; i< counter; i++) {
                if($('#item-id-'+i).length != 0){
                    reloadItem('#item-id-' + i, false);
                }
            }    
        }

        @if(old('employee_id')) 
            reloadPerson('#employee-id', 'employee', false); 
            reloadPerson('#contact_id', 'supplier', false); 
        @else 
            reloadPerson('#contact_id', 'supplier'); 
            reloadPerson('#employee-id', 'employee'); 
        @endif 
    </script>
@stop
