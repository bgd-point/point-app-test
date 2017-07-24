<div id="edit-modal" class="modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title"><strong>Edit Data</strong></h3>
            </div>
            <div class="modal-body">
                <form id="form_item" action="#" method="post" class="form-horizontal form-bordered">
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label class="col-md-3 control-label">Asset Account *</label>
                            <div class="col-md-9"> 
                                <input type="text" readonly id="coa-edit" name="coa" class="form-control">
                                <input type="hidden" readonly id="index"  class="form-control">
                                <input type="hidden" readonly id="row-id" name="row_id" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Category *</label>
                            <div class="col-md-9"> 
                                <input type="text" readonly id="category-edit" name="category" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Warehouse *</label>
                            <div class="col-md-9"> 
                                <input type="text" readonly id="warehouse-edit" name="warehouse" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Name *</label>
                            <div class="col-md-9"> 
                                <input type="text" id="name-edit" name="name" class="form-control ">
                                <input type="hidden" readonly id="unit-edit" name="unit"  class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Quantity *</label>
                            <div class="col-md-9"> 
                                <input type="text" id="quantity-edit" name="quantity" class="form-control format-quantity">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Cost Of Sale *</label>
                            <div class="col-md-9"> 
                                <input type="text" id="cos-edit" name="cos" class="form-control format-quantity">
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
                <button type="button" id="button-item" class="btn btn-effect-ripple btn-primary " onclick="validateItem()">Submit</button>
                <button type="button" class="btn btn-effect-ripple btn-danger" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>