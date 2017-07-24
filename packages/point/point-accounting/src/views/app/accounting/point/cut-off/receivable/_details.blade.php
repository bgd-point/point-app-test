<form id="details" action="#" method="post" class="form-horizontal form-bordered">
    <div id="modal-detail" class="modal"  tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h3 class="modal-title"><strong><span id="modal-coa-name-cutoff-receivable"></span></strong></h3>
                </div>
                <div id="modal-body-cutoff-receivable" class="modal-body">

                </div>
                <div class="modal-footer" id="modal-footer">
                    <input type="hidden" name="modal_coa_id" id="modal-coa-id">
                    <input type="hidden" id="index">
                    <input type="hidden" id="key">
                            
                    <button type="button" id="button-coa" onclick="validateStore()" class="btn btn-effect-ripple btn-primary">Submit</button>
                    <button type="button" class="btn btn-effect-ripple btn-danger" onclick="confirm()">Clear</button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>

function validateStore() {
    if($('#total').val() == "0"){ return false;}
    storeAjaxDetails();
}

function storeAjaxDetails(){
    data = $("#details").serialize();
    var index = $("#index").val();
    $.ajax({
        type:'POST',
        url: "{{URL::to('accounting/point/cut-off/receivable/store-tmp')}}",
        data:data,
        success: function(result){
            $("#row-amount-"+index).val(accountingNum(result.total));
            $('.modal').modal('hide');
            reCalculate();

        }, error: function(result){
            console.log(result.status);
        }
    });
}

function confirm() {
    swal({
        title: "Are you sure?",
        text: "Data will be remove from database",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, delete it!",
        closeOnConfirm: true
    },
    function() {
        clearTmpDetail();       
    }); 
}
function clearTmpDetail(){
    data = $("#details").serialize();
    var index = $("#index").val();
    $.ajax({
        type:'POST',
        url: "{{URL::to('accounting/point/cut-off/receivable/clear-tmp-detail')}}",
        data:data,
        success: function(result){
            $("#row-amount-"+index).val("0");
            $('.modal').modal('hide');
            swal("success", "Data has been removed", "success");
            reCalculate();

        }, error: function(result){
            console.log(result.status);
        }
    });   
}
</script>
