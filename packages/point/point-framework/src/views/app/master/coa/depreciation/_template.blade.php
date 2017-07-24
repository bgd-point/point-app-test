<div id="modal-template-account-depreciation" class="modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title"><strong>Account Depreciation</strong></h3>
            </div>
            <div class="modal-body" id="template-fixed-asset" style="height:300px; overflow: auto"></div>
            <div class="modal-footer" id="footer-template-depreciation"></div>
        </div>
    </div>
</div>

<script>
    function linkedAccountDeprecition() {
        $("#modal-template-account-depreciation").modal();
        var button_close = '<button type="button" class="btn btn-effect-ripple btn-danger" data-dismiss="modal">Close</button>';
        $("#footer-template-depreciation").html(button_close);
        $.ajax({
            url: '{{url("master/coa/depreciation/show")}}',
            success: function(data) {
                $("#template-fixed-asset").html(data);
            }
        });
    }
</script>
