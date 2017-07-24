<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <div class="btn-group pull-right">
                <a class="btn btn-default" data-dismiss="modal" onclick="createCoaByGroup({{$group->category->id}},'{{$group->category->name}}',{{$group->id}},'{{$group->name}}')" title="create coa"><i class="fa fa-plus"></i> Create Account</a>
                <a class="btn btn-default" data-dismiss="modal" onclick="editGroup({{$group->id}})" title="edit"><i class="fa fa-pencil"></i> Edit</a>
                <a class="btn btn-default" data-dismiss="modal" onclick="secureDelete({{$group->id}},'{{url('master/coa/group/'.$group->id.'/delete')}}')" title="delete"><i class="fa fa-times"></i> Delete</a>
            </div>
            <h3 class="modal-title"><strong>Detail</strong></h3>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="col-md-3 control-label">Coa Category</label>
                <div class="col-md-9 content-show">{{$group->category->name}}</div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">Account Number</label>
                <div class="col-md-9 content-show">{{$group->coa_number}}</div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">Name</label>
                <div class="col-md-9 content-show">{{$group->name}}</div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-effect-ripple btn-danger" data-dismiss="modal">Close</button>
        </div>
    </div>
</div>



