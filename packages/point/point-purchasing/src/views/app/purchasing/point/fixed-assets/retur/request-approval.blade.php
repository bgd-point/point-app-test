@extends('core::app.layout')

@section('scripts')
    <script>
        $("#check-all").change(function () {
            $("input:checkbox").prop('checked', $(this).prop("checked"));
        });
    </script>
@stop

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li>request approval</li>
        </ul>
        <h2 class="sub-header">Return</h2>
        @include('point-purchasing::app.purchasing.point.inventory.retur._menu')

        <form action="{{url('purchasing/point/retur/send-request-approval')}}" method="post">
            {!! csrf_field() !!}

            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="table-responsive">
                        {!! $list_retur->render() !!}
                        <table class="table table-striped table-bordered">
                            <thead>
                            <tr>
                                <th width="100px" class="text-center">
                                    <input type="checkbox" id="check-all">
                                </th>
                                <th>Form Date</th>
                                <th>Form Number</th>
                                <th>Supplier</th>
                                <th>Item</th>
                                <th>Last Request</th>

                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list_retur as $retur)
                                <tr id="list-{{$retur->formulir_id}}">
                                    <td class="text-center">
                                        <input type="checkbox" name="formulir_id[]" value="{{$retur->formulir_id}}">
                                    </td>
                                    <td>{{ date_format_view($retur->formulir->form_date) }}</td>
                                    <td>
                                        <a href="{{ url('purchasing/point/retur/'.$retur->id) }}">{{ $retur->formulir->form_number}}</a>
                                    </td>
                                    <td>
                                        {{$retur->supplier->codeName}}
                                    </td>
                                    <td>
                                        <ul>
                                            @foreach($list_retur->items as $items)
                                                <li>{{$items->item->codeName}} : {{$items->quantity}}</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td>
                                        @if($retur->formulir->request_approval_at != '0000-00-00 00:00:00' and $retur->formulir->request_approval_at != null)
                                            {{ date_format_view($retur->formulir->request_approval_at, true) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {!! $list_retur->render() !!}
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
