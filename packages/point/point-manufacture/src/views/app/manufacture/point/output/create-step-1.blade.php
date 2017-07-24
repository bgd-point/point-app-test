@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-manufacture::app.manufacture.point.output._breadcrumb')
            <li>Create step 1</li>
        </ul>
        <h2 class="sub-header">{{$process->name}} | Output</h2>
        @include('point-manufacture::app.manufacture.point.output._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                <div class="table-responsive">
                    {!! $list_input->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th width="100px" class="text-center"></th>
                            <th>date</th>
                            <th>form number</th>
                            <th>notes</th>
                            <th>product</th>
                            <th>material</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($list_input as $formula)
                            <tr id="list-{{$formula->formulir_id}}">
                                <td class="text-center">
                                    <a href="{{ url('manufacture/point/process-io/'. $process->id.'/output/create-step-2/'.$formula->id) }}"
                                       class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-external-link"></i>
                                        Create process Out</a>
                                </td>
                                <td>{{ \DateHelper::formatView($formula->formulir->form_date) }}</td>
                                <td>
                                    <a href="{{ url('manufacture/point/process-io/'. $process->id.'/input') }}">{{ $formula->formulir->form_number}}</a>
                                </td>
                                <td>{{$formula->formulir->notes}}</td>
                                <td>
                                    @foreach($formula->product as $product)
                                        {{ $product->item->codeName }}
                                        = {{ \NumberHelper::formatQuantity($product->quantity) }} {{ $product->unit }}
                                        <br/>
                                    @endforeach
                                </td>
                                <td>
                                    @foreach($formula->material as $material)
                                        {{ $material->item->codeName }}
                                        = {{ \NumberHelper::formatQuantity($material->quantity) }} {{ $material->unit }}
                                        <br/>
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $list_input->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop
