@if(auth()->user()->may('read.bumi.deposit'))
<a href="{{ url('facility/bumi-deposit/deposit') }}" class="btn {{\Request::segment(4)==''?'btn-primary':'btn-info'}}">
    {{trans('framework::framework/global.button.list')}}
</a>
@endif
@if(auth()->user()->may('create.bumi.deposit'))
<a href="{{ url('facility/bumi-deposit/deposit/create') }}" class="btn {{\Request::segment(4)=='create'?'btn-primary':'btn-info'}}">
    {{trans('framework::framework/global.button.create')}}
</a>
@endif
@if(auth()->user()->may('create.role'))
<a href="{{ url('temporary-access/deposit/bumi deposit') }}" class="btn btn-info">
    {{trans('framework::framework/global.button.access')}}
</a>
@endif
<br/><br/>
