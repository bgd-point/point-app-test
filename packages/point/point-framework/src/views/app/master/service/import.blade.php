@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('core::app/master/_breadcrumb')
        <li><a href="{{ url('master/service') }}">Service</a></li>
        <li>Import</li>
    </ul>

    <h2 class="sub-header">Service</h2>
    @include('framework::app.master.service._menu')
    @include('core::app.error._alert')

    <div class="panel panel-default">
        <div class="panel-body">
            <form action="{{$url_upload}}" method="post" enctype="multipart/form-data">
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
                <a href="{{$url_download}}" class="btn btn-effect-ripple btn-info"><i class="fa fa-download"></i> DOWNLOAD EXAMPLE EXCEL FILE</a>
            </div>

            <br/>

            <form action="{{$url_import}}" method="post" class="form-horizontal">
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
                                    * {{count($error)}} service not added
                                    <a href="#modal-import-error" data-toggle="modal"><i class="fa fa-eye fa-fw"></i>Detail</a>
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif
                @endif
                @if(!empty($success))
                </div>

                {!! $list_import->render() !!}

                <div class="table-responsive"> 
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th style="width:70px">No</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Notes</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $count=0;?>
                            @for($i=0; $i < count($success); $i++)
                            <tr>
                                <td>{{$i + 1 + (app('request')->input('page')*100) }}</td>
                                <td id="name-{{$i}}">{{$success[$i]['name']}}</td>
                                <td id="price-{{$i}}">{{number_format_quantity($success[$i]['price'], 0)}}</td>
                                <td id="notes-{{$i}}">{{$success[$i]['notes']}}</td>
                                
                                <td align="center">
                                    <a href="{{url('master/service/import/delete/'.$success[$i]['rowid'])}}" class="remove-row btn btn-danger" data-item=""><i class="fa fa-trash"></i></a>
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
                            <a href="{{url('master/service/import/clear')}}" class="btn btn-effect-ripple btn-danger" data-toggle="modal">CLEAR</a>
                        </div>
                    </div>
                </div>
            </form>
            @endif
        </div>
    </div>  
</div>
@include('framework::app.master.service._error-import')
@include('framework::app.master.service._edit-import')
<script>
function edit(id, index) {
    name = $("#name-"+index).html();
    price = $("#price-"+index).html();
    notes  = $("#notes-"+index).html();
    
    $("#name-edit").val(name);
    $("#price-edit").val(price);
    $("#notes-edit").val(notes);
    $("#row-id").val(id);
    $("#index").val(index);
    
    resetForm();
}

function validateService(){
    if($("#name-edit").val().trim() == ""){
        swal("Failed","Please, complete the fill.");
        $("#name-edit").focus();
    }else{
        storeService();
    }
}

function storeService(){
    data = $("#form_service").serialize();
    $("#button-service").html("Saving...");

    $.ajax({
        type:'POST',
        url: "{{URL::to('master/service/import/update-temp')}}",
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
    $("#button-service").html("Submit");
}

function updateTable(result, index){
    $("#name-"+index).html(result.name);
    $("#price-"+index).html(result.price);
    $("#notes-"+index).html(result.notes);
}

</script>

@stop
