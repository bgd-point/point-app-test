@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        <li><a href="{{ url('finance') }}">Finance</a></li>
        <li>Debts Aging Report</li>
    </ul>
    <h2 class="sub-header">Debts Aging Report</h2>

    <div class="panel panel-default">
        <div class="panel-body">
            <form id="search" action="#" method="post" class="form-horizontal form-bordered">
                {!! csrf_field() !!}
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
                    <div class="col-md-3">
                        <button type="button" id="button" onclick="view()" class="btn btn-effect-ripple btn-primary">View</button>
                    </div>
                </div>
            </div>
            <br/>
                <div class="report-view" style="margin: 20px;">
                    
                </div>                
            </div>
        </div>
    </div>  
</div>
@stop

@section('scripts')
<script>
$(function() {
    $('#coa-id', '#subledger-id' ).selectize({
        preload: true,
        sortField: [[]],
        initData: true
    });
})

function view()
{
    var data = $("#search").serialize();

    $.ajax({
        url: "{{URL::to('finance/point/debts-aging-report/view')}}",
        type: 'POST',
        data: data,
        success: function(data) {
            $('.report-view').html(data);
        }
    });
}
</script>
@stop
