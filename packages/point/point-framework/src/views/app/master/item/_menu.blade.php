@if(access_is_allowed_to_view('read.item'))
<a href="{{ url('master/item/') }}" class="btn {{\Request::segment(3)==''?'btn-primary':'btn-info'}}">
    List Data
</a>
@endif
@if(access_is_allowed_to_view('create.item'))
<a href="{{ url('master/item/unit_master') }}" class="btn {{\Request::segment(3)=='unit_master'?'btn-primary':'btn-info'}}">
    Unit
</a>
<a href="{{ url('master/item/category') }}" class="btn {{\Request::segment(3)=='category'?'btn-primary':'btn-info'}}">
    Category
</a>
<a href="{{ url('master/item/create') }}" class="btn {{\Request::segment(3)=='create'?'btn-primary':'btn-info'}}">
    Create
</a>
@endif
@if(access_is_allowed_to_view('create.user'))
<a href="{{ url('master/item/import') }}" class="btn {{\Request::segment(3)=='import'?'btn-primary':'btn-info'}}">
    Import
</a>
@endif
@if(access_is_allowed_to_view('update.coa'))
<a href="{{ url('master/item/journal') }}" class="btn {{\Request::segment(3)=='journal'?'btn-primary':'btn-info'}}">
    Journal
</a>
@endif
@if(access_is_allowed_to_view('create.role'))
<a href="{{ url('temporary-access/item/item') }}" class="btn {{\Request::segment(3)=='access'?'btn-primary':'btn-info'}}">
    Temporary Access
</a>
@endif

<br/><br/>
