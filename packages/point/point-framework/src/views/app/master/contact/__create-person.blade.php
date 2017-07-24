<div id="modal-contact" class="modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title"><strong>New Person</strong></h3>
            </div>
            <div class="modal-body">
                <form id="contact" name="contact" action="#" method="post" class="form-horizontal form-bordered">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Type *</label>
                        <div class="col-md-9">
                            <select id="type_person" name="type_person" class="selectize" onchange="selectType(this.value)" required>
                                <option value="">-- Select type person --</option>
                                <option value="supplier">Supplier</option>
                                <option value="customer">Customer</option>
                                <option value="expedition">Expedition</option>
                            </select>
                            <input type="hidden" name="slug" id="slug">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Group *</label>
                        <div class="col-md-9">
                            <select id="person_group_id" name="person_group_id" class="selectize" required>
                                <option value="">Choose your group</option>
                                
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Code *</label>
                        <div class="col-md-9">
                            <input type="text" name="code" required id="code-contact" readonly class="form-control" value="">
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

    function selectType(type) {
        if (!type)
            return false;

        $("#slug").val(type);
        $.ajax({
            url: '{{url("master/contact/group/")}}',
            type: 'GET',
            data: {
                slug: type
            },
            success: function(data) {
                console.log(data);
                var selectize = $("#person_group_id")[0].selectize;
                selectize.clear();
                selectize.clearOptions();
                selectize.load(function(callback) {
                    callback(eval(JSON.stringify(data.lists)));
                    selectize.addItem(data.defaultId);
                });
                $("#code-contact").val(data.code);
            }, error: function(data) {
                swal('Failed', 'Something went wrong', 'error');
            }
        });

    }
    function validateAjaxContact() {
        if($("#slug").val().trim()=="" || $("#name-contact").val().trim()=="" || $("#code-contact").val()=="" ){
            swal("Failed","Please fill all input.", "error");
            return false;
        }
        storeAjaxContact();
    }

    function storeAjaxContact(){
        var data = $("#contact").serialize();
        var slug = $("#slug").val().toLowerCase();
        var url = "{{url()}}/master/contact/insert/"+slug;
        
        $("#button-contact").html("Saving...");
        $.ajax({
            type:'POST',
            url: url,
            data:data,
            success: function(result){
                console.log(result);
                if(result.status == "failed"){
                    swal("Please, fill all input.", "error");
                    resetAjaxContact();
                    $("#name-contact").focus();
                    return false;
                }

                var selectize = $("#person_id")[0].selectize;
                var codeName = result.name;
                selectize.addOption({value:result.id,text:codeName});
                selectize.addItem(result.id);
                $('.modal').modal('hide');
                resetAjaxContact();
            }, error: function(result){
                swal('Failed', 'Something went wrong', 'error');
            }
        });
    }

    function resetAjaxContact(){
        $("#button-contact").html("Save");
        $("#name-contact").val("");
        $("#code-contact").val("");
        var selectize = $("#type_person")[0].selectize;
        selectize.clear();
        var selectize = $("#person_group_id")[0].selectize;
        selectize.clear();
    }
</script>
