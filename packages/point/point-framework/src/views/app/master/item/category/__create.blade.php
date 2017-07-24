<div id="modal-category" class="modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title"><strong>New Category</strong></h3>
            </div>
            <div class="modal-body">
                <form id="category" action="#" method="post" class="form-horizontal form-bordered">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Code *</label>
                        <div class="col-md-9">
                            <input type="text" id="code-category" name="code" class="form-control" value="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Name *</label>
                        <div class="col-md-9">
                            <input type="text" name="name" id="name-category" class="form-control" value="">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="button" onclick="validate()" class="btn btn-effect-ripple btn-primary">Save</button>
                <button type="button" class="btn btn-effect-ripple btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    function validate() {
        if($("#code-category").val().trim()=="" || $("#name-category").val().trim()==""){
            swal('Failed', 'please fill all provided column');
            $("#code-category").focus();
            return false;
        }
        storeCategory();
    }

    function storeCategory(){
        data = $("#category").serialize();
        $("#button").html("Saving...");
        $.ajax({
            type:"POST",
            url: "{{URL::to('master/item/category/insert/?')}}",
            data:data,
            success: function(result){
                if(result.status == "failed"){
                    swal('Failed', 'Code already exist, please use another code','error');
                    resetForm();
                    $("#code-category").focus();
                    return false;
                }

                var selectize = $("#item_category_id")[0].selectize;
                selectize.addOption({value:result.code,text:result.name}); 
                selectize.addItem(result.code); 
                $('.modal').modal('hide');
            
            }, error: function(e){
                swal('Failed', 'Something went wrong', 'error');
            }
        });
    }
</script>
