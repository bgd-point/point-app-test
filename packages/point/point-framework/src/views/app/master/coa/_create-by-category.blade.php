<div id="modal-coa" class="modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title"><strong>New Coa</strong></h3>
            </div>
            <div class="modal-body">
                <form id="coa-create" action="#" method="post" class="form-horizontal form-bordered">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Coa Category</label>
                        <div class="col-md-9 content-show modal-coa-category"> Category</div>
                        <input type="hidden" id="modal-coa-category-id" name="category_id">

                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Account Number</label>
                        <div class="col-md-9">
                           <input type="text" name="coa_number" id="number-coa" class="form-control" value="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Name *</label>
                        <div class="col-md-9">
                           <input type="text" name="name" id="name-coa" class="form-control" value="">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Has Subledger</label>
                        <div class="col-md-9">
                            <label class="switch switch-primary">
                                <input id="has-subledger-category" name="has_subledger" type="checkbox" onclick="showSubledger(this.checked, 'category')">
                                <span></span>
                            </label>
                        </div>
                    </div>
                    <div id="subledger-body-category" style="display:none">

                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="button-coa" onclick="validateCoa()" class="btn btn-effect-ripple btn-primary">Save</button>
                <button type="button" class="btn btn-effect-ripple btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    function createCoaByCategory(category_id, category_name){
        manipulateSubledger(category_name, 'category', '#subledger-body-category');
        $("#has-subledger-category").prop('checked', false);
        $("#subledger-body-category").css("display","none");

        $(".modal-coa-category").html(category_name);
        $("#modal-coa-category-id").val(category_id);
        $("#number-coa").val("");
        $("#name-coa").val("");
        $("#button-coa").html("Submit");
        $("#modal-coa").modal();
    }

    function createCoaByGroup(category_id, category_name, group_id, group_name){
        manipulateSubledger(category_name, 'group', '#subledger-body-group');
        $("#has-subledger-group").prop('checked', false);
        $("#subledger-body-group").css("display","none");

        $(".modal-coa-group-name").html(group_name);
        $(".modal-coa-category-name").html(category_name);
        $("#modal-coa-group-category-id").val(category_id);
        $("#modal-coa-group-id").val(group_id);
        $("#number-coa-group").val("");
        $("#name-coa-group").val("");
        $("#button-coa-group").html("Submit");

        $("#modal-coa-group").modal();
    }

    function validateCoa() {
        if($("#name-coa").val().trim()==""){
            swal('Failed', 'please fill all provided column');
            $("#name-coa").focus();
            return false;
        }

        if($("#useful-life").val() ==""){
            swal('Failed', 'please fill all provided column');
            $("#useful-life").focus();
            return false;
        }

        storeCoa();
    }

    function storeCoa(){
        $("#button-coa").html("Saving...");
        $.ajax({
            type:'POST',
            url: "{{URL::to('master/coa/insert-by-category')}}",
            data:$("#coa-create").serialize(),
            success: function(result){
                if(result.status == "failed"){
                    swal('Failed', 'Name or account number already exist, please use another name or account number');
                    $("#name-coa").focus();
                    $("#button-coa").html("Submit");
                } else {
                    $('.modal').modal('hide');
                    loadIndex();
                }
            }
        });
    }
</script>
