<script>
    function reloadItemFixedAssets(element, reset = true) {
        $.ajax({
            url: "{{URL::to('master/fixed-assets-item/list')}}",
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

    function getAttributeItemFixedAssets(item_id, attribute, callback, callback_type) {
        $.ajax({
            url: "{{url('master/fixed-assets-item/get-attribute')}}",
            data:
                { 
                    item_id: item_id,
                    attribute: attribute
                },
            success: function (data) {
                if (callback_type == 'html') {
                    $(callback).html(data);
                } else if (callback_type = 'input') {
                    $(callback).val(data);
                }
            }
        })
    }


</script>
