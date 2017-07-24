@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li><a href="{{ url('purchasing/point/purchase-requisition') }}">Purchase Requisition</a></li>
            <li>Request approval</li>
        </ul>
        <h2 class="sub-header">Purchase Requisition</h2>
        @include('point-purchasing::app.purchasing.point.inventory.purchase-requisition._menu')

        <form action="{{url('purchasing/point/purchase-requisition/send-request-approval')}}" method="post">
            {!! csrf_field() !!}

            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="table-responsive">
                        {!! $list_purchase_requisition->render() !!}
                        <table class="table table-striped table-bordered">
                            <thead>
                            <tr>
                                <th class="text-center">
                                    <input type="checkbox" id="check-all">
                                </th>
                                <th>Form Date</th>
                                <th>Form Number</th>
                                <th>Supplier</th>
                                <th>Order</th>
                                <th>Last Request</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list_purchase_requisition as $purchase_requisition)
                                <tr id="list-{{$purchase_requisition->formulir_id}}">
                                    <td class="text-center">
                                        <input type="checkbox" name="formulir_id[]"
                                               value="{{$purchase_requisition->formulir_id}}">
                                    </td>
                                    <td>{{ date_format_view($purchase_requisition->formulir->form_date) }}</td>
                                    <td>
                                        <a href="{{ url('purchasing/point/purchase-requisition/'.$purchase_requisition->id) }}">{{ $purchase_requisition->formulir->form_number}}</a>
                                    </td>
                                    <td>
                                        <a href="{{ url('master/contact/employee/'.$purchase_requisition->employee_id) }}">{{ $purchase_requisition->employee->codeName}}</a>
                                    </td>
                                    <td>
                                        @foreach($purchase_requisition->items as $purchase_requisition_item)
                                            {{ $purchase_requisition_item->item->codeName }}
                                            = {{ number_format_quantity($purchase_requisition_item->quantity) }} {{ $purchase_requisition_item->unit }}
                                            <br/>
                                        @endforeach
                                    </td>
                                    <td>
                                        @if($purchase_requisition->formulir->request_approval_at != null)
                                            {{ date_format_view($purchase_requisition->formulir->request_approval_at, true) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {!! $list_purchase_requisition->render() !!}
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
