<div id="modal-insert-group" class="modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title"><strong>New Group</strong></h3>
            </div>
            <div class="modal-body">
                <form id="create-group" action="#" method="post" class="form-horizontal form-bordered">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Coa Category</label>
                        <div class="col-md-9 content-show modal-group-category"></div>
                        <input type="hidden" id="modal-group-category-id" name="coa_category_id">
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Account Number</label>
                        <div class="col-md-9">
                           <input type="text" name="number" id="number-group" class="form-control" value="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Name *</label>
                        <div class="col-md-9">
                           <input type="text" name="name" id="name-group" class="form-control" value="">
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="button-group" onclick="validateGroup()" class="btn btn-effect-ripple btn-primary">Save</button>
                <button type="button" class="btn btn-effect-ripple btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    function createGroup(category_id, category_name){
        $("#modal-group-category-id").val(category_id);
        $(".modal-group-category").html(category_name);
        $("#number-group").val("");
        $("#name-group").val("");
        $("#button-group").html("Submit");
        $("#modal-insert-group").modal();
    }

    function validateGroup() {
        if($("#name-group").val().trim()==""){
            swal('Failed', 'please fill all provided column');
            $("#name-group").focus();
            return false;
        }

        storeGroup();
    }

    function storeGroup(){
        $("#button-group").html("Saving...");
        $.ajax({
            type: 'POST',
            url: "{{URL::to('master/coa/group/store')}}",
            data: $("#create-group").serialize(),
            success: function(result){
                if(result.status == "failed"){
                    swal('Failed', 'Name already exist, please use another name');
                    $("#name-coa-group").focus();
                    $("#button-coa-group").html("Submit");
                } else {
                    $('.modal').modal('hide');
                    loadIndex();
                }
            }
        });
    }
</script>
