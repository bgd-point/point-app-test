<a href="{{ url('master/contact/'. $person_type->slug ) }}" class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
    List Data
</a>
<a href="{{ url('master/contact/'. $person_type->slug .'/group') }}" class="btn {{\Request::segment(4)=='group'?'btn-primary':'btn-info'}}">
    Group
</a>
<a href="{{ url('master/contact/'. $person_type->slug .'/create') }}" class="btn {{\Request::segment(4)=='create'?'btn-primary':'btn-info'}}">
    Create
</a>
<a href="{{ url('master/contact/'. $person_type->slug .'/import') }}" class="btn {{\Request::segment(4)=='import'?'btn-primary':'btn-info'}}">
    Import
</a>
<a href="{{ url('temporary-access/'. $person_type->slug .'/'.$person_type->slug) }}" class="btn {{\Request::segment(4)=='access'?'btn-primary':'btn-info'}}">
    Temporary Access
</a>

<br/><br/>
