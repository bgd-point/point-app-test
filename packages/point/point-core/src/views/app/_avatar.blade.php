@if(file_exists('uploads/avatar/' . config('point.client.slug') . '/' . $user_id . '.jpg'))
	{{asset('uploads/avatar/'. config('point.client.slug') . '/'.$user_id.'.jpg')}}
@else
	{{asset('core/assets/img/avatar/avatar.jpg')}}
@endif
