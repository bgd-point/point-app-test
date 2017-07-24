@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-inventory::app/inventory/point/stock-opname/_breadcrumb')
        <li>Request approval</li>
    </ul>
    <h2 class="sub-header">Inventory Usage</h2>
    @include('point-inventory::app.inventory.point.stock-opname._menu')
    
    <form action="{{url('inventory/point/stock-opname/send-request-approval')}}" method="post">
        {!! csrf_field() !!}

        <div class="panel panel-default">
            <div class="panel-body">            
                <div class="table-responsive">
                    {!! $listStockOpname->render() !!}
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
                            @foreach($listStockOpname as $stock_opname)
                             <tr id="list-{{$stock_opname->formulir_id}}">
                                <td class="text-center">
                                    <input type="checkbox" name="formulir_id[]" value="{{$stock_opname->formulir_id}}">
                                </td>
                                <td>{{ date_format_view($stock_opname->formulir->form_date) }}</td>
                                <td><a href="{{ url('inventory/point/stock-opname/'.$stock_opname->id) }}">{{ $stock_opname->formulir->form_number}}</a></td>
                                <td>{{ $stock_opname->warehouse->codeName }}</td>
                                <td>{{ $stock_opname->formulir->approvalTo->name }}</td>
                                <td>
                                    @if($stock_opname->formulir->request_approval_at != '0000-00-00 00:00:00' and $stock_opname->formulir->request_approval_at != null)
                                        {{ date_format_view($stock_opname->formulir->request_approval_at, true) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @endforeach  
                        </tbody> 
                    </table>
                    {!! $listStockOpname->render() !!}
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
