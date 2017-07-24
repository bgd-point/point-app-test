<div id="modal-group-edit" class="modal form-horizontal form-bordered" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="edit-group" action="#" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h3 class="modal-title"><strong>Edit Group</strong></h3>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Coa Category</label>
                        <div id="coa-category-name-edit-group" class="col-md-9 content-show"></div>
                        <input type="hidden" id="coa-category-id-edit-group" value="" name="coa_category_id">
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Account Number</label>
                        <div class="col-md-9">
                            <input type="text" name="number" id="number-edit-group" class="form-control" value="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Name *</label>
                        <div class="col-md-9">
                            <input type="text" name="name" id="name-edit-group" class="form-control" value="">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="group_id" id="modal-edit-group-id">
                    <button type="button" id="button-edit-group" onclick="validateEditGroup()" class="btn btn-effect-ripple btn-primary">Save</button>
                    <button type="button" class="btn btn-effect-ripple btn-danger" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    function editGroup(group_id) {
        $("#modal-group-edit").modal();
        $("#modal-edit-group-id").val(group_id);
        $("#button-edit-group").html("Submit");
        $.ajax({
            url: '{{url("master/coa/group/edit")}}',
            data: {
                group_id: group_id,
            },
            success: function(data) {
                $("#number-edit-group").val(data['coa_number']);
                $("#name-edit-group").val(data['name']);
                $("#coa-category-name-edit-group").html(data['coa_category_name']);
                $("#coa-category-id-edit-group").val(data['coa_category_id']);
            }
        });
    }

    function validateEditGroup() {
        if($("#name-edit-group").val().trim()==""){
            swal('Failed', 'please fill all provided column');
            $("#name-edit-group").focus();
            return false;
        }
        
        updateGroup();
    }

    function updateGroup(){
        $("#button-edit-group").html("Saving...");
        $.ajax({
            type: 'POST',
            url: "{{URL::to('master/coa/group/update')}}",
            data: $("#edit-group").serialize(),
            success: function(result){
                if(result.status == "failed"){
                    swal('Failed', 'Please try again');
                    $("#name-edit-group").focus();
                    $("#button-edit-group").html("Submit");
                } else {
                    $('.modal').modal('hide');
                    loadIndex();
                }
            }
        });
    }
</script>
