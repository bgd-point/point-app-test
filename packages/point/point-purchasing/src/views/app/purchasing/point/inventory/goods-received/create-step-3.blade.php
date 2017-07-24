@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li><a href="{{ url('purchasing/point/goods-received') }}">Goods Received</a></li>
            <li>Create step 3</li>
        </ul>
        <h2 class="sub-header">Goods Received</h2>
        @include('point-purchasing::app.purchasing.point.inventory.goods-received._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                @if($list_expedition_order->count())
                    {!! $list_expedition_order->render() !!}
                    <div class="table-responsive">
                        <h3>Goods Received picked by Expedition</h3>
                        <table class="table table-striped table-bordered">
                            <thead>
                            <tr>
                                <th width="100px" class="text-center"></th>
                                <th>Form Number <br> Expedition</th>
                                <th>Notes Expedition</th>
                                <th>Supplier</th>
                                <th>Expedition</th>
                                <th>Group</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $po_id = '';
                            $group = '';
                            ?>
                            @foreach($list_expedition_order as $expedition_order)
                                @if($expedition_order->reference()->id != $po_id)
                                <tr>
                                    <td colspan="6">
                                        {{ date_format_view($expedition_order->reference()->formulir->form_date) }} <br>
                                        <a href="{{get_class($expedition_order->reference())::showUrl($expedition_order->reference()->id)}}"> {{ $expedition_order->reference()->formulir->form_number }}</a>
                                    </td>
                                </tr>
                                <?php $po_id = $expedition_order->reference()->id; ?>
                                @endif
                                <tr>
                                    <td class="text-center">
                                    @if($expedition_order->group != $group && $expedition_order->reference()->id == $po_id)
                                        <a class="btn btn-effect-ripple btn-xs btn-info" href="{{url('purchasing/point/goods-received/create-step-4/'.$expedition_order->reference()->id.'/'.$expedition_order->group)}}">
                                        <i class="fa fa-external-link"></i> Create good received </a>
                                        <?php $group = $expedition_order->group; ?>
                                    @endif
                                    </td>
                                    
                                    <td>
                                        {{ date_format_view($expedition_order->formulir->form_date) }} <br>
                                        <a href="{{$expedition_order->showUrl()}}"> {{ $expedition_order->formulir->form_number }} </a>
                                    </td>
                                    <td>{{$expedition_order->formulir->notes}}</td>
                                    <td>{!! get_url_person($expedition_order->reference()->supplier_id) !!}</td>
                                    <td>
                                        {!! get_url_person($expedition_order->expedition_id) !!}
                                    </td>
                                    <td class="text-center">{{ $expedition_order->group }}</td>
                                </tr>

                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    {!! $list_expedition_order->render() !!}
                @endif
            </div>
        </div>
    </div>
@stop
