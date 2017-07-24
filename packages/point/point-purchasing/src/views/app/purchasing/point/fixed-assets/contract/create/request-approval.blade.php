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
            @include('point-purchasing::app.purchasing.point.fixed-assets._breadcrumb')
            <li><a href="{{ url('purchasing/point/fixed-assets/contract') }}">Contract</a></li>
            <li>Request approval</li>
        </ul>
        <h2 class="sub-header">Contract | Fixed Assets</h2>
        @include('point-purchasing::app.purchasing.point.fixed-assets.contract._menu')

        <form action="{{url('purchasing/point/fixed-assets/contract/send-request-approval')}}" method="post">
            {!! csrf_field() !!}

            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="table-responsive">
                        {!! $list_contract->render() !!}
                        <table class="table table-striped table-bordered">
                            <thead>
                            <tr>
                                <th class="text-center">
                                    <input type="checkbox" id="check-all">
                                </th>
                                <th>Form Date</th>
                                <th>Assets Account</th>
                                <th>Assets Name</th>
                                <th>Supplier</th>
                                <th>Total Price</th>
                                <th>Last Request</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list_contract as $contract)
                                <tr id="list-{{$contract->formulir_id}}">
                                    <td class="text-center">
                                        <input type="checkbox" name="formulir_id[]"
                                               value="{{$contract->formulir_id}}">
                                    </td>
                                    <td>
                                        {{ date_format_view($contract->formulir->form_date) }} <br>
                                        <a href="{{ url('purchasing/point/fixed-assets/contract/'.$contract->id) }}">{{ $contract->formulir->form_number}}</a>
                                    </td>
                                    <td>{{$contract->name}}</td>
                                    <td>
                                        {{$contract->coa->name}}
                                    </td>
                                    <td>
                                        <a href="{{ url('master/contact/supplier/'.$contract->supplier_id) }}">{{ $contract->supplier->codeName}}</a>
                                    </td>
                                    <td>{{ number_format_quantity($contract->total_price) }}</td>
                                    <td>
                                        @if($contract->formulir->request_approval_at != '0000-00-00 00:00:00')
                                            {{ date_format_view($contract->formulir->request_approval_at, true) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {!! $list_contract->render() !!}
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
