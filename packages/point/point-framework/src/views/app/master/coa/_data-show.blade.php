<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <div class="btn-group pull-right">
                <a class="btn btn-default" data-dismiss="modal" onclick="editCoa({{$coa->id}})" title="edit"><i class="fa fa-pencil"></i> Edit</a>
                <a class="btn btn-default" data-dismiss="modal" onclick="state({{$coa->id}})" title="disable"><i class="fa @if($coa->disabled == 1) fa-play @else fa-pause @endif"></i> @if($coa->disabled == 1) Enable @else Disable @endif</a>
                <a class="btn btn-default" data-dismiss="modal" onclick="secureDelete({{$coa->id}},'{{url('master/coa/'.$coa->id.'/delete')}}')" title="delete"><i class="fa fa-times"></i> Delete</a>
            </div>
            <h3 class="modal-title"><strong>Detail</strong></h3>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="col-md-3 control-label">Coa Category</label>
                <div class="col-md-9 content-show coa-category-name">{{$coa->category->name}}</div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">Group</label>
                <div class="col-md-9 content-show">@if($coa->group) {{$coa->group->name}} @else - @endif</div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">Account Number</label>
                <div class="col-md-9 content-show">{{$coa->coa_number}}</div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">Name</label>
                <div class="col-md-9 content-show">{{$coa->name}}</div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label">Subledger Type</label>
                <div class="col-md-9 content-show">{{ $coa->getSubledgerName() }}</div>
            </div>
            @if($coa->isFixedAssetAccount())
            <div class="form-group">
                <label class="col-md-3 control-label">Useful Period</label>
                <div class="col-md-9 content-show">{{number_format_quantity($coa->getUsefulLife())}} year</div>
            </div>
            @endif
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-effect-ripple btn-danger" data-dismiss="modal">Close</button>
        </div>
    </div>
</div>
