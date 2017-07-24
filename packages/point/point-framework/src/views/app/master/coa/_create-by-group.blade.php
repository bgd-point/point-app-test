<div id="modal-coa-group" class="modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title"><strong>New Coa</strong></h3>
            </div>
            <div class="modal-body">
                <form id="coa-create-group" action="#" method="post" class="form-horizontal form-bordered">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Group</label>
                        <div class="col-md-9 content-show modal-coa-group-name"> Group</div>
                        <input type="hidden" id="modal-coa-group-id" name="group_id">
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Coa Category</label>
                        <div class="col-md-9 content-show modal-coa-category-name"> Category</div>
                        <input type="hidden" id="modal-coa-group-category-id" name="category_id">
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Account Number</label>
                        <div class="col-md-9">
                           <input type="text" name="number" id="number-coa-group" class="form-control" value="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Name *</label>
                        <div class="col-md-9">
                           <input type="text" name="name" id="name-coa-group" class="form-control" value="">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Has Subledger</label>
                        <div class="col-md-9">
                           <label class="switch switch-primary">
                                <input id="has-subledger-group" value="1" type="checkbox" onclick="showSubledger(this.checked, 'group')">
                                <span></span>
                            </label>
                        </div>
                    </div>
                    <div id="subledger-body-group" style="display:none">
                        <div class="form-group" id="subledger-type-group">
                            <label class="col-md-3 control-label">Subledger Type</label>
                            <div class="col-md-9">
                               <select name="subledger_type" id="select-subledger-group" class="selectize" style="width: 50%;" data-placeholder="Choose one.." tabindex="-1" aria-hidden="true">

                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="button-coa-group" onclick="validateCoaGroup()" class="btn btn-effect-ripple btn-primary">Save</button>
                <button type="button" class="btn btn-effect-ripple btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    function validateCoaGroup() {
        if($("#name-coa-group").val().trim()==""){
            swal('Failed', 'please fill all provided column');
            $("#name-coa-group").focus();
            return false;
        }

        storeCoaGroup();
    }

    function storeCoaGroup(){
        $("#button-coa-group").html("Saving...");
        $.ajax({
            type: 'POST',
            url: "{{URL::to('master/coa/insert-by-group')}}",
            data: $("#coa-create-group").serialize(),
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
