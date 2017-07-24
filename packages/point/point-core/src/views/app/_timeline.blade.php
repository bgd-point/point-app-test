<li>
    <div class="timeline-time">{{ date_format_view($timeline->created_at) }} {{ date('H:i', strtotime($timeline->created_at)) }} </div>
    <div class="timeline-icon"><img src="@include('core::app._avatar', ['user_id' => $timeline->user_id])" alt="avatar" style="width:100%;height:100%"></div>
    <div class="timeline-content">
        <p class=""><strong>{{ $timeline->user->name }}</strong></p>
        <p class="">{!! $timeline->message !!}</p>
    </div>
</li>