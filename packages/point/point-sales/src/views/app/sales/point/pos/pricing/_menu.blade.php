@if(auth()->user()->may('read.point.sales.pos.pricing'))
<a href="{{ url('sales/point/pos/pricing') }}" class="btn {{\Request::segment(5)==''?'btn-primary':'btn-info'}}">
    List
</a>
@endif
@if(auth()->user()->may('create.point.sales.pos.pricing'))
<a href="{{ url('sales/point/pos/pricing/create-step-1') }}" class="btn {{\Request::segment(5)==('create-step-1') || \Request::segment(5)==('create-step-2')?'btn-primary':'btn-info'}}">
    Create
</a>
@endif
@if(auth()->user()->may('create.role'))
<a href="{{ url('temporary-access/Point of Sales | Pricing/point sales pos pricing') }}" class="btn btn-info">
    Temporary Access
</a>
@endif
<a href="{{ url('sales/point/pos/pricing/import') }}" class="btn {{\Request::segment(5)==('import')?'btn-primary':'btn-info'}}">
    Import
</a>
<br/><br/>
