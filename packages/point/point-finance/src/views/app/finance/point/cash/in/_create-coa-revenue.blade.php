<div id="modal-coa-revenue" class="modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title"><strong>New COA Revenue</strong></h3>
            </div>
            <div class="modal-body">
                <form id="coa" name="coa" action="#" method="post" class="form-horizontal form-bordered">
                    <div class="form-group">
                        <label class="col-md-3 control-label">COA Category</label>
                        <div class="col-md-9">
                            <select id="coa_category" name="coa_category" class="selectize" required>
                                <option value="">-- Select COA Category --</option>
                                @foreach($list_coa_category_revenue as $coa_category_revenue)
                                    <option value="{{$coa_category_revenue->id}}">{{$coa_category_revenue->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Name *</label>
                        <div class="col-md-9">
                            <input type="text" name="name" required id="name-coa" class="form-control" value="">
                            <input type="hidden" name="index" id="index">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="button-coa" onclick="validateAjaxCoa()" class="btn btn-effect-ripple btn-primary">Save</button>
                <button type="button" class="btn btn-effect-ripple btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    function validateAjaxCoa() {
        if($("#name-coa").val().trim()=="" ){
            swal("Please, fill all input.");
        }else{
            storeAjaxCoa();
        }
    }

    function storeAjaxCoa(){
        data = $("#coa").serialize();
        index = $("#index").val();
        $("#button-coa").html("Saving...");
        $.ajax({
            url: '{{url("master/coa/ajax/create")}}',
            type: 'POST',
            data: data,
            success: function(result){
                console.log(result);
                if(result.status == "failed"){
                    swal("Please, fill all input.");
                    resetAjaxCoa();
                    $("#name-coa").focus();
                }else{
                    console.log(result);
                    addNewCoaInSelectize(index, result);
                    $('.modal').modal('hide');
                    resetAjaxCoa();
                }

            }, error: function(result){
                console.log(result.status);
            }
        });
    }

    function resetAjaxCoa(){
        $("#button-coa").html("Save");
        $("#name-coa").val("");
    }
</script>
