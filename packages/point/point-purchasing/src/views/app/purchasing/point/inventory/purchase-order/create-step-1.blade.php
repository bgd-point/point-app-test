@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li><a href="{{ url('purchasing/point/purchase-order') }}">Purchase Order</a></li>
            <li>Create step 1</li>
        </ul>
        <h2 class="sub-header">Purchase Order</h2>
        @include('point-purchasing::app.purchasing.point.inventory.purchase-order._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                <a href="{{ url('purchasing/point/purchase-order/basic/create') }}" class="btn btn-info">
                    Create New Purchase Order
                </a>
                @if($list_purchase_requisition->count())
                <br><br>
                <h3>Create From Purchase Requisition</h3>
                <div class="table-responsive">
                    {!! $list_purchase_requisition->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th width="100px" class="text-center"></th>
                            <th>PURCHASE REQUISITION</th>
                            <th>Notes</th>
                            <th>Employee</th>
                            <th>Supplier</th>
                            <th>Order</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($list_purchase_requisition as $purchase_requisition)
                            <tr>
                                <td>
                                    <a href="{{ url('purchasing/point/purchase-order/create-step-2/'.$purchase_requisition->id) }}"
                                       class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-external-link"></i>
                                        Create Purchase Order
                                    </a>
                                </td>
                                <td>
                                    {{ date_format_view($purchase_requisition->formulir->form_date) }}
                                    <br/> 
                                    <a href="{{ url('purchasing/point/purchase-requisition/'.$purchase_requisition->id) }}">{{ $purchase_requisition->formulir->form_number}}</a>
                                </td>
                                <td>{{ $purchase_requisition->formulir->notes }}</td>
                                <td>
                                    {!! get_url_person($purchase_requisition->employee_id) !!}
                                </td>
                                <td>
                                    @if($purchase_requisition->supplier_id)
                                        {!! get_url_person($purchase_requisition->supplier_id) !!}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @foreach($purchase_requisition->items as $purchase_requisition_item)
                                        {{ $purchase_requisition_item->item->codeName }}
                                        = {{ number_format_quantity($purchase_requisition_item->quantity) }} {{ $purchase_requisition_item->unit }}
                                        <br/>
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $list_purchase_requisition->render() !!}
                </div>
                @endif
            </div>
        </div>
    </div>
@stop
