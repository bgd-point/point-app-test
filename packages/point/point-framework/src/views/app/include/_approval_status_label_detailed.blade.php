@if($approval_status == -1)
    <label class="label label-danger">Approval : Rejected</label>
    <i class="fa fa-calendar"></i> {{ date_format_view($approval_at) }}
    <i class="fa fa-user"></i> {{$approval_to}}
    <hr>
    {{$approval_message}}
@elseif($approval_status == 0)
    <label class="label label-warning">Approval : Pending</label>
@elseif($approval_status == 1)
    <label class="label label-success">Approval : Approved</label>
    <i class="fa fa-calendar"></i> {{ date_format_view($approval_at) }}
    <i class="fa fa-user"></i> {{$approval_to}}
    <hr>
    {{$approval_message}}
@endif
