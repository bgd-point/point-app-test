@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        <li><a href="{{ url('finance') }}">Finance</a></li>
        <li>Report</li>
    </ul>
    <h2 class="sub-header">{{$type}} Report</h2>

    <div class="panel panel-default">
        <div class="panel-body">
            <form id="search" action="#" method="post" class="form-horizontal form-bordered">
                {!! csrf_field() !!}
                <input type="hidden" name="type" value="{{$type}}">
                <div class="form-group">
                    <label class="col-md-3 control-label">Period</label>
                    <div class="col-sm-6">
                        <div class="input-group input-daterange" data-date-format="{{date_format_masking()}}">
                            <input type="text" name="date_from" class="form-control date input-datepicker"
                                   placeholder="From"
                                   value="{{\Input::get('date_from') ? \Input::get('date_from') : ''}}">
                            <span class="input-group-addon"><i class="fa fa-chevron-right"></i></span>
                            <input type="text" name="date_to" class="form-control date input-datepicker"
                                   placeholder="To"
                                   value="{{\Input::get('date_to') ? \Input::get('date_to') : ''}}">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Account</label>
                    <div class="col-md-6">
                        <select id="coa-id" name="coa_id" class="selectize" >
                            @foreach($list_coa as $coa)
                                <option value="{{$coa->id}}">{{$coa->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Subledger</label>
                    <div class="col-md-6">
                        <select id="subledger-id" name="subledger_id" class="selectize">
                            <option value="0">all</option>
                            @foreach($list_person as $person)
                                <option value="{{$person->id}}">{{$person->codeName}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3"></label>
                    <div class="col-md-6">
                        <input type="button" id="button-report" onclick="view()" value="Submit" class="btn btn-effect-ripple btn-info">
                    </div>
                </div>
                
                <div class="report-view" style="margin: 20px;">
                        
                </div>
            </form>
        </div>
    </div>  
</div>
@stop

@section('scripts')
<script>
$(function() {
    initSelectize('#coa-id');
    initSelectize('#subledger-id' );
})

function view() {
    $("#button-report").val("Loading..");
    var data = $("#search").serialize();
    $.ajax({
        url: "{{URL::to('finance/point/debt-report/view')}}",
        type: 'POST',
        data: data,
        success: function(data) {
            $('.report-view').html(data);
            $("#button-report").val("Submit");
            initSelectize('#approver');
        }, error: function(data) {
            $("#button-report").val("Submit");
        }
    });
}

function sendApproval() {
    $data = $("#search").serialize();
    $("#btn-send-approval").attr("disabled", true);
    $.post("{{URL::to('finance/point/debt-report/'.$type)}}", $data, (response) => {
        notification('send approval success');
    })
    .fail((error) => {
        notification('send approval fail');
    })
    .always(function() {
        $("#btn-send-approval").removeAttr("disabled");
    });
}
</script>
@stop
