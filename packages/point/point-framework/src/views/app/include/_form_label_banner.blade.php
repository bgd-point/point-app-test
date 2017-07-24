@if($canceled_at != null)
    <div class="form-group">
        <div class="col-md-12">
            <div class="alert alert-danger alert-dismissable">
                <h1 class="text-center"><strong>Canceled</strong></h1>
            </div>
        </div>
    </div>
@endif

@if($archived != null)
    <div class="form-group">
        <div class="col-md-12">
            <div class="alert alert-danger alert-dismissable">
                <h1 class="text-center"><strong>Archived</strong></h1>
            </div>
        </div>
    </div>
@endif

@if($approval_status == 1)
    <div class="form-group">
        <div class="col-md-12">
            <div class="alert alert-success alert-dismissable">
                <h1 class="text-center"><strong>Approved</strong></h1>
            </div>
        </div>
    </div>
@endif

@if($approval_status == -1)
    <div class="form-group">
        <div class="col-md-12">
            <div class="alert alert-danger alert-dismissable">
                <h1 class="text-center"><strong>Rejected</strong></h1>
            </div>
        </div>
    </div>
@endif