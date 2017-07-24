<div id="modal-warehouse" class="modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title"><strong>New Warehouse</strong></h3>
            </div>
            <div class="modal-body">
                <form id="warehouse" action="#" method="post" class="form-horizontal form-bordered">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Cash Account *</label>
                        <div class="col-md-9">
                            <select id="petty_cash_account" name="petty_cash_account" class="selectize">
                                <option value="">Choose your cash account</option>
                                @foreach($list_petty_cash_account as $petty_cash_account)
                                    <option value="{{ $petty_cash_account->id }}">{{ $petty_cash_account->name }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" id="index" value="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Name *</label>
                        <div class="col-md-9">
                            <input type="text" name="name" id="name-warehouse" class="form-control" value="">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="button-warehouse" onclick="validateWarehouse()" class="btn btn-effect-ripple btn-primary">Save</button>
                <button type="button" class="btn btn-effect-ripple btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    function validateWarehouse() {
        
        if($("#petty_cash_account").val()=="" || $("#name-warehouse").val().trim()==""){
            swal('Failed', 'please fill all provided column');
            $("#code-warehouse").focus();
        } else {
            storeWarehouse();
        }
    }

    function storeWarehouse(){
        data = $("#warehouse").serialize();
        $("#button-warehouse").html("Saving...");
        $.ajax({
            type:'POST',
            url: "{{URL::to('master/warehouse/insert')}}",
            data:data,
            success: function(result){
                if(result.status == "failed"){
                    swal('Failed', 'Code already exist, please use another name', 'error');
                    resetForm();
                    $("#name-warehouse").focus();

                    return false;
                }
                
                for(var x=0; x < counter; x++){
                    if($('#warehouse_id'+x).length != 0){
                        var selectize = $("#warehouse_id"+x)[0].selectize;
                        selectize.addOption({value:result.id,text:result.name});
                    }
                }
                var selectize = $("#warehouse_id"+$("#index").val())[0].selectize;
                selectize.addOption({value:result.id,text:result.name});
                selectize.addItem(result.id);
                $('.modal').modal('hide');
                
            },error:function(result) {
                swal("Failed", "Something went wrong", 'error');
            }
        });
    }
</script>
