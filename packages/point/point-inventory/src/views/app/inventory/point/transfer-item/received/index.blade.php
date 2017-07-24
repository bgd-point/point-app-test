@extends('core::app.layout')
@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        <li><a href="{{ url('inventory') }}">Inventory</a></li>
        <li>Transfer Item</li>
    </ul>
    <h2 class="sub-header">Transfer Item</h2>
    @include('point-inventory::app.inventory.point.transfer-item._menu')

    <div class="panel panel-default">
        <div class="panel-body">
           
               <div class="table-responsive">
                {!! $transfer_item->appends(['search'=>app('request')->get('search')])->render() !!}
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Date Send</th>
                            <th>Form number</th>
                            <th>From Warehouse</th>
                            <th>To Warehouse</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transfer_item as $list_transfer_item)

                        @if ($list_transfer_item->formulir->approval_status == 1 && $list_transfer_item->formulir->form_status == 0)
                        <tr id="list-{{$list_transfer_item->id}}">
                            <td><a href="{{ url('inventory/point/transfer-item/received/create/'.$list_transfer_item->id) }}"><button type="submit" class="btn btn-effect-ripple btn-primary">Receive</button></a></td>
                            <td>{{date_format_view($list_transfer_item->formulir->form_date)}}</td>
                            <td><a href="{{ url('inventory/point/transfer-item/send/'.$list_transfer_item->id) }}">{{ $list_transfer_item->formulir->form_number}}</a></td>
                            <td>{{$list_transfer_item->warehouseFrom->codeName}}</td>
                            <td>{{$list_transfer_item->warehouseTo->codeName}}</td>
                        </tr>
                        @endif

                        @endforeach  
                    </tbody> 
                </table>
                {!! $transfer_item->appends(['search'=>app('request')->get('search')])->render() !!}
            </div>
        </div>
    </div>  
</div>
@stop
