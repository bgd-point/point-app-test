@if(access_is_allowed_to_view('read.coa'))
<a href="{{ url('master/coa/') }}" class="btn {{\Request::segment(3)==''?'btn-primary':'btn-info'}}">
    List Data
</a>
@endif
@if(access_is_allowed_to_view('update.coa'))
<a href="{{ url('master/coa/setting-journal') }}" class="btn {{\Request::segment(3)=='setting-journal'?'btn-primary':'btn-info'}}">
    Setting Journal
</a>
@endif
@if(access_is_allowed_to_view('create.role'))
<a href="{{ url('temporary-access/chart of accounts/coa') }}" class="btn {{\Request::segment(3)=='access'?'btn-primary':'btn-info'}}">
    Temporary Access
</a>
@endif

<br/><br/>
