@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-expedition::app/expedition/point/invoice/_breadcrumb')
            <li>Create Step 1</li>
        </ul>
        <h2 class="sub-header">Invoice</h2>
        @include('point-expedition::app.expedition.point.invoice._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                <a href="{{ url('expedition/point/invoice/basic/create') }}" class="btn btn-info">
                    Create New Invoice
                </a>
                @if($list_expedition_order->count())
                <br><br>
                <h3>Create From Delivery Order</h3>
                <div class="table-responsive">
                    {!! $list_expedition_order->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th width="100px" class="text-center"></th>
                            <th>EXPEDITION</th>
                            <th>INFO EXPEDITION ORDER</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($list_expedition_order as $expedition_order)
                            <tr id="list-{{$expedition_order->formulir_id}}">
                                <td class="text-center">
                                    <a href="{{ url('expedition/point/invoice/create-step-2/'.$expedition_order->expedition_id) }}"
                                       class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-external-link"></i>
                                        Create Invoice</a>
                                </td>
                                <td>
                                    <a href="{{ url('master/contact/expedition/'.$expedition_order->expedition_id) }}">{{ $expedition_order->expedition->codeName }}</a>
                                </td>
                                <td><ol> <?php $expedition_order->getListExpeditionOrder(); ?></ol></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $list_expedition_order->render() !!}
                </div>
                @endif
            </div>
        </div>
    </div>
@stop
