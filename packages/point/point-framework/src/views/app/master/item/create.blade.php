@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('core::app/master/_breadcrumb')
        <li><a href="{{ url('master/item') }}">Item</a></li>
        <li>Create</li>
    </ul>

    <h2 class="sub-header">Item</h2>
    @include('framework::app.master.item._menu')

    @include('core::app.error._alert')

    <div class="panel panel-default"> 
        <div class="panel-body">
            <form action="{{url('master/item/')}}" method="post" class="form-horizontal form-bordered">
                {!! csrf_field() !!}

                <div class="form-group">
                    <label class="col-md-3 control-label">Asset Account *</label>
                    <div class="col-md-6">
                        <div class="@if(access_is_allowed_to_view('create.coa')) input-group @endif">
                            <select id="account_asset_id" name="account_asset_id" class="selectize">
                                <option value="">Choose your asset account</option>
                                @foreach($list_account_asset as $item_account)
                                    <option value="{{ $item_account->id }}" @if(old('account_asset_id')==$item_account->id) selected @endif>{{ $item_account->name }}</option>
                                @endforeach
                            </select>
                            @if(access_is_allowed_to_view('create.coa'))
                            <span class="input-group-btn">
                                <a href="#modal-coa" onclick="resetForm()" class="btn btn-effect-ripple btn-primary" data-toggle="modal">
                                    <i class="fa fa-plus"></i>
                                </a>
                            </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label">Category *</label>
                    <div class="col-md-6">
                        <div class="input-group">
                            <select id="item_category_id" name="item_category_id" onchange="selectCategoryItem(this.value)" class="selectize">
                                <option value="">Choose your category</option>
                                @foreach($list_item_category as $item_category)
                                    <option value="{{ $item_category->id }}" @if(old('item_category_id')==$item_category->id) selected @endif>{{ $item_category->name }}</option>
                                @endforeach
                            </select>
                            <span class="input-group-btn">
                                <a href="#modal-category" onclick="resetForm()" class="btn btn-effect-ripple btn-primary" data-toggle="modal"><i class="fa fa-plus"></i></a>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Code *</label>
                    <div class="col-md-6">
                        <input readonly type="text" id="code" name="code" class="form-control" value="{{old('code')}}">
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
                    <legend><i class="fa fa-angle-right"></i> Unit</legend>
                    <div class="form-group">
                        <div class="col-md-9">
                            <span class="help-block">

                            </span>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="table-responsive" style="overflow-x:visible"> 
                                <table id="unit-datatable" class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th ></th>
                                            <th >Convertion From</th>
                                            <th >Convertion To</th>
                                            <th >Convertion </th>
                                            <th >Description </th>
                                        </tr>
                                    </thead>
                                    <tbody class="manipulate-row">
                                    @for($i=0;$i<count($converter);$i++)
                                        <?php $description = "1 ".$converter[$i]['convert_from']." = ".$converter[$i]['convertion']." ".$converter[$i]['convert_to'];?>
                                        <tr>
                                            <td><a href="javascript:void(0)" class="remove-row btn btn-danger" data-item="{{$converter[$i]['rowid']}}"><i class="fa fa-trash"></i></a></td>
                                            <td>
                                                <div class="input-group">
                                                    <select id="convertion-from-{{$i}}" onChange="makeDescription({{$i}})"  name="convertion_from[]" width="100%" class="selectize converter-from" data-placeholder="Choose one..">
                                                        @foreach($list_unit as $unit)
                                                            <option value="{{ $unit->name }}" @if($unit->name==$converter[$i]['convert_from']) selected @endif>{{ $unit->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <span class="input-group-btn">
                                                        <a href="#modal-unit" onclick="resetForm({{$i}},'from')" class="btn btn-effect-ripple btn-primary" data-toggle="modal">
                                                            <i class="fa fa-plus"></i>
                                                        </a>
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <select id="convertion-to-{{$i}}" onChange="makeDescription({{$i}})"  name="convertion_to[]" width="100%" class="selectize converter-to" data-placeholder="Choose one..">
                                                        @foreach($list_unit as $unit)
                                                            <option value="{{ $unit->name }}" @if($unit->name==$converter[$i]['convert_to']) selected @endif>{{ $unit->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <span class="input-group-btn">
                                                        <a href="#modal-unit" onclick="resetForm({{$i}}, 'to')" class="btn btn-effect-ripple btn-primary" data-toggle="modal">
                                                            <i class="fa fa-plus"></i>
                                                        </a>
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" id="convertion-{{$i}}" onChange="makeDescription({{$i}})" name="convertion[]" class="form-control format-quantity" value="{{$converter[$i]['convertion']}}" />
                                            </td>
                                            <td>
                                                <input type="text" readonly id="description-{{$i}}" name="description[]" class="form-control text-center " value="{{$description}}" />
                                            </td>
                                        </tr>
                                    @endfor
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-right">
                                                <b>Default Unit</b>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <select id="default_unit" name="default_unit" class="selectize" onChange="makeDefaultDescription()">
                                                        @foreach($list_unit as $unit)
                                                            <option value="{{ $unit->name }}">{{ $unit->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <span class="input-group-btn">
                                                        <a href="#modal-unit" onclick="resetForm('-','default')" class="btn btn-effect-ripple btn-primary" data-toggle="modal">
                                                            <i class="fa fa-plus"></i>
                                                        </a>
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" readonly id="default_description" name="description" class="form-control text-center " value="" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="5">
                                                <input type="button" id="addRow" class="btn btn-primary" value="Add Converter">
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                   </div>
                </fieldset>


                <fieldset>
                    <legend><i class="fa fa-angle-right"></i> Stock Reminder</legend>
                    <div class="form-group">
                        <div class="col-md-9">
                            <span class="help-block">
                                Set the minimum quantity of inventory and Vesa will remind you to re-order
                            </span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Activate Reminder</label>
                        <div class="col-md-9"> 
                            <div class="input-group">
                                <input type="checkbox" id="reminder" name="reminder" {{ old('reminder') == 'on' ? 'checked'  : '' }}>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Minimum Stock</label>
                        <div class="col-md-6">
                            <input type="text" name="reminder_quantity_minimum" class="form-control format-quantity" value="{{old('reminder_quantity_minimum') ? old('reminder_quantity_minimum') : '0'}}">
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend><i class="fa fa-angle-right"></i> Opening cost</legend>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Date</label>
                        <div class="col-md-3">
                            <input type="text" name="form_date" class="form-control date input-datepicker" data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}" value="{{date(date_format_get(), strtotime(\Carbon::now()))}}" />
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table id="opening-cost-datatable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 5%"></th>
                                        <th class="text-center" style="width: 25%">Quantity</th>
                                        <th class="text-center" style="width: 25%">Cost of Sale</th>
                                        <th class="text-center" style="width: 35%">Warehouse</th>
                                    </tr>
                                </thead>
                                <tbody class="row-manipulated">
                               
                                <?php $olds = count(old('warehouse_id')); ?>
                                @if ( $olds > 0 )
                                    @for($olds=0; $olds < count(old('warehouse_id')); $olds++)
                                    <tr>
                                        <td><a href="javascript:void(0)" class="remove-row btn btn-danger pull-right"><i class="fa fa-trash"></i></a></td>
                                        <td>
                                            <div class="input-group">
                                            <input type="text" name="quantity[]" class="form-control text-right " value="{{old('quantity.'.$olds)}}" />
                                            <span id="unit_span{{$olds}}" class="input-group-addon">unit</span>
                                            </div>
                                        </td>
                                        <td><input type="text" name="cogs[]" class="form-control text-right " value="{{old('cogs.'.$olds)}}" /></td>
                                        <td>
                                            <div class="@if(access_is_allowed_to_view('create.warehouse')) input-group @endif">
                                                <select id="warehouse_id{{$olds}}" name="warehouse_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                                    @foreach($list_warehouse as $warehouse)
                                                    <option @if($warehouse->id == old('warehouse_id.'.$olds)) selected @endif value="{{$warehouse->id}}" >{{$warehouse->name}}</option>
                                                    @endforeach
                                                </select>

                                                <span class="input-group-btn" style="vertical-align: top;">
                                                    <a href="#modal-warehouse" onclick="resetForm(); getBtnID({{$olds}});" class="btn btn-effect-ripple btn-primary" data-toggle="modal"><i class="fa fa-plus"></i></a>
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                    @endfor
                                @endif
                                </tbody> 
                                <tfoot>
                                    <tr >
                                        <td colspan="4"><input type="button" id="addItemRow" class="btn btn-primary" value="add additional warehouse"></td>
                                    </tr>
                                </tfoot>
                            </table> 
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

@include('framework::app.master.item.category.__create')
@include('framework::app.master.warehouse.__create')
@include('framework::app.master.coa.__create')
@include('framework::app.master.item.unit.__create')
@stop

@section('scripts')
<script>
    /**
     * Get new code category for item
     * 
     */
    function selectCategoryItem(category_id){
        $.ajax({
            url: '{{url("master/item/code")}}',
            data: {
                item_category_id: category_id
            },
            success: function(data) {
                $('#code').val(data.code);
            }
        });
    }
    
    /**
     * Javascript for unit converter
     * 
     */
    var unit_datatable = initDatatable('#unit-datatable');
    initFunctionRemoveInDatatable('#unit-datatable', unit_datatable);
    var counter = $("#unit-datatable").dataTable().fnGetNodes().length;

    $('#addRow').on( 'click', function () {
        var label = $("#unit_name_default").val();
        var default_unit = $('#default_unit option:selected').val();

        unit_datatable.row.add( [
            '<a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a>',
            '<div class="input-group"><select id="convertion-from-'+counter+'" onChange="makeDescription('+counter+')"  name="convertion_from[]" class="selectize converter-from" data-placeholder="Choose one..">'
                @foreach($list_unit as $unit)
                +'<option value="{{$unit->name}}" >{{$unit->name}}</option>'
                @endforeach
            +'</select>'
            +'<span class="input-group-btn"><a href="#modal-unit" onclick=resetForm('+counter+',"from") class="btn btn-effect-ripple btn-primary" data-toggle="modal">'
                +'<i class="fa fa-plus"></i>'
            +'</a></span></div>',
            '<div class="input-group"><select id="convertion-to-'+counter+'" onChange="makeDescription('+counter+')" name="convertion_to[]" width="100%" class="selectize converter-to" data-placeholder="Choose one..">'
                @foreach($list_unit as $unit)
                +'<option value="{{$unit->name}}">{{$unit->name}}</option>'
                @endforeach
            +'</select>'
            +'<span class="input-group-btn"><a href="#modal-unit" onclick=resetForm('+counter+',"to") class="btn btn-effect-ripple btn-primary" data-toggle="modal">'
                +'<i class="fa fa-plus"></i>'
            +'</a></span></div>',
            '<input type="text" id="convertion-'+counter+'" onChange="makeDescription('+counter+')" name="convertion[]" class="form-control format-quantity" value="1" />',
            '<input type="text" readonly id="description-'+counter+'" name="description[]" class="form-control text-center " value="" />',

        ] ).draw( false );
        
        initSelectize('#convertion-from-'+counter);
        initSelectize('#convertion-to-'+counter);
        makeDescription(counter);
        reloadUnit(counter);
        counter++;
        
    } );
    

    function addNewUnitInAllSelectize(result, index, key){
        // set default
        var unit_default = $('#default_unit')[0].selectize;
        unit_default.addOption({value:result.name,text:result.name});
        
        if(index == "-") {
            unit_default.addItem(result.name);
        } else {
            // set cuurent selectize
            var curent_selectize_from = $('#convertion-from-'+index)[0].selectize;
            var curent_selectize_to = $('#convertion-to-'+index)[0].selectize;
            curent_selectize_from.addOption({value:result.name,text:result.name});
            curent_selectize_to.addOption({value:result.name,text:result.name});
            // check selected
            if(key == 'from') {
                curent_selectize_from.addItem(result.name);
            } else {
                curent_selectize_to.addItem(result.name);
            }
        }
        // set new unit in all selectize
        if(counter > 0){
            var selectize_zero_from = $('#convertion-from-0')[0].selectize;
            var selectize_zero_to = $('#convertion-to-0')[0].selectize;
            selectize_zero_from.addOption({value:result.name,text:result.name});
            selectize_zero_to.addOption({value:result.name,text:result.name});

            for (var i = 1; i < counter; i++) {
                if(i != index){
                    if($('#convertion-from-'+i).length != 0){
                        var unit_from = $('#convertion-from-'+i)[0].selectize;
                        unit_from.addOption({value:result.name,text:result.name});
                    }

                    if($('#convertion-to-'+i).length != 0){
                        var unit_to = $('#convertion-to-'+i)[0].selectize;
                        unit_to.addOption({value:result.name,text:result.name});
                    }
                }
            };
        }
        
    }

    function reloadUnit(counter){
        $.ajax({
            url: "{{URL::to('master/item/unit_master/list')}}",
            success: function(data) {
                var unit_from = $('#convertion-from-'+counter)[0].selectize;
                unit_from.load(function(callback) {
                    callback(eval(JSON.stringify(data.lists)));
                });

                var unit_to = $('#convertion-to-'+counter)[0].selectize;
                unit_to.load(function(callback) {
                    callback(eval(JSON.stringify(data.lists)));
                });
            }, error: function(data) {
                
            }
        });
    }

    function makeDescription(counter){
        var convert_from = $('#convertion-from-'+counter+' option:selected').val();
        var convert_to = $('#convertion-to-'+counter+' option:selected').val();
        var convert = $("#convertion-"+counter).val();
        var description = '1 '+convert_from+' = '+convert+' '+convert_to;
        $("#description-"+counter).val(description);
    }

    function makeDefaultDescription(){
        var unit = $('#default_unit option:selected').val();
        $("#default_description").val('Default = 1 '+unit);
    }
    makeDefaultDescription();

    $(function(){
        var old = {{$olds}};
        var unit_default = $("#default_unit").text();

        if(old > 0){            
            for(var x=0; x < old; x++){
                $("#unit_span"+x).text(unit_default);
            }
        }

    });

    /**
     * Javascript for opening cost
     * 
     */
    
    var count;
    var opening_cost_datatable = initDatatable('#opening-cost-datatable');
    initFunctionRemoveInDatatable('#opening-cost-datatable', opening_cost_datatable);
    
    {{$olds}}>0 ? count = {{$olds}} : count = 0 ; 

    $('#addItemRow').on( 'click', function () {
        opening_cost_datatable.row.add( [
            '<a href="javascript:void(0)" class="remove-row btn btn-danger pull-right"><i class="fa fa-trash"></i></a>',
            '<div class="input-group"><input type="text" name="quantity[]" class="form-control text-right format-accounting" value="0" /><span id="unit_span'+count+'" class="input-group-addon">unit</span></div>',
            '<input type="text" name="cogs[]" class="form-control text-right format-accounting" value="0" />',
            '<div class="input-group"><select id="warehouse_id'+count+'" name="warehouse_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
                @foreach($list_warehouse as $warehouse)
                +'<option value="{{$warehouse->id}}">{{$warehouse->name}}</option>'
                @endforeach
            +'</select><span class="input-group-btn">'
            +'@if(auth()->user()->may("create.warehouse"))'
            +'<a href="#modal-warehouse" onclick="resetForm(); getBtnID('+count+');" class="btn btn-effect-ripple btn-primary" data-toggle="modal"><i class="fa fa-plus"></i></a>@endif</span></div>'
        ] ).draw( false );
    
        initSelectize('#warehouse_id'+count);
        initFormatNumber();
        getWarehouse(count); 
        count++;
               
    } );

    $(document).on("keypress", 'form', function (e) {
        var code = e.keyCode || e.which;
        if (code == 13) {
            e.preventDefault();
            return false;
        }
    });

    function selectUnit(value) {
        $(".label-unit").val(value);
    }

    function resetForm(index, key) {
        $("#button").html("Save");
        $("#code-category").val("");
        $("#name-category").val("");

        $("#button-warehouse").html("Save");
        $("#name-warehouse").val("");

        $("#button-coa").html("Save");
        $("#name-coa").val("");

        $("#button-unit").html("Save");
        $("#name-unit").val("");
        $("#index-unit").val(index);
        $("#key-unit").val(key);
        
    }

    function getWarehouse(count) {
        $.ajax({
            url: "{{URL::to('master/warehouse/list')}}",
            success: function(warehouse) {
                var selectize = $("#warehouse_id"+count)[0].selectize;
                selectize.clear();
                selectize.clearOptions();
                selectize.load(function(callback) {
                    callback(eval(JSON.stringify(warehouse.lists)));
                });

            }, error: function(warehouse) {
                swal("Failed","something went wrong");
            }
        });

        var unit_default = $("#default_unit").text();
        $("#unit_span"+count).text(unit_default);
    }

    function getBtnID(index) {
        $("#index").val(index);
    }

</script>
@stop
