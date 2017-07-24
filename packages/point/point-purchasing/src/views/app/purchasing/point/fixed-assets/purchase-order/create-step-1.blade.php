@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.fixed-assets._breadcrumb')
            <li><a href="{{ url('purchasing/point/fixed-assets/purchase-order') }}">Purchase Order</a></li>
            <li>Create step 1</li>
        </ul>
        <h2 class="sub-header">Purchase Order</h2>
        @include('point-purchasing::app.purchasing.point.fixed-assets.purchase-order._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                <a href="{{ url('purchasing/point/fixed-assets/purchase-order/basic/create') }}" class="btn btn-info">
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
                            <th>Order</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($list_purchase_requisition as $purchase_requisition)
                            <tr>
                                <td class="text-center">
                                    <a href="{{ url('purchasing/point/fixed-assets/purchase-order/create-step-2/'.$purchase_requisition->id) }}"
                                       class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-external-link"></i>
                                        Create Purchase Order</a>
                                </td>
                                <td>
                                    {{ date_format_view($purchase_requisition->formulir->form_date) }}
                                    <br/> <a
                                            href="{{ url('purchasing/point/fixed-assets/purchase-requisition/'.$purchase_requisition->id) }}">{{ $purchase_requisition->formulir->form_number}}</a>
                                </td>
                                <td>{{ $purchase_requisition->formulir->notes }}</td>
                                <td>
                                    <a href="{{ url('master/contact/employee/'.$purchase_requisition->employee_id) }}">{{ $purchase_requisition->employee->codeName}}</a>
                                </td>
                                <td>
                                    @foreach($purchase_requisition->details as $purchase_requisition_detail)
                                        {{ $purchase_requisition_detail->coa->name }}
                                        {{ $purchase_requisition_detail->name }}
                                        = {{ number_format_quantity($purchase_requisition_detail->quantity) }} {{$purchase_requisition_detail->unit}}
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
