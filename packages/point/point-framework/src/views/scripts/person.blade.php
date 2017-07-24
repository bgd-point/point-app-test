<script>
	function reloadPerson(element, type, reset = true) {
	    var url = '{{url()}}/master/contact/list-by-type/'+type;
	    $.ajax({
	        url: url,
	        success: function (data) {
	            var person = $(element)[0].selectize;
	            if (reset === true) {
                    person.clear();
                }
	            person.load(function (callback) {
	                callback(eval(JSON.stringify(data.lists)));
	            });

	        }, error: function (data) {
	            swal('Failed', 'Something went wrong', 'error');
	        }
	    });
	}
</script>