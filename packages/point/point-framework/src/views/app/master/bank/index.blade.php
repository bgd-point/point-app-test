@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('core::app/master/_breadcrumb')
        <li>Bank</li>
    </ul>

    <h2 class="sub-header">Bank</h2>
    @include('framework::app.master.bank._menu')

    <div class="panel panel-default">
        <div class="panel-body">
            <div class="form-group col-sm-6">
                <form action="#" method="post" id="form-bank" class="form-horizontal">
                    <label>Name *</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="bank name">
                    <input type="hidden" id="bank-id" name="bank_id" class="form-control">
                    <input type="button" class="btn btn-effect-ripple btn-md btn-info" value="submit" onclick="save()"/>
                </form>
            </div>
            <div class="clearfix"></div>
            
            <br/>
            <div class="table-responsive" id="bank-data">
                @include('framework::app.master.bank._data')
            </div>
        </div>
    </div>  
</div>
@stop

@section('scripts')
<script type="text/javascript">
    function save () {
        var data = $("#form-bank").serialize();
        var url = '{{url("master/bank/store")}}';
        if ($("#bank-id").val() != "") {
            url = '{{url("master/bank/update")}}';
        }

        if ($('#name').val() == "") {
            swal('Error', 'Failed, please complete the fill');
            return false;
        }

        $.ajax({
            url: url,
            data: data,
            type: 'POST',
            success: function(res) {
                $('#bank-data').html(res);
                clearForm();
            }
        });
    }

    function clearForm() {
        $("#name").val("");
        $("#bank-id").val("");
    }

    
</script>
@stop
