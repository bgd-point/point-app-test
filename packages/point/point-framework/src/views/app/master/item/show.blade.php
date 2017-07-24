@extends('core::app.layout')
 
@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('core::app/master/_breadcrumb')
        <li><a href="{{ url('master/item') }}">Item</a></li>
        <li>{{ $item->code }}</li>
    </ul>

    <h2 class="sub-header">Item</h2>
    @include('framework::app.master.item._menu')

    <div class="block full">
        <!-- Block Tabs Title -->
        <div class="block-title">
            <ul class="nav nav-tabs" data-toggle="tabs">
                <li class="active"><a href="#block-tabs-form">Form</a></li>
                <li><a href="#block-tabs-history">History</a></li>
                <li><a href="#block-tabs-settings"><i class="gi gi-settings"></i></a></li>
            </ul>
        </div>
        <!-- END Block Tabs Title -->

        <!-- Tabs Content -->
        <div class="tab-content">
            <div class="tab-pane active" id="block-tabs-form">
                <div class="form-horizontal form-bordered">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Asset Account</label>
                        <div class="col-md-6 content-show">
                            {{$item->accountAsset->account}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Category</label>
                        <div class="col-md-6 content-show">
                            {{$item->category->name}}    
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Code</label>
                        <div class="col-md-6 content-show">
                            {{$item->code}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Name</label>
                        <div class="col-md-6 content-show">
                            {{$item->name}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>
                        <div class="col-md-6 content-show">
                            {{$item->notes}}
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
                                            <th >No</th>
                                            <th >Convertion From</th>
                                            <th >Convertion To</th>
                                            <th >Convertion </th>
                                            <th >Description </th> 
                                        </tr>
                                    </thead>
                                    <tbody class="manipulate-row">
                                    <?php $count = count($item_unit)-1;?>
                                    @for($i=0;$i < $count;$i++)
                                        
                                        <?php
                                            $converter = $item_unit[$i]['converter'];
                                            $converter = $converter / $item_unit[$i+1]['converter'];
                                            $description = "1 ".$item_unit[$i]['name']. " = ".$converter." ".$item_unit[$i+1]['name'];
                                        ?>
                                        <tr>
                                            <td>{{$i+1}}</td>
                                            <td>{{$item_unit[$i]['name']}}</td>
                                            <td>{{$item_unit[$i+1]['name']}}</td>
                                            <td>{{$converter}}</td>
                                            <td>{{$description}}</td>
                                        </tr>
                                    @endfor
                                    </tbody> 
                                    <tfoot>
                                        <tr>
                                            <td colspan="4" class="text-right"></td>
                                            <td>
                                                <b>Default unit = 1 {{$unit_default[0]['name']}}</b>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table> 
                            </div>
                        </div>
                   </div>
                </fieldset>

                    <fieldset>
                        <legend><i class="fa fa-angle-right"></i> Reminder Stock</legend>
                        <div class="form-group">
                            <div class="col-md-9">
                            <span class="help-block">
                                If you want to get notification when your stock below your limit, just put your
                                minimum quantity here to get our reminder
                            </span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label">Activate Reminder</label>
                            <div class="col-md-9 content-show">
                                <div class="input-group">
                                    <input type="checkbox" id="reminder" name="reminder" {{$item->reminder == true ? 'checked' : ''}} disabled>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Minimum Stock</label>
                            <div class="col-md-6 content-show">
                                {{number_format_quantity($item->reminder_quantity_minimum)}}
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
            <div class="tab-pane" id="block-tabs-history">
                @include('framework::app._histories', ['histories' => $histories])
            </div>
            <div class="tab-pane" id="block-tabs-settings">
                <a href="{{url('master/item/'.$item->id.'/edit')}}" class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                <a href="javascript:void(0)" class="btn btn-effect-ripple btn-danger" onclick="secureDelete({{$item->id}}, '{{url('master/item/delete')}}', '/master/item/')"><i class="fa fa-times"></i> Delete</a>

                <a id="link-state-{{$item->id}}" href="javascript:void(0)" 
                class="btn btn-effect-ripple {{$item->disabled == 0 ? 'btn-success' : 'btn-default' }}" 
                onclick="state({{$item->id}})">
                <i id="icon-state-{{$item->id}}" class="{{$item->disabled == 0 ? 'fa fa-pause' : 'fa fa-play' }}"></i> {{$item->disabled == 0 ? 'disable' : 'enable' }}</a>
            </div>
        </div>
        <!-- END Tabs Content -->
    </div> 
</div>
@stop

@section('scripts')
<script>
function state(index) {
    $.ajax({
        type:'post',
        url: "{{URL::to('master/item/state')}}",
        data: {
            index: index,
        },
        success: function(result){
            if(result.status === "failed"){
                swal(result.status, result.message,"error");
                return false;
            }
            
            var status = result.data_value == 0 ? 'enable' : 'disable'; 
            if(result.data_value == 0 ){
                $("#link-state-"+index).removeClass("btn-default").addClass("btn-success");
                $("#icon-state-"+index).removeClass("fa fa-play").addClass("fa fa-pause");
                $("#link-state-"+index).html("<i class='fa fa-pause'></i> disable");
            } else {
                $("#link-state-"+index).removeClass("btn-success").addClass("btn-default");
                $("#icon-state-"+index).removeClass("fa fa-pause").addClass("fa fa-play");
                $("#link-state-"+index).html("<i class='fa fa-play'></i> enable");
            } 
            swal(result.status, result.message,"success");
        }, error : function(e){
            swal('Failed', 'Something went wrong', 'error');
        }
    });
} 
</script>
@stop
