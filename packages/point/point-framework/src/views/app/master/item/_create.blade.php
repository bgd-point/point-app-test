<div id="modal-item" class="modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title"><strong>New Item</strong></h3>
            </div>
            <div class="modal-body">
                <form id="item" action="#" method="post" class="form-horizontal form-bordered">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Asset Account *</label>
                        <div class="col-md-6">
                            <select id="account-asset-id" name="account_asset_id" class="selectize">
                                <option value="">Choose your asset account</option>
                                @foreach($list_account_asset as $item_account)
                                    <option value="{{ $item_account->id }}">{{ $item_account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Category *</label>
                        <div class="col-md-6">
                            <select id="item-category-id" name="item_category_id" onchange="selectCategoryItem(this.value)" class="selectize">
                                <option value="">Choose your category</option>
                                @foreach($list_item_category as $item_category)
                                    <option value="{{ $item_category->id }}">{{ $item_category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Code *</label>
                        <div class="col-md-6">
                            <input readonly type="text" id="code" name="code" class="form-control" value="{{old('code')}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Name *</label>
                        <div class="col-md-6">
                            <input type="text" name="name" id="name" class="form-control" value="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Default Unit *</label>
                        <div class="col-md-6">
                            <select id="default-unit" name="default_unit" class="selectize">
                                <option value="">Choose your unit</option>
                                @foreach($list_item_unit as $unit)
                                    <option value="{{ $unit->name }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>
                        <div class="col-md-6">
                            <input type="text" name="notes" id="notes" class="form-control" value="">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <input type="hidden" name="callback_type" id="callback-type">
                <input type="hidden" name="callback_id" id="callback-id">
                <input type="hidden" name="callback_data" id="callback-data">
                <button type="button" id="button-item" onclick="storeItem()" class="btn btn-effect-ripple btn-primary">Save</button>
                <button type="button" class="btn btn-effect-ripple btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    /**
     * Open modal form item
     * @param  {[string]} callback_type x: selectize, input, html
     * @param  {[string]} callback_id x: #item-input
     * @param  {[string]} callback_data x: codeName / name / notes
     * @return {[json]}               [description]
     */
    function openModalItem(callback_type, callback_id, callback_data) {
        $("#callback-type").val(callback_type);
        $("#callback-id").val(callback_id);
        $("#callback-data").val(callback_data);
    }

    function validate() {
        name = $("#name").val();
        code = $("#code").val();
        account_asset_id = $("#account-asset-id").val();
        item_category_id = $("#item-category-id").val();
        default_unit = $("#default-unit").val();

        if (name == "" || code == "" || account_asset_id == "" || item_category_id == "" || default_unit == "") {
            swal("Failed", "Please complate required form");
            return false;
        }

        return true;
    }

    function storeItem(){
        if (!validate()){
            return false;
        }

        data = $("#item").serialize();
        $("#button-item").html("Saving...");
        $.ajax({
            type:'POST',
            url: "{{URL::to('master/item/create/ajax')}}",
            data:data,
            success: function(result){
                $("#button-item").html("Save");
                if (result.status == 'failed') {
                    swal("Failed","Please select another name", "error");
                    return false;
                }
                
                callback(result);
            }, error: function(result){
               swal("Failed","something went wrong", "error");
               closeModal();
            }
        });
    }

    function callback(data) {
        var callback_type = $("#callback-type").val();
        var callback_id = $("#callback-id").val();
        var callback_data = $("#callback-data").val();
        if (callback_type == 'selectize') {
            var items = $(callback_id)[0].selectize;
            items.addOption(data.lists);
            items.addItem(data.id);
        } else if(callback_type == 'input') {
            $(callback_id).val(data.callback_data);
        } else if(callback_type == 'html') {
            $(callback_id).html(data.callback_data);
        }

        closeModal();
    }

    function closeModal(argument) {
        $("#name").val("");
        $("#code").val("");
        $("#notes").val("");
        $("#default-unit").val("");
        $("#button-item").html("Save");
        category = $("#item-category-id")[0].selectize;
        category.clear();
        account = $("#account-asset-id")[0].selectize;
        account.clear();
        unit = $("#default-unit")[0].selectize;
        unit.clear();
        $("#modal-item").modal('hide');
    }

    function selectCategoryItem(category_id){
        $.ajax({
            url: '{{url("master/item/code")}}',
            data: {
                item_category_id: category_id
            },
            success: function(data) {
                $('#code').val(data.code);
            }
        });
    }

</script>
