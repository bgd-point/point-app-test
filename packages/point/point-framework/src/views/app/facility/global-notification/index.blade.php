@extends('core::app.layout')

@section('scripts') 
<script src="{{asset('core/themes/appui-backend/js')}}/plugins/ckeditor/ckeditor.js"></script>
<script>
	CKEDITOR.config.toolbar = [
	   ['Format','FontSize','Bold','Italic','Underline'],
	] ;

    function apply(message)
    { 
        $.ajax({
            url: "{{URL::to('facility/global-notification/apply')}}",
            type: 'GET',
            data: {
                message: message
            },
            success: function(data) {
                notification(data['title'], data['msg']);
            }, error: function(data) {
                notification(data['title'], data['msg']);
            }
        });
    }
</script>
@stop

@section('content')
<div id="page-content">
    <a href="{{url('facility')}}" class="pull-right">
        <i class="fa fa-arrow-circle-left push-bit"></i> Back
    </a>
    <h2 class="sub-header">Global Notification</h2>

    @include('core::app.error._alert')

    <div class="panel panel-default"> 
        <div class="panel-body">
            <div class="form-horizontal form-bordered">
                {!! csrf_field() !!}
                <div class="form-group">
                    <label class="col-md-3 control-label">Message</label>
                    <div class="col-md-9">
                        <textarea id="message" name="message" class="form-control ckeditor"></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-6 col-md-offset-3">
                        <button type="button" onclick="apply(CKEDITOR.instances['message'].getData())" class="btn btn-effect-ripple btn-primary">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </div>  
</div>
@stop