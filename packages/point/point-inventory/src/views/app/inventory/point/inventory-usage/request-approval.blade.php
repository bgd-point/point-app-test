@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-inventory::app/inventory/point/inventory-usage/_breadcrumb')
        <li>Request approval</li>
    </ul>
    <h2 class="sub-header">Inventory Usage</h2>
    @include('point-inventory::app.inventory.point.inventory-usage._menu')
    
    <form action="{{url('inventory/point/inventory-usage/send-request-approval')}}" method="post">
        {!! csrf_field() !!}

        <div class="panel panel-default">
            <div class="panel-body">            
                <div class="table-responsive">
                    {!! $listInventoryUsage->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center">
                                    <input type="checkbox" id="check-all">
                                </th>
                                <th>Form Date</th>
                                <th>Form Number</th>
                                <th>Warehouse</th>
                                <th>Approval To</th>
                                <th>Last Request</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($listInventoryUsage as $inventory_usage)
                             <tr id="list-{{$inventory_usage->formulir_id}}">
                                <td class="text-center">
                                    <input type="checkbox" name="formulir_id[]" value="{{$inventory_usage->formulir_id}}">
                                </td>
                                <td>{{ date_format_view($inventory_usage->formulir->form_date) }}</td>
                                <td><a href="{{ url('inventory/point/inventory-usage/'.$inventory_usage->id) }}">{{ $inventory_usage->formulir->form_number}}</a></td>
                                <td>{{ $inventory_usage->warehouse->codeName }}</td>
                                <td>{{ $inventory_usage->formulir->approvalTo->name }}</td>
                                <td>
                                    @if($inventory_usage->formulir->request_approval_at != '0000-00-00 00:00:00' and $inventory_usage->formulir->request_approval_at != null)
                                        {{ date_format_view($inventory_usage->formulir->request_approval_at, true) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @endforeach  
                        </tbody> 
                    </table>
                    {!! $listInventoryUsage->render() !!}
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-effect-ripple btn-primary">Send Request</button>
                    </div>
                </div>
            </div>
        </div>          
    </form>
</div>
@stop
