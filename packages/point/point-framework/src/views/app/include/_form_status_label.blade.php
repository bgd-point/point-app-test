@if($form_status == 0)
    <label class="label label-warning" data-toggle="tooltip" data-original-title="Form Status Open"><i class="fa fa-file-text"></i> Open</label>
@elseif($form_status == 1)
    <label class="label label-success" data-toggle="tooltip" data-original-title="Form Status Closed"><i class="fa fa-file-text"></i> Closed</label>
@elseif($form_status == -1)
    <label class="label label-danger" data-toggle="tooltip" data-original-title="Form Status Canceled"><i class="fa fa-file-text"></i> Canceled</label>
@endif
