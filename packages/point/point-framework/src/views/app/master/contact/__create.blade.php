<div id="modal-contact" class="modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title"><strong>New {{$person_type}}</strong></h3>
            </div>
            <div class="modal-body">
                <form id="contact" name="contact" action="#" method="post" class="form-horizontal form-bordered">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Group *</label>
                        <div class="col-md-9">
                            <select id="person_group_id" name="person_group_id" class="selectize" required>
                                <option value="">Choose your group</option>
                                @foreach($list_group as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Code *</label>
                        <div class="col-md-9">
                            <input type="text" name="code" required id="code-contact" readonly class="form-control" value="{{ $code_contact }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Name *</label>
                        <div class="col-md-9">
                            <input type="text" name="name" required id="name-contact" class="form-control" value="">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="button-contact" onclick="validateAjaxContact()" class="btn btn-effect-ripple btn-primary">Save</button>
                <button type="button" class="btn btn-effect-ripple btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    function validateAjaxContact() {
        if($("#name-contact").val().trim()==""){
            swal("Please, fill all input.");
            $("#name-contact").focus();
            return false;
        }
        storeAjaxContact();
    }

    function storeAjaxContact(){
        data = $("#contact").serialize();
        $("#button-contact").html("Saving...");

        $.ajax({
            type:'POST',
            url: "{{URL::to('master/contact/insert/'.$person_type)}}",
            data:data,
            success: function(result){
                console.log(result.status);
                if(result.status == "failed"){
                    swal('Failed', 'Please, fill all input', 'error');
                    resetAjaxContact();
                    $("#name-contact").focus();
                    return false;
                }
                var selectize = $("#contact_id")[0].selectize;
                selectize.addOption({value:result.id,text:result.name});
                selectize.addItem(result.id);
                $('.modal').modal('hide');
                resetAjaxContact();
                $("#code-contact").val(result.code);
                
            }, error: function(result){
                swal('Failed', 'Something went wrong', 'error');
            }
        });
    }

    function resetAjaxContact(){
        $("#button-contact").html("Save");
        $("#name-contact").val("");
    }
</script>
