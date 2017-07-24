<div id="edit-modal" class="modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title"><strong>Edit Data</strong></h3>
            </div>
            <div class="modal-body">
                <form id="form_service" action="#" method="post" class="form-horizontal form-bordered">
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label class="col-md-3 control-label">Name *</label>
                            <div class="col-md-9"> 
                                <input type="text" id="name-edit" name="name" class="form-control ">
                                <input type="hidden" readonly id="index"  class="form-control">
                                <input type="hidden" readonly id="row-id" name="row_id" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Price</label>
                            <div class="col-md-9"> 
                                <input type="text" id="price-edit" name="price" class="form-control format-quantity">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Notes</label>
                            <div class="col-md-9"> 
                                <input type="text" id="notes-edit" name="notes" class="form-control">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="button-service" class="btn btn-effect-ripple btn-primary " onclick="validateService()">Submit</button>
                <button type="button" class="btn btn-effect-ripple btn-danger" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>