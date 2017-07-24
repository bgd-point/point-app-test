@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            <li><a href="{{ url('manufacture') }}">Manufacture</a></li>
            <li><a href="{{ url('manufacture/point/process-io/'. $process->id) }}">Process</a></li>
            <li>Output</li>
        </ul>
        <h2 class="sub-header">{{$process->name}} | Output</h2>
        @include('point-manufacture::app.manufacture.point.output._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{ url('manufacture/point/process-io/'. $process->id . '/output'  ) }}" method="get"
                      class="form-horizontal">
                    <div class="form-group">
                        <div class="col-sm-6">
                            <div class="input-group input-daterange"
                                 data-date-format="{{\DateHelper::formatMasking()}}">
                                <input type="text" name="date_from" class="form-control date input-datepicker"
                                       placeholder="From"
                                       value="{{\Input::get('date_from') ? \Input::get('date_from') : ''}}">
                                <span class="input-group-addon"><i class="fa fa-chevron-right"></i></span>
                                <input type="text" name="date_to" class="form-control date input-datepicker"
                                       placeholder="To"
                                       value="{{\Input::get('date_to') ? \Input::get('date_to') : ''}}">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <input type="text" name="search" class="form-control" placeholder="Search..."
                                   value="{{\Input::get('search')}}"
                                   value="{{\Input::get('search') ? \Input::get('search') : ''}}">
                        </div>
                        <div class="col-sm-3">
                            <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i
                                        class="fa fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </form>

                <br/>

                <div class="table-responsive">
                    {!! $list_output->appends(['search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th style="width: 120px">date</th>
                            <th>number</th>
                            <th>process</th>
                            <th>machine</th>
                            <th>notes</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($list_output as $output)
                            <tr id="list-{{$output->formulir_id}}">
                                <td>{{ \DateHelper::formatView($output->formulir->form_date) }}</td>
                                <td>
                                    <a href="{{ url('manufacture/point/process-io/'. $output->input->process_id . '/output/'.$output->id) }}">{{ $output->formulir->form_number}}</a>
                                </td>
                                <td>{{$output->input->process->name}}</td>
                                <td>{{$output->machine->name}}</td>
                                <td>{{$output->formulir->notes}}</td>
                            </tr>
                            <tr>
                                <th></th>
                                <th></th>
                                <th>Finished Goods</th>
                                <th>Quantity produced</th>
                                <th>Warehouse</th>
                            </tr>
                            @foreach($output->product as $out)
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td>{{ $out->item->name}} </td>
                                    <td>{{ number_format_quantity($out->quantity)}} {{$out->unit}}</td>
                                    <td>{{ $out->warehouse->codeName}}</td>
                                </tr>
                            @endforeach
                        @endforeach
                        </tbody>
                    </table>
                    {!! $list_output->appends(['search'=>app('request')->get('search'), 'date_from'=>app('request')->get('date_from'), 'date_to'=>app('request')->get('date_to') ])->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop
