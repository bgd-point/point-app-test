<div id="modal-unit" class="modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title"><strong>New Unit</strong></h3>
            </div>
            <div class="modal-body">
                <form id="unit" action="#" method="post" class="form-horizontal form-bordered">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Name *</label>
                        <div class="col-md-9">
                           <input type="text" name="name" id="name-unit" class="form-control" value="">
                           <input type="hidden" readonly name="index" id="index-unit" class="form-control" value="">
                           <input type="hidden" readonly name="key" id="key-unit" class="form-control" value="">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="button-unit" onclick="validateUnit()" class="btn btn-effect-ripple btn-primary">Save</button>
                <button type="button" class="btn btn-effect-ripple btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    function validateUnit() {
        if($("#name-unit").val().trim()==""){
            swal("Failed", "Please complete the fill.");
            $("#name-unit").focus();
        }else{
            storeUnit();        
        }
    }
    
    function storeUnit(){
        data = $("#unit").serialize();
        key = $("#key-unit").val();
        index = $("#index-unit").val();
        
        $("#button-unit").html("Saving...");
        $.ajax({
            type:'POST',
            url: "{{URL::to('master/item/unit_master/ajax-insert')}}",
            data:data,
            success: function(result){
                if(result.status == "failed"){
                    swal("Failed"," Please use another name", 'success');
                    resetForm();
                    $("#name-unit").focus();
                    return false;
                }
                addNewUnitInAllSelectize(result, index, key);
                $('.modal').modal('hide');
            
            }, error: function(result){
               swal("Failed","something went wrong", "error");
            }
        });
    }
</script>
