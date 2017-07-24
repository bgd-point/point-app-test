<div id="edit-modal" class="modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title"><strong>Edit Data</strong></h3>
            </div>
            <div class="modal-body">
                <form id="form_contact" action="#" method="post" class="form-horizontal form-bordered">
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label class="col-md-3 control-label">Group *</label>
                            <div class="col-md-9"> 
                                <input type="text" readonly id="group-edit" name="group" class="form-control">
                                <input type="hidden" readonly id="index"  class="form-control">
                                <input type="hidden" readonly id="row-id" name="row_id" class="form-control">
                                <input type="hidden" readonly id="person-type" name="person_type" value="{{$person_type->slug}}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Name *</label>
                            <div class="col-md-9"> 
                                <input type="text" id="name-edit" name="name" class="form-control ">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Email</label>
                            <div class="col-md-9"> 
                                <input type="text" id="email-edit" name="email" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Address</label>
                            <div class="col-md-9"> 
                                <input type="text" id="address-edit" name="address" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Phone</label>
                            <div class="col-md-9"> 
                                <input type="text" id="phone-edit" name="phone" class="form-control">
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
                <button type="button" id="button-contact" class="btn btn-effect-ripple btn-primary " onclick="validateContact()">Submit</button>
                <button type="button" class="btn btn-effect-ripple btn-danger" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>