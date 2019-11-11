@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('core::app/master/_breadcrumb')
        <li><a href="{{ url('master/item') }}">Item</a></li>
        <li><a href="{{ url('master/item/'.$item->id) }}">{{$item->code}}</a></li>
        <li>Edit</li>
    </ul>

    <h2 class="sub-header">Item</h2>
    @include('framework::app.master.item._menu')

    @include('core::app.error._alert')

    <div class="panel panel-default"> 
        <div class="panel-body">
            <form action="{{url('master/item/'.$item->id)}}" method="post" class="form-horizontal form-bordered">
                {!! csrf_field() !!}
                <input name="_method" type="hidden" value="PUT">

                <div class="form-group">
                    <label class="col-md-3 control-label">Asset Account</label>
                    <div class="col-md-6">
                        <div class="@if(access_is_allowed_to_view('create.coa')) input-group @endif">
                            <select id="account_asset_id" name="account_asset_id" class="selectize">
                                <option value="">Choose your asset account</option>
                                @foreach($list_account_asset as $item_account)
                                    <option value="{{ $item_account->id }}" @if($item->accountAsset->id==$item_account->id) selected @endif>{{ $item_account->name }}</option>
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
                    <label class="col-md-3 control-label">Category</label>
                    <div class="col-md-6 content-show">
                        {{$item->category->codeName}}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Code</label>
                    <div class="col-md-6 content-show">
                        <input type="text" name="code" class="form-control" value="{{$item->code}}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Name *</label>
                    <div class="col-md-6">
                        <input type="text" name="name" class="form-control" value="{{$item->name}}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Notes</label>
                    <div class="col-md-6">
                        <input type="text" name="notes" class="form-control" value="{{$item->notes}}">
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
                                                            <option value="{{ $unit->name }}" @if(\Point\Framework\Models\Master\Item::defaultUnit($item->id)->name == $unit->name) selected @endif>{{ $unit->name }}</option>
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
                                <input type="checkbox" id="reminder" name="reminder" {{ $item->reminder == true ? 'checked'  : '' }}>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Minimum Stock</label>
                        <div class="col-md-6">
                            <input type="text" name="reminder_quantity_minimum" class="form-control format-quantity" value="{{$item->reminder_quantity_minimum}}">
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
@include('framework::app.master.coa.__create')
@include('framework::app.master.item.unit.__create')
@stop

@section('scripts')
<script>

    /**
     * Javascript for unit converter
     * 
     */
    
    var unit_datatable = initDatatable('#unit-datatable');
    initFunctionRemoveInDatatable('#unit-datatable', datatable);
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

    $(document).on("keypress", 'form', function (e) {
        var code = e.keyCode || e.which;
        if (code == 13) {
            e.preventDefault();
            return false;
        }
    });

    function selectUnit(value){
        $(".label-unit").val(value);
    }

    function resetForm(index, key){
        $("#button").html("Save");
        $("#code-category").val("");
        $("#name-category").val("");

        $("#button-warehouse").html("Save");
        $("#code-warehouse").val("");
        $("#name-warehouse").val("");

        $("#button-coa").html("Save");
        $("#name-coa").val("");

        $("#button-unit").html("Save");
        $("#name-unit").val("");
        $("#index-unit").val(index);
        $("#key-unit").val(key);
    }

    function addNewUnitInAllSelectize(result, index, key){
        // set default
        var unit_default = $('#default_unit')[0].selectize;
        unit_default.addOption({value:result.name,text:result.name});
        var selectize_zero_from = $('#convertion-from-0')[0].selectize;
        var selectize_zero_to = $('#convertion-to-0')[0].selectize;
        selectize_zero_from.addOption({value:result.name,text:result.name});
        selectize_zero_to.addOption({value:result.name,text:result.name});

        if(index == "-"){
            unit_default.addItem(result.name);
        }else{
            // set cuurent selectize
            var curent_selectize_from = $('#convertion-from-'+index)[0].selectize;
            var curent_selectize_to = $('#convertion-to-'+index)[0].selectize;
            curent_selectize_from.addOption({value:result.name,text:result.name});
            curent_selectize_to.addOption({value:result.name,text:result.name});
            // check selected
            if(key == 'from'){
                curent_selectize_from.addItem(result.name);
            }else{
                curent_selectize_to.addItem(result.name);
            }
        }
        // set new unit in all selectize
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

    function reloadUnit(counter)
    {
        $.ajax({
            url: "{{URL::to('master/item/unit_master/list')}}",
            success: function(data) {
                console.log(data);
                var unit_from = $('#convertion-from-'+counter)[0].selectize;
                unit_from.load(function(callback) {
                    callback(eval(JSON.stringify(data.lists)));
                });

                var unit_to = $('#convertion-to-'+counter)[0].selectize;
                unit_to.load(function(callback) {
                    callback(eval(JSON.stringify(data.lists)));
                });
            }, error: function(data) {
                swal('Failed', 'Something went wrong', 'error');   
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
</script>
@stop
