@extends('core::app.layout')

@section('content')
<div id="page-content">
    <a href="{{url('master')}}" class="pull-right">
        <i class="fa fa-arrow-circle-left push-bit"></i> Back
    </a>

    <h2 class="sub-header">Contact</h2>
    @include('framework::app.master.contact._menu')
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
                                    * {{count($error)}} contact not added
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
                                <th>Group</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Phone</th>
                                <th>Notes</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $count=0;?>
                            @for($i=0; $i < count($success); $i++)
                            <tr>
                                <td>{{$i + 1 + (app('request')->input('page')*100) }}</td>
                                <td id="group-{{$i}}">{{$success[$i]['group']}}</td>
                                <td id="name-{{$i}}">{{$success[$i]['name']}}</td>
                                <td id="email-{{$i}}">{{$success[$i]['email']}}</td>
                                <td id="address-{{$i}}">{{$success[$i]['address']}}</td>
                                <td id="phone-{{$i}}">{{$success[$i]['phone']}}</td>
                                <td id="notes-{{$i}}">{{$success[$i]['notes']}}</td>
                                
                                <td align="center">
                                    <a href="{{url('master/contact/'.$person_type->slug.'/import/delete/'.$success[$i]['rowid'])}}" class="remove-row btn btn-danger" data-item=""><i class="fa fa-trash"></i></a>
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
                            <a href="{{url('master/contact/'.$person_type->slug.'/import/clear')}}" class="btn btn-effect-ripple btn-danger" data-toggle="modal">CLEAR</a>
                        </div>
                    </div>
                </div>
            </form>
            @endif
        </div>
    </div>  
</div>
@include('framework::app.master.contact._error-import')
@include('framework::app.master.contact._edit-import')
<script>
function edit(id, index) {
    name = $("#name-"+index).html();
    email = $("#email-"+index).html();
    address  = $("#address-"+index).html();
    phone = $("#phone-"+index).html();
    notes  = $("#notes-"+index).html();
    group  = $("#group-"+index).html();
    
    $("#group-edit").val(group);
    $("#name-edit").val(name);
    $("#email-edit").val(email);
    $("#address-edit").val(address);
    $("#phone-edit").val(phone);
    $("#notes-edit").val(notes);
    $("#row-id").val(id);
    $("#index").val(index);
    
    resetForm();
}

function validateContact(){
    if($("#name-edit").val().trim() == ""){
        swal("Failed","Please, complete the fill.");
        $("#name-edit").focus();
    }else{
        storeContact();
    }
}

function storeContact(){
    data = $("#form_contact").serialize();
    $("#button-contact").html("Saving...");

    $.ajax({
        type:'POST',
        url: "{{URL::to('master/contact/'.$person_type->slug.'/import/update-temp')}}",
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
    $("#button-contact").html("Submit");
}

function updateTable(result, index){
    $("#name-"+index).html(result.name);
    $("#email-"+index).html(result.email);
    $("#address-"+index).html(result.address);
    $("#phone-"+index).html(result.phone);
    $("#notes-"+index).html(result.phone);
}

</script>

@stop
