<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th width="100px" class="text-center"></th>                                
            <th>NAME</th>
        </tr>
    </thead>
    <tbody>
        @foreach($list_bank as $bank)
        <tr>
            <td class="text-center">
                <a href="javascript:void(0)" data-toggle="tooltip" title="Edit" onclick="edit({{$bank->id}}, '{{$bank->name}}')" class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-pencil"></i></a>
                <a href="javascript:void(0)" data-toggle="tooltip" title="delete" onclick="confirmation({{$bank->id}})" class="btn btn-effect-ripple btn-xs btn-danger"><i class="fa fa-trash"></i></a>
            </td> 
            <td><a href="{{ url('master/bank/'.$bank->id) }}">{{ $bank->name }}</a></td>
        </tr>
        @endforeach  
    </tbody> 
</table>
<script type="text/javascript">
    function edit (id, name) {
        $("#bank-id").val(id);
        $("#name").val(name);
    }

    function confirmation(id) {
        swal({
            title: "Are you sure?",
            text: "You will not be able to recover this data!",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#DD6B55',
            confirmButtonText: 'Yes, I am sure!',
            cancelButtonText: "No, cancel it!",
            closeOnConfirm: true,
            closeOnCancel: true
        },

        function(isConfirm){
            if (isConfirm){
                remove(id);
            } else {
                return false;
            }
        });
    }

    function remove (id) {
        var url = "{{url()}}/master/bank/delete/"+id;
        $.ajax({
            url: url,
            type: 'POST',
            success: function(res) {
                $('#bank-data').html(res);
                clearForm();
            }
        })
    }

</script>