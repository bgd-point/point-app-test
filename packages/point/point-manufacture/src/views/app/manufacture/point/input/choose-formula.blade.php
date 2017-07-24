@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-manufacture::app.manufacture.point.input._breadcrumb')
            <li>Choose Formula</li>
        </ul>
        <h2 class="sub-header">{{$process->name}} | Input</h2>
        @include('point-manufacture::app.manufacture.point.input._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{ url('manufacture/point/process-io/'. $process->id .'/input') }}" method="get"
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
                    {!! $list_formula->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th></th>
                            <th>Number</th>
                            <th>finished goods</th>
                            <th>raw material</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($list_formula as $formula)
                            <tr id="list-{{$formula->formulir_id}}">
                                <td class="text-center">
                                    <a href="{{ url('manufacture/point/process-io/'. $process->id .'/input/use-formula/'.$formula->id) }}"
                                       class="btn btn-effect-ripple btn-xs btn-info">
                                        <i class="fa fa-external-link"></i>
                                        Use
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ url('manufacture/point/formula/'.$formula->id) }}">{{ $formula->formulir->form_number}}</a>
                                </td>
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
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $list_formula->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop
