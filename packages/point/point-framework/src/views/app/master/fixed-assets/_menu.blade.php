@if(access_is_allowed_to_view('read.fixed.assets.item'))
<a href="{{ url('master/fixed-assets-item/') }}" class="btn {{\Request::segment(3)==''?'btn-primary':'btn-info'}}">
    List Data
</a>
@endif
@if(access_is_allowed_to_view('create.fixed.assets.item'))
<a href="{{ url('master/fixed-assets-item/create') }}" class="btn {{\Request::segment(3)=='create'?'btn-primary':'btn-info'}}">
    Create
</a>
@endif
@if(access_is_allowed_to_view('create.role'))
<a href="{{ url('temporary-access/fixed-assets-item/fixed assets item') }}" class="btn {{\Request::segment(3)=='access'?'btn-primary':'btn-info'}}">
    Temporary Access
</a>
@endif

<br/><br/>
