@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.fixed-assets._breadcrumb')
            <li><a href="{{ url('purchasing/point/fixed-assets/contract') }}">Contract</a></li>
            <li>Create step 1</li>
        </ul>
        <h2 class="sub-header">Contract</h2>
        @include('point-purchasing::app.purchasing.point.fixed-assets.contract._menu')

        <div class="panel panel-default">
            <div class="panel-body">
                <h3>Create Contract</h3>
                <div class="table-responsive">
                    {!! $list_contract_reference->render() !!}
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th width="100px" class="text-center"></th>
                            <th>Form Journal</th>
                            <th>Asset Account</th>
                            <th>Asset Name</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                            <th>Supplier</th>
                            <th>Notes</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($list_contract_reference as $contract_reference)
                            <tr>
                                <td class="text-center">
                                    <a href="{{ url('purchasing/point/fixed-assets/contract/create-step-2/'.$contract_reference->id) }}"
                                       class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-external-link"></i>
                                        Create Contract</a>

                                    <a href="{{ url('purchasing/point/fixed-assets/contract/join/'.$contract_reference->id) }}"
                                       class="btn btn-effect-ripple btn-xs btn-info"><i class="fa fa-external-link"></i>
                                        Join to Contract</a>
                                </td>
                                <td>
                                    {{ date_format_view($contract_reference->formulir->form_date) }}
                                    <br/> {{ $contract_reference->formulir->form_number}}
                                </td>
                                <td>
                                    {{ $contract_reference->coa->name}}
                                </td>
                                <td>
                                    {{ $contract_reference->name}}
                                </td>
                                <td>
                                    {{ number_format_quantity($contract_reference->quantity, 0)}}
                                </td>
                                <td class="text-right">
                                    {{ $contract_reference->unit}}
                                </td>
                                <td>
                                    <a href="{{ url('master/contact/supplier/'.$contract_reference->supplier->id) }}">{{ $contract_reference->supplier->codeName}}</a>
                                </td>
                                <td>
                                    {{ $contract_reference->formulir->notes }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $list_contract_reference->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop
