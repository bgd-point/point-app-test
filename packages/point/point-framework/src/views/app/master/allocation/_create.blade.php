<div id="modal-allocation" class="modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title"><strong>New Allocation</strong></h3>
            </div>
            <div class="modal-body">
                <form id="allocation" name="allocation" action="#" method="post" class="form-horizontal form-bordered">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Name *</label>
                        <div class="col-md-9">
                            <input type="text" name="name" required id="name-allocation" class="form-control" value="">
                            <input type="hidden" name="index" id="index">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="button-allocation" onclick="validateAjaxAllocation()" class="btn btn-effect-ripple btn-primary">Save</button>
                <button type="button" class="btn btn-effect-ripple btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    function validateAjaxAllocation() {
        if($("#name-allocation").val().trim()==""){
            $("#name-allocation").focus();
            swal("please insert allocation name.");
        } else {
            storeAjaxAllocation();
        }
    }

    function storeAjaxAllocation(){
        data = $("#allocation").serialize();
        index = $("#index").val();
        $("#button-allocation").html("Saving...");
        
        $.ajax({
            url: '{{url("master/allocation/ajax-create")}}',
            type: 'POST',
            data: data,
            success: function(result){
                if(result.status == "failed"){
                    swal('error', 'name already used');
                    resetAjaxAllocation();
                    $("#name-allocation").focus();
                    return false;
                }

                addNewAllocationInSelectize(index, result);
                $('.modal').modal('hide');
                resetAjaxAllocation();
            }, error: function(result){
                swal('Failed', 'Something went wrong', 'error');
            }
        });
    }

    function resetAjaxAllocation(index){
        $("#index").val(index);
        $("#button-allocation").html("Save");
        $("#name-allocation").val("");
    }

    function reloadAllocationInSelectize(allocation_id) {
        $.ajax({
            url: "{{URL::to('master/allocation/list')}}",
            success: function(data) {
                var allocation = $(allocation_id)[0].selectize;
                allocation.load(function(callback) {
                    callback(eval(JSON.stringify(data.lists)));
                });
            }, error: function(e){
                swal('Failed', 'Something went wrong', 'error');
            }
        });
    }

    function addNewAllocationInSelectize(index, result) {
        var selectize = $("#allocation-id-"+index)[0].selectize;
        selectize.addOption({value:result.code,text:result.name});
        selectize.addItem(result.code);

        for (var i = 0; i < counter; i++) {
            if(i != index){
                if($('#allocation-id-'+i).length != 0){
                    var allocation = $('#allocation-id-'+i)[0].selectize;
                    allocation.addOption({value:result.code,text:result.name});
                }
            }
        };
    }
</script>
