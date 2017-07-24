<div id="modal-bug-report" class="modal" tabindex="-1" role="dialog" data-backdrop="false" style="background-color: rgba(0, 0, 0, 0.6);" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title"><strong>Bug Report</strong></h3>
            </div>
            <div class="modal-body">
                <form id="bugs" action="#" method="post" enctype="multipart/form-data" class="form-horizontal form-bordered">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Title *</label>
                        <div class="col-md-9">
                            <input type="text" id="title-report" name="title" class="form-control" value="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Menu *</label>
                        <div class="col-md-9">
                            <select class="selectize" name="plugins">
                                <?php
                                    $plugins = array(
                                        'dashboard', 'master', 'inventory', 'sales', 'expedition',
                                        'manufacture', 'finance', 'accounting', 'facility'
                                    );
                                    for ($i=0; $i < count($plugins); $i++) {
                                        echo '<option value="'.$plugins[$i].'">'.$plugins[$i].'</option>';
                                    }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Message *</label>
                        <div class="col-md-9">
                            <textarea type="textarea" id="message-report" name="message" class="form-control autosize"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Screenshot</label>
                        <div class="col-md-9">
                            <input type="file" name="file" style="display:inline"/>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-effect-ripple btn-danger" data-dismiss="modal">Cancel</button>
                <button type="button" id="button" onclick="validateReport()" class="btn btn-effect-ripple btn-primary">Submit</button>
            </div>
        </div>
    </div>
</div>

<script>
    function validateReport() {
        if($("#title-report").val().trim()=="" || $("#message-report").val().trim()==""){
            swal('Failed', 'please fill required input');
            $("#title-report").focus();
        } else {
            sendReport();
        }
    }

    function sendReport(){
        var data = new FormData(document.getElementById("bugs"));

        $("#button").html("Process ...");
        $.ajax({
            type:"POST",
            url: "{{URL::to('/bug-report')}}",
            data: data,
            mimeType:"multipart/form-data",
            contentType: false,
            cache: false,
            processData:false,
            dataType: 'json',
            success: function(result){
                if(result.status == "success"){
                    $('.modal').modal('hide');
                    swal('success', 'Send Report Success',"success");
                } else {
                    swal('Failed', 'Something when wrong');
                    $("#button").html("Send Report");
                    $("#title-report").val("");
                    $("#message-report").val("");
                    $("#title-report").focus();
                }
            }
        });
    }
</script>
