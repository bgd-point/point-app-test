@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('core::app/master/_breadcrumb')
            <li><a href="{{ url('master/coa') }}">Chart Of Account</a></li>
            <li>Setting Jurnal</li>
        </ul>

        <h2 class="sub-header">Setting Journal</h2>
        @include('framework::app.master.coa._menu')
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="form-horizontal form-bordered">
                    <fieldset>
                        <legend><i class="fa fa-angle-right"></i> Setting Journal</legend>
                    </fieldset>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">Feature</label>
                        <div class="col-md-6">
                            <select id="choose" name="group_id" size="1" onchange="selectGroupJournal(this.value)" class="selectize">
                                <option value="">Choose Group Journal</option>
                                @foreach($setting_journal as $setting_journal_group)
                                    <option value="{{ $setting_journal_group->id}}" @if(old('id')==$setting_journal_group->id) selected @endif>{{ $setting_journal_group->group }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div id="setting-group">

                    </div>

                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-3">
                            <a href="{{ url('master/coa') }}" class="btn btn-effect-ripple btn-primary">Done</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script>
        function selectGroupJournal(id){
            $.ajax({
                url: '{{url("master/coa/setting-journal/select-group")}}',
                data: {
                    id: id
                },
                success: function(data) {
                    $('#setting-group').html(data.groups);
                    initSelectize('.groups-selectize');
                }
            });
        }

        function updateCoa(setting_journal_id, coa_id) {
            $.ajax({
                url: "{{URL::to('master/coa/setting-journal/update-setting-journal')}}",
                type: 'POST',
                data: {
                    setting_journal_id: setting_journal_id,
                    coa_id: coa_id
                },
                success: function(data) {
                    notification('success', 'setting journal has been set');
                },error: function(data){
                    notification('Failed', 'Something went wrong');
                }
            });
        }
    </script>
@stop
