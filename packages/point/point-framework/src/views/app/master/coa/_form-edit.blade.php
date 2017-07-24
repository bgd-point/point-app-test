<div class="form-group">
    <label class="col-md-3 control-label">Coa Category</label>
    <div class="col-md-9 content-show modal-coa-category"> {{$coa->category->name}}</div>
    <input type="hidden" id="modal-coa-category-id" value="{{$coa->category->id}}" name="category_id">
</div>

@if(count($list_group) > 0)
<div class="form-group">
    <label class="col-md-3 control-label">Group</label>
    <div class="col-md-9">
       <select name="group_id" id="select-group" class="selectize" style="width: 50%;" data-placeholder="Choose one.." tabindex="-1" aria-hidden="true">
            <option></option>
            @foreach($list_group as $group_coa)
                @if($coa->group)
                    <option value="{{$group_coa->id}}" @if($coa->group->id == $group_coa->id) selected @endif>{{$group_coa->name}}</option>
                @else
                <option value="{{$group_coa->id}}">{{$group_coa->name}}</option>
                @endif
            @endforeach
        </select>
    </div>
</div>
@endif

<div class="form-group">
    <label class="col-md-3 control-label">Account Number</label>
    <div class="col-md-9">
       <input type="text" name="number_coa_edit" id="number-coa-edit" value="{{$coa->coa_number}}" class="form-control">
    </div>
</div>
<div class="form-group">
    <label class="col-md-3 control-label">Name *</label>
    <div class="col-md-9">
       <input type="text" name="name_coa_edit" id="name-coa-edit" class="form-control" value="{{$coa->name}}">
    </div>
</div>
@if($coa->isUse())
<div class="form-group">
    <label class="col-md-3 control-label">Subledger Type</label>
    <div class="col-md-9 content-show">{{$coa->getSubledgerName()}}</div>
</div>
@else
<div class="form-group">
    <label class="col-md-3 control-label">Has Subledger</label>
    <div class="col-md-9">
        <label class="switch switch-primary">
            <input id="has-subledger-category" name="has_subledger" type="checkbox" @if($coa->has_subledger)  checked="" @endif onclick="showSubledgerEdit(this.checked, 'category')">
            <span></span>
        </label>
    </div>
</div>
<div id="edit-subledger-body-category" style="@if($coa->has_subledger) display:block @else display:none @endif ">
    <div class="form-group" id="edit-subledger-type-category">
        <label class="col-md-3 control-label">Subledger Type</label>
        <div class="col-md-9">
           <select name="subledger_type" id="select-subledger-category-edit" class="selectize" style="width: 50%;" data-placeholder="Choose one.." tabindex="-1" aria-hidden="true">
                <option ></option>
                <option value="person" @if($coa->subledger_type == 'Point\Framework\Models\Master\Person') selected="" @endif>Person</option>
                <option value="item" @if($coa->subledger_type == 'Point\Framework\Models\Master\Item') selected="" @endif>Item</option>
                @if($coa->isFixedAssetAccount())
                <option value="fixed_asset" @if($coa->subledger_type == 'Point\Framework\Models\FixedAsset') selected="" @endif>Fixed Asset</option>
                @endif
            </select>
        </div>
    </div>
</div>
@if($coa->isFixedAssetAccount() && $coa->subledger_type == 'Point\Framework\Models\FixedAsset')
<div class="form-group">
    <label class="col-md-3 control-label">Useful Period</label>
    <div class="col-md-9">
       <input type="text" name="useful_life" id="useful-coa-edit" class="form-control format-quantity" value="{{$coa->getUsefulLife()}}">
    </div>
</div>
@endif
@endif
<script type="text/javascript">
    initSelectize("#select-group");
    initSelectize("#select-subledger");
    var subledger = false;
    initFormatNumber();
    function showSubledgerEdit(subledger, key){
        if(subledger === true) {
            $("#edit-subledger-body-category").css("display","block");
            initFormatNumber();
        } else {
            $("#edit-subledger-body-category").css("display","none");
        }
        var select = initSelectize('#select-subledger-category-edit');
    }
</script>

