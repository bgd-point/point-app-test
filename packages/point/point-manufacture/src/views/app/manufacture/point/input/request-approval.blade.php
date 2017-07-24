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
            @include('point-manufacture::app.manufacture.point.input._breadcrumb')
            <li>Request approval</li>
        </ul>
        <h2 class="sub-header">{{$process->name}} | Input</h2>
        @include('point-manufacture::app.manufacture.point.input._menu')

        <form action="{{url('manufacture/point/process-io/'. $process->id .'/input/send-request-approval')}}"
              method="post">
            {!! csrf_field() !!}

            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="table-responsive">
                        {!! $list_input->render() !!}
                        <table class="table table-striped table-bordered">
                            <thead>
                            <tr>
                                <th width="100px" class="text-center">
                                    <input type="checkbox" id="check-all">
                                </th>
                                <th>Form Date</th>
                                <th>Form Number</th>
                                <th>Finished goods</th>
                                <th>Raw Material</th>
                                <th>Approval To</th>
                                <th>Last Request</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list_input as $input)
                                <tr id="list-{{$input->formulir_id}}">
                                    <td class="text-center">
                                        <input type="checkbox" name="formulir_id[]" value="{{$input->formulir_id}}">
                                    </td>
                                    <td>{{ date_format_view($input->formulir->form_date) }}</td>
                                    <td>
                                        <a href="{{ url('manufacture/point/process-io/'. $input->process_id .'/input/'.$input->id) }}">{{ $input->formulir->form_number}}</a>
                                    </td>
                                    <td>
                                        <ul>
                                            @foreach($input->product as $product)
                                                <li>
                                                    <a href="{{url('master/item/'.$product->product_id)}}">{{$product->item->codeName}}</a>
                                                    = {{\NumberHelper::formatQuantity($product->quantity)}} {{$product->unit}}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td>
                                        <ul>
                                            @foreach($input->material as $list_material)
                                                <li>
                                                    <a href="{{url('master/item/'.$list_material->material_id)}}">{{$list_material->item->codeName}}</a>
                                                    = {{\NumberHelper::formatQuantity($list_material->quantity)}} {{$list_material->unit}}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td>{{$input->formulir->approvalTo->name}}</td>
                                    <td>
                                        @if($input->formulir->request_approval_at != '0000-00-00 00:00:00' and $input->formulir->request_approval_at != null)
                                            {{ date_format_view($input->formulir->request_approval_at, true) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {!! $list_input->render() !!}
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
