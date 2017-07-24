<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <div class="btn-group pull-right">
                <a class="btn btn-default" data-dismiss="modal" onclick="createCoaByCategory({{$category->id}}, '{{$category->name}}')" title="create coa"><i class="fa fa-plus"></i> Create Account</a>
                <a class="btn btn-default" data-dismiss="modal" onclick="createGroup({{$category->id}}, '{{$category->name}}')" title="create group"><i class="fa fa-plus"></i> Create Group</a>
                @if($category->name == 'Fixed Assets')
                <a class="btn btn-default" data-dismiss="modal" onclick="linkedAccountDeprecition()" title="linked account depreciation"><i class="fa fa-eye"></i> Linked Account Depreciation</a>
                @endif
                <a class="btn btn-default" data-dismiss="modal" onclick="secureDelete({{$category->id}},'{{url('master/coa/category/'.$category->id.'/delete')}}')" title="delete"><i class="fa fa-times"></i> Delete</a>
            </div>
            <h3 class="modal-title"><strong>Detail</strong></h3>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="col-md-3 control-label">Coa Position</label>
                <div class="col-md-9 content-show">{{$category->position->name}}</div>
            </div>
            @if($category->coa_group_category_id)
            <div class="form-group">
                <label class="col-md-3 control-label">Coa Group</label>
                <div class="col-md-9 content-show">{{ $category->groupCategory->name}}</div>
            </div>
            @endif
            <div class="form-group">
                <label class="col-md-3 control-label">Name</label>
                <div class="col-md-9 content-show">{{$category->name}}</div>
            </div>
            
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-effect-ripple btn-danger" data-dismiss="modal">Close</button>
        </div>
    </div>
</div>



