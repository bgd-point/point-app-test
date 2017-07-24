@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-inventory::app/inventory/point/transfer-item/_breadcrumb')
        <li>Request approval</li>
    </ul>
    <h2 class="sub-header">Transfer Item</h2>
    @include('point-inventory::app.inventory.point.transfer-item._menu')
    
    <form action="{{url('inventory/point/transfer-item/send/send-request-approval')}}" method="post">
        {!! csrf_field() !!}

        <div class="panel panel-default">
            <div class="panel-body">            
                <div class="table-responsive">
                    {!! $listTransferItem->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center">
                                    <input type="checkbox" id="check-all">
                                </th>
                                <th>Form Date</th>
                                <th>Form Number</th>
                                <th>From Warehouse</th>
                                <th>To Warehouse</th>
                                <th>Approval To</th>
                                <th>Last Request</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($listTransferItem as $transfer_item)
                             <tr id="list-{{$transfer_item->formulir_id}}">
                                <td class="text-center">
                                    <input type="checkbox" name="formulir_id[]" value="{{$transfer_item->formulir_id}}">
                                </td>
                                <td>{{ date_format_view($transfer_item->formulir->form_date) }}</td>
                                <td><a href="{{ url('inventory/point/transfer-item/send/'.$transfer_item->id) }}">{{ $transfer_item->formulir->form_number}}</a></td>
                                <td>{{ $transfer_item->warehouseFrom->codeName }}</td>
                                <td>{{ $transfer_item->warehouseTo->codeName }}</td>
                                <td>{{ $transfer_item->formulir->approvalTo->name }}</td>
                                <td>
                                    @if($transfer_item->formulir->request_approval_at != '0000-00-00 00:00:00' and $transfer_item->formulir->request_approval_at != null)
                                        {{ date_format_view($transfer_item->formulir->request_approval_at, true) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @endforeach  
                        </tbody> 
                    </table>
                    {!! $listTransferItem->render() !!}
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
