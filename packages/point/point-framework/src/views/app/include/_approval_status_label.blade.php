@if($approval_status == -1)
    <label class="label label-danger" data-toggle="tooltip" data-original-title="{{date_format_view($approval_at, true)}}"><i class="fa fa-file-text"></i> Rejected</label>
@elseif($approval_status == 0)
    <label class="label label-warning" data-toggle="tooltip" data-original-title="Approval Pending"><i class="fa fa-file-text"></i> Pending</label>
@elseif($approval_status == 1)
    <label class="label label-success" data-toggle="tooltip" data-original-title="{{date_format_view($approval_at, true)}}"><i class="fa fa-file-text"></i> Approved</label>
@endif
