<script>
	function reloadService(element) {
	    var url = '{{url()}}/master/service/list';
	    $.ajax({
	        url: url,
	        success: function (data) {
	            var person = $(element)[0].selectize;
	            person.load(function (callback) {
	                callback(eval(JSON.stringify(data.lists)));
	            });

	        }, error: function (data) {
	            swal('Failed', 'Something went wrong', 'error');
	        }
	    });
	}

	function getPrice(service_id, callback, callback_type ) {
	    var url = '{{url()}}/master/service/get-price';
	    $.ajax({
	        url: url,
	        data : {service_id : service_id},
	        success: function (price) {
	        	if(callback_type == 'html') { 
                    $(callback).html(appNum(price));    
                } else if (callback_type == 'input') {
                    $(callback).val(appNum(price));
                }
	        }, error: function (data) {
	            swal('Failed', 'Something went wrong', 'error');
	        }
	    });
	}
</script>