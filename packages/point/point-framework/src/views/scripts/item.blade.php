<script>
    // GLOBAL VARIABLE
    var list_item = '';
    var list_item_having_quantity = '';

    function getItemQuantity(item_id, warehouse_id, form_date, time, callback, callback_type) {
        if (! item_id) {
            return false;
        }
        
        $.ajax({
            url: "{{URL::to('master/item/get-quantity')}}",
            type: 'GET',
            data: {
                item_id: item_id,
                warehouse_id: warehouse_id,
                form_date: form_date,
                time: time,
            },
            success: function(data) {
                if(callback_type == 'html') { 
                    $(callback).html(appNum(data.value));    
                } else if (callback_type == 'input') {
                    $(callback).val(appNum(data.value));
                }
            }
        });
    }

    function getItemUnit(item_id, callback, callback_type) {
        if (! item_id) {
            return false;
        }

        $.ajax({
            url: "{{URL::to('master/item/unit')}}",
            type: 'GET',
            data: { item_id: item_id },
            success: function(data) {
                if (callback_type == 'html') {
                    $(callback).html(data.default_name);
                } else if (callback_type = 'input') {
                    $(callback).val(data.default_name);
                }
            }
        });
    }

    function reloadItemHavingQuantity(element, reset = true) {
        $.ajax({
            url: "{{URL::to('master/item/list-having-quantity')}}",
            success: function (data) {
                var items = $(element)[0].selectize;
                if (reset === true) {
                    items.clear();
                }
                items.load(function (callback) {
                    callback(eval(JSON.stringify(data.lists)));
                });

            }, error: function (data) {
                swal('Failed', 'Something went wrong', 'error');
            }
        });
    }

    function reloadItemManufacture(element, reset = true) {
        $.ajax({
            url: "{{URL::to('master/item/list-item-manufacture')}}",
            success: function (data) {
                var items = $(element)[0].selectize;
                if (reset === true) {
                    items.clear();
                }
                items.load(function (callback) {
                    callback(eval(JSON.stringify(data.lists)));
                });

            }, error: function (data) {
                swal('Failed', 'Something went wrong', 'error');
            }
        });
    }

    function reloadItem(element, reset = true) {
        if (list_item == '') {
            populateJsonItem();
        }

        initSelectizeItem(element, reset);
    }

    function populateJsonItem() {
        $.ajax({
            url: "{{URL::to('master/item/list')}}",
            async: false,
            success: function (data) {
                list_item = data;
            }, error: function (data) {
                swal('Failed', 'Cannot reload master item', 'error');
            }
        });
    }

    function initSelectizeItem(element, reset) {
        var items = $(element)[0].selectize;
        if (reset === true) {
            items.clear();
        }
        items.load(function (callback) {
            callback(eval(JSON.stringify(list_item.lists)));
        });

        return true;
    }

    function getPrice(item_id, callback, callback_type) {
        $.ajax({
            url: "{{url('master/item/price')}}",
            data: { item_id: item_id },
            success: function (data) {
                if (callback_type == 'html') {
                    $(callback).html(data.price);
                } else if (callback_type = 'input') {
                    $(callback).val(data.price);
                }
            }
        })
    }


</script>
