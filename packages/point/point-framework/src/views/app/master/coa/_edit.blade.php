<div id="edit-coa" class="modal form-horizontal form-bordered" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="form-edit-coa" action="#" method="post" class="">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h3 class="modal-title"><strong>Edit Coa</strong></h3>
                </div>
                <div class="modal-body modal-edit">
                    
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="coa_id" id="coa-id-edit">
                    <button type="button" id="button-coa" onclick="validateEditCoa()" class="btn btn-effect-ripple btn-primary">Save</button>
                    <button type="button" class="btn btn-effect-ripple btn-danger" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">

    function editCoa(coa_id){
        var category_name = $(".coa-category-name").html();
        $("#edit-coa").modal();
        $("#coa-id-edit").val(coa_id);
        $.ajax({
            url: '{{url("master/coa/load-edit-form")}}',
            data: {
                coa_id: coa_id,
            },
            success: function(data) {
                $(".modal-edit").html(data);
                initSelectize('#select-subledger-category-edit');
            }
        });
    }

    function validateEditCoa() {
        if($("#name-coa-edit").val().trim()==""){
            swal('Failed', 'please fill all provided column');
            $("#name-coa").focus();
            return false;
        }

        storeEditCoa();
    }

    function storeEditCoa(){
        var data = $("#form-edit-coa").serialize();
        $("#button-coa").html("Saving...");
        $.ajax({
            type:'POST',
            url: "{{URL::to('master/coa/update-by-category')}}",
            data:data,
            success: function(result){
                if(result.status == "failed"){
                    swal('Failed', 'Name already exist, please use another name');
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
