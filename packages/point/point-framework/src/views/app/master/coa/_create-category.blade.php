<div id="modal-create-category" class="modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title"><strong>New Coa Category</strong></h3>
            </div>
            <div class="modal-body">
                <form id="form-coa-category-create" action="#" method="post" class="form-horizontal form-bordered">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Coa Category</label>
                        <div class="col-md-9 content-show modal-coa-position"> Position</div>
                        <input type="hidden" readonly="" id="modal-coa-position-id" name="position_id">
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Group *</label>
                        <div class="col-md-9">
                            <select class="selectize" id="group-id" name="group_id" style="width: 100%;" data-placeholder="Choose one..">
                                <option></option>
                                @foreach($list_coa_group_category as $group)
                                <option value="{{$group->id}}">{{$group->name}}</option>
                                @endforeach
                            </select>
                           
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
                <button type="button" id="button-save-category" onclick="validationCategroy()" class="btn btn-effect-ripple btn-primary">Save</button>
                <button type="button" class="btn btn-effect-ripple btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    function createCategory(position_id, position_name) {
        $("#modal-create-category").modal();
        $("#modal-coa-position-id").val(position_id);
        $(".modal-coa-position").html(position_name);
        $("#name-category").val("");
        $("#button-save-category").html('Submit');
        
    }

    function validationCategroy() {
        var group = $("#group-id option:selected").text();

        if($("#name-category").val().trim()==""){
            swal('Failed', 'please fill all provided column');
            $("#name-category").focus();
            return false;
        }

        if(group ==""){
            swal('Failed', 'please fill all provided column');
            return false;
        }

        storeCategory();
    }

    function storeCategory(){
        $("#button-save-category").html("Saving...");
        $.ajax({
            type:'POST',
            url: "{{URL::to('master/coa/category/store')}}",
            data:$("#form-coa-category-create").serialize(),
            success: function(result){
                console.log(result);
                if(result.status == "failed"){
                    swal('Failed', 'Name or account number already exist, please use another name or account number');
                    $("#name-category").focus();
                    $("#button-save-category").html("Submit");
                } else {
                    $('#modal-create-category').modal('hide');
                    loadIndex();
                }
            }
        });
    }
</script>
