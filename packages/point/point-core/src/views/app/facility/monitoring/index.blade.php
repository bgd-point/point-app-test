@extends('core::app.layout')

@section('scripts')
<script>
	socket.on('{{config('point.client.channel')}}:Timeline', function( data )
    {
    	var message = data.message;
    	$(data.message).prependTo('#timeline').hide().fadeIn('slow');
    	$('#notif_audio')[0].play();
    });

    function more()
    { 
        $.ajax({
            url: "{{URL::to('/facility/monitoring/more')}}",
            type: 'GET',
            data: {
                date_input: $('#date-input').val(),
                user_id: $('#user-id').val(),
                last_id: $('#last-id').val()
            },
            success: function(data) {
            	$(data.content).appendTo('#timeline').hide().fadeIn('slow');
                $('#last-id').val(data.last_id);
                if(data.last_id == 0) {
                    notification('No More Data','');
                    $('#more').hide();
                }                
            }
        });
    }

    function search()
    {
    	$.ajax({
            url: "{{URL::to('/facility/monitoring/search')}}",
            type: 'GET',
            data: {
                date_input: $('#date-input').val(),
                user_id: $('#user-id').val(),
                last_id: $('#last-id').val()
            },
            success: function(data) {
                $('#last-id').val(data.last_id);
                if(data['reset'] == true) {
                	$('#timeline').html('');
                	$(data.content).prependTo('#timeline').hide().fadeIn('slow');
                	$('#more').show();
                }                
            }
        });
    }
</script>
@stop

@section('content')
<div id="page-content">

	<ul class="breadcrumb breadcrumb-top">
		<li><a href="{{ url('facility') }}">Facility</a></li>
		<li>Monitoring</li>
	</ul>
	<h2 class="sub-header">Monitoring</h2>

	<div class="row">
        <div class="col-md-12">
		    <div class="block">
		    	<div class="form-horizontal">
	                <div class="form-group">
	                    <div class="col-sm-3">
	                        <input type="text" id="date-input" class="form-control date input-datepicker" data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}">
	                    </div>
	                    <div class="col-sm-3">
	                        <select id="user-id" name="user-id" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="chooseUserId(this.value)">
                                <option value="0">All</option><!-- Required for data-placeholder attribute to work with Select2 plugin -->
                                @foreach($users as $user)
                                <option value="{{$user->id}}" @if(old('user') == $user->id) selected @endif>{{$user->name}}</option>
                                @endforeach
                            </select>
	                    </div>
	                    <div class="col-sm-6">
	                        <button type="button" class="btn btn-effect-ripple btn-effect-ripple btn-primary" onclick="search()"><i class="fa fa-search"></i> Search</button> 
	                    </div>
	                </div>
	            </div>

		        <div class="timeline block-content-full">
		            <ul id="timeline" class="timeline-list">
		            	@foreach($timelines as $timeline)
		                <li>
		                    <div class="timeline-time">{{ date_format_view($timeline->created_at) }} {{ date('H:i', strtotime($timeline->created_at)) }} </div>
		                    <div class="timeline-icon"><img src="@include('core::app._avatar', ['user_id' => $timeline->user_id])" alt="avatar" style="width:40px;height:40px"></div>
		                    <div class="timeline-content">
		                        <p class=""><strong>{{ $timeline->user->name }}</strong></p>
		                        <p class="">{!! $timeline->message !!}</p>
		                    </div>
		                </li>
		                @endforeach
		            </ul>

					<br/>

					@if($timelines->count())
		            <ul class="timeline-list" id="more">
		            	<li>
		            		<input type="hidden" name="last-id" id="last-id" value="{{$timeline->id}}"/>
		                	<button class="btn btn-block btn-default" onclick="more($('#last_id').val())">More ...</button>
		                    <div class="timeline-time">&nbsp;</div>
		                     
		                    <div class="timeline-content">
		                        <p class="">&nbsp;</p>
		                        <p class="">&nbsp;</p>
		                    </div>
		                </li>  
		            </ul>
					@endif

		        </div>
		    </div>
		</div>
	</div>
</div>
@stop
