@extends('core::app.layout')

@section('content')
<div id="page-content">
    <a href="{{url('master')}}" class="pull-right">
        <i class="fa fa-arrow-circle-left push-bit"></i> Back
    </a>

    <h2 class="sub-header">Item</h2>
    @include('framework::app.master.item._menu')
    @include('core::app.error._alert')

    <div class="panel panel-default">
        <div class="panel-body">
            <form action="{{url('master/item/import/upload')}}" method="post" enctype="multipart/form-data">
                {!! csrf_field() !!}
                <div class="form-group">
                    <label for="example-nf-email">IMPORT FROM EXCEL</label>
                    <br/>
                    <input type="file" name="file" required style="display:inline"/>
                    <button class="btn btn-primary"><i class="fa fa-upload"></i> UPLOAD FILE</button>
                </div> 
            </form>
            <div class="form-group">
                <p>
                    Download example file using the button below, then fill corresponding provided columns

                    <br/>
                    <label class="label label-default">Name</label> data must be filled
                </p>
                <a href="{{url('master/item/import/download')}}" class="btn btn-effect-ripple btn-info"><i class="fa fa-download"></i> DOWNLOAD EXAMPLE EXCEL FILE</a>
            </div>

            <br/>

            <form action="{{url('master/item/import')}}" method="post" class="form-horizontal">
                {!! csrf_field() !!}

                <div class="form-horizontal form-bordered">
                    @if($success || $error)
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Form</legend>
                        </div>
                    </div>
                    @if(!empty($error))
                    <div class="form-group">
                        <div class=" col-md-12">
                            <div class="well-sm bg-info">
                                <h4><strong>Information</strong></h4>
                                <p>
                                    * {{count($error)}} item not added
                                    <a href="#modal-import-error" data-toggle="modal"><i class="fa fa-eye fa-fw"></i>Detail</a>
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif
                @endif
                @if(!empty($success))
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Date *</label>
                        <div class="col-md-9">
                            <input type="text" name="form_date" class="form-control date input-datepicker" data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}" value="{{date(date_format_get(), strtotime(\Carbon::now()))}}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>
                        <div class="col-md-9">
                            <input type="text" name="notes" class="form-control" value="">
                        </div>
                    </div>
                </div>

                {!! $list_import->render() !!}

                <div class="table-responsive"> 
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th style="width:70px">No</th>
                                <th>Asset Account</th>
                                <th>Category</th>
                                <th>Warehouse</th>
                                <th>Name</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                                <th>Cost of Sale</th>
                                <th>Notes</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $count=0;?>
                            @for($i=0; $i < count($success); $i++)
                            <tr>
                                <td>{{$i + 1 + (app('request')->input('page')*100) }}</td>
                                <td id="coa-{{$i}}">{{$success[$i]['asset_account']}}</td>
                                <td id="category-{{$i}}">{{$success[$i]['category']}}</td>
                                <td id="warehouse-{{$i}}">{{$success[$i]['warehouse']}}</td>
                                <td id="name-{{$i}}">{{$success[$i]['name']}}</td>
                                <td id="quantity-{{$i}}">{{number_format_quantity($success[$i]['quantity'],0)}}</td>
                                <td id="unit-{{$i}}">{{$success[$i]['unit']}}</td>
                                <td id="cos-{{$i}}">{{number_format_quantity($success[$i]['cost_of_sale'],0)}}</td>
                                <td id="notes-{{$i}}">{{$success[$i]['notes']}}</td>
                                <td align="center">
                                    <a href="{{url('master/item/import/delete/'.$success[$i]['rowid'])}}" class="remove-row btn btn-danger" data-item=""><i class="fa fa-trash"></i></a>
                                    <a href="#edit-modal" onclick="edit({{$success[$i]['rowid']}},{{$i}})"  class="remove-row btn btn-info"data-toggle="modal"><i class="fa fa-pencil"></i></a>
                                </td>
                            </tr>
                            @endfor
                        </tbody>
                    </table>

                    {!! $list_import->render() !!}

                    <div class="form-group">
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-effect-ripple btn-primary loading">SUBMIT FORM</button>
                            <a href="{{url('master/item/import/clear')}}" class="btn btn-effect-ripple btn-danger" data-toggle="modal">CLEAR</a>
                        </div>
                    </div>
                </div>
            </form>
            @endif
        </div>
    </div>  
</div>
@include('framework::app.master.item._error-import')
@include('framework::app.master.item._edit-import')
<script>
function edit(id, index) {
    coa = $("#coa-"+index).html();
    category = $("#category-"+index).html();
    warehouse  = $("#warehouse-"+index).html();
    name = $("#name-"+index).html();
    quantity  = $("#quantity-"+index).html();
    unit  = $("#unit-"+index).html();
    cos = $("#cos-"+index).html();
    notes = $("#notes-"+index).html();
    
    $("#coa-edit").val(coa);
    $("#unit-edit").val(unit);
    $("#category-edit").val(category);
    $("#warehouse-edit").val(warehouse);
    $("#name-edit").val(name);
    $("#quantity-edit").val(quantity);
    $("#row-id").val(id);
    $("#index").val(index);
    $("#cos-edit").val(appNum(cos));
    $("#notes-edit").val(notes);
    resetForm();

}

function validateItem(){
    if($("#name-edit").val().trim() == "" || $("#quantity-edit").val().trim() == "" || $("#cos-edit").val().trim() == ""){
        swal("Failde","Please, complete the fill.");
        $("#name-edit").focus();
    }else{
        storeItem();
    }
}

function storeItem(){
    data = $("#form_item").serialize();
    $("#button-item").html("Saving...");

    $.ajax({
        type:'POST',
        url: "{{URL::to('master/item/import/insert')}}",
        data:data,
        success: function(result){
            console.log(result);
            if(result.status == "failed"){
                swal("Failed", "please try again");
                resetForm();
            }else{
                updateTable(result,$("#index").val());
                $('.modal').modal('hide');
            }
            
        }, error: function(result){
           swal('Failed', 'Something went wrong');
        }
    });
}

function resetForm(){
    $("#button-item").html("Submit");
}

function updateTable(result, index){
    $("#name-"+index).html(result.name);
    $("#quantity-"+index).html(result.quantity);
    $("#cos-"+index).html(result.cost_of_sale);
    $("#notes-"+index).html(result.notes);
    
}
</script>

@stop
