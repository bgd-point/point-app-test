<div id="modal-coa" class="modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title"><strong>New Coa</strong></h3>
            </div>
            <div class="modal-body">
                <form id="coa" action="#" method="post" class="form-horizontal form-bordered">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Name *</label>
                        <div class="col-md-9">
                           <input type="text" name="name" id="name-coa" class="form-control" value="">
                        </div>
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

    function validateCoa() {
        if($("#name-coa").val().trim()==""){
            swal('Failed', 'please fill all provided column');
            $("#name-coa").focus();
        } else {
            storeCoa();
        }
    }

    function storeCoa(){
        data = $("#coa").serialize();
        $("#button-coa").html("Saving...");
        $.ajax({
            type:'POST',
            url: "{{URL::to('master/coa/ajax-insert')}}",
            data:data,
            success: function(result){
                if(result.status == "failed"){
                    swal('Failed', 'Code already exist, please use another code');
                    resetForm();
                    $("#code-category").focus();
                } else {
                    var selectize = $("#account_asset_id")[0].selectize;
                    selectize.addOption({value:result.code,text:result.name}); 
                    selectize.addItem(result.code); 
                    $('.modal').modal('hide');
                }
            }
        });
    }

</script>
