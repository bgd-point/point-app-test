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
            @include('point-manufacture::app.manufacture.point.formula._breadcrumb')
            <li>request approval</li>
        </ul>
        <h2 class="sub-header">Manufacture | formula</h2>
        @include('point-manufacture::app.manufacture.point.formula._menu')

        <form action="{{url('manufacture/point/formula/send-request-approval')}}" method="post">
            {!! csrf_field() !!}

            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="table-responsive">
                        {!! $list_formula->render() !!}
                        <table class="table table-striped table-bordered">
                            <thead>
                            <tr>
                                <th width="100px" class="text-center">
                                    <input type="checkbox" id="check-all">
                                </th>
                                <th>Form Date</th>
                                <th>Form Number</th>
                                <th>Formula</th>
                                <th>Finished goods</th>
                                <th>Raw Material</th>
                                <th>Approval To</th>
                                <th>Last Request</th>

                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list_formula as $formula)
                                <tr id="list-{{$formula->formulir_id}}">
                                    <td class="text-center">
                                        <input type="checkbox" name="formulir_id[]" value="{{$formula->formulir_id}}">
                                    </td>
                                    <td>{{ date_format_view($formula->formulir->form_date) }}</td>
                                    <td>
                                        <a href="{{ url('manufacture/point/formula/'.$formula->id) }}">{{ $formula->formulir->form_number}}</a>
                                    </td>
                                    <td>{{$formula->name}}</td>
                                    <td>
                                        <ul>
                                            @foreach($formula->product as $product)
                                                <li>
                                                    <a href="{{url('master/item/'.$product->product_id)}}">{{$product->item->codeName}}</a>
                                                    = {{\NumberHelper::formatQuantity($product->quantity)}} {{$product->unit}}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td>
                                        <ul>
                                            @foreach($formula->material as $list_material)
                                                <li>
                                                    <a href="{{url('master/item/'.$list_material->material_id)}}">{{$list_material->item->codeName}}</a>
                                                    = {{\NumberHelper::formatQuantity($list_material->quantity)}} {{$list_material->unit}}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td>{{$formula->formulir->approvalTo->name}}</td>
                                    <td>
                                        @if($formula->formulir->request_approval_at != '0000-00-00 00:00:00' and $formula->formulir->request_approval_at != null)
                                            {{ date_format_view($formula->formulir->request_approval_at, true) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {!! $list_formula->render() !!}
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
