@extends('core::app.layout')

@section('content')
<div id="page-content" class="inner-sidebar-left">

     @include('core::app.settings._sidebar')

     @include('core::app.error._alert')
 
    <div class="panel panel-default"> 
        <table class="table table-striped">
            <thead>
                <tr>
                    <th colspan="2" class="text-left">End Notes</th>
                </tr>
                @foreach($list_end_notes as $end_notes)
                <tr>
                    <td class="text-center">
                        <a href="#" onclick="editNotes({{$end_notes->id}})" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>
                    </td>
                    <td>
                        <h4 class="text-dark"><strong id="feature-{{$end_notes->id}}">{{$end_notes->feature}}</strong></h4>
                        <div class="text-muted" id="notes-{{$end_notes->id}}">{{$end_notes->notes}}</div>
                    </td>
                </tr>
                @endforeach
            </thead>
        </table>
    </div>
</div>

<div id="modal-notes" class="modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title" id="title-notes">Test</h3>
            </div>
            <div class="modal-body">
                <form id="end-notes" action="#" method="post" class="form-horizontal form-bordered">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes *</label>
                        <div class="col-md-9">
                            <input type="hidden" name="id" id="id">
                            <textarea id="notes" name="notes" class="form-control autosize">
                                {!! nl2br(e(old('notes'))) !!}
                            </textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="button-notes" onclick="saveEndNotes()" class="btn btn-effect-ripple btn-primary">Save</button>
                <button type="button" class="btn btn-effect-ripple btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('scripts')
<script>
    function editNotes(id) {
        var feature = $("#feature-"+id).html();
        var notes = $("#notes-"+id).html();
        $("#title-notes").html(feature);
        $("#id").val(id);
        $("#notes").val(notes);
        $("#modal-notes").modal();
    }
    function saveEndNotes() {
        $("#button-notes").html("Saving...");
        $.ajax({
            type:'POST',
            url: "{{URL::to('settings/end-notes/update')}}",
            data: $("#end-notes").serialize(),
            success: function(data){
                if (data.status == 'success') {
                    $("#notes-"+data.id).html(data.notes);
                    $("#button-notes").html("Save");
                    $('#modal-notes').modal('hide');
                } else {
                    $("#button-notes").html("Save");
                    $('#modal-notes').modal('hide');
                    swal('failed', 'Something went wrong');
                }
            },
            error: function (data) {
                swal('failed', 'Something went wrong');
            }
        });
    }

</script>
@stop
