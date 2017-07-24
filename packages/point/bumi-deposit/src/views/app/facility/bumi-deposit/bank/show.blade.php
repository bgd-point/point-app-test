@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('bumi-deposit::app/facility/bumi-deposit/_breadcrumb')
        <li><a href="{{ url('facility/bumi-deposit/bank') }}">Bank</a></li>
        <li>Show</li>
    </ul>

    <h2 class="sub-header">Bank</h2>
    @include('bumi-deposit::app.facility.bumi-deposit.bank._menu')

    <div class="block full">
        <!-- Block Tabs Title -->
        <div class="block-title">
            <ul class="nav nav-tabs" data-toggle="tabs">
                <li class="active"><a href="#block-tabs-form">form</a></li>
                <li><a href="#block-tabs-history">history</a></li>
                <li><a href="#block-tabs-settings"><i class="gi gi-settings"></i></a></li>
            </ul>
        </div>
        <!-- END Block Tabs Title -->

        <!-- Tabs Content -->
        <div class="tab-content">
            <div class="tab-pane active" id="block-tabs-form">
                <div class="form-horizontal form-bordered">
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Bank</legend>
                            </div>
                        </div>
                    </fieldset>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Bank Name</label>
                        <div class="col-md-6 content-show">
                            {{ $bank->name }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Branch</label>
                        <div class="col-md-6 content-show">
                            {{ $bank->branch }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Address</label>
                        <div class="col-md-6 content-show">
                            {{ $bank->address }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Phone</label>
                        <div class="col-md-6 content-show">
                            {{ $bank->phone }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Fax</label>
                        <div class="col-md-6 content-show">
                            {{ $bank->fax }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>
                        <div class="col-md-6 content-show">
                            {{ $bank->notes }}
                        </div>
                    </div>

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-3">
                            </div>
                            <div class="col-md-9">
                                <legend><i class="fa fa-angle-right"></i> Account</legend>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-3">
                            </div>
                            <div class="col-md-9">
                                <div class="table-responsive">
                                    <table id="account-datatable" class="table table-striped">
                                        <thead>
                                        <tr>
                                            <th></th>
                                            <th>ACCOUNT NAME</th>
                                            <th>ACCOUNT NUMBER</th>
                                            <th>NOTES</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($bank->accounts as $account)
                                            <tr>
                                                <td></td>
                                                <td>{{ $account->account_name }}</td>
                                                <td>{{ $account->account_number }}</td>
                                                <td>{{ $account->account_notes }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-3">
                            </div>
                            <div class="col-md-9">
                                <legend><i class="fa fa-angle-right"></i> Product</legend>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-3">
                            </div>
                            <div class="col-md-9">
                                <div class="table-responsive">
                                    <table id="product-datatable" class="table table-striped">
                                        <thead>
                                        <tr>
                                            <th></th>
                                            <th>PRODUCT NAME</th>
                                            <th>NOTES</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($bank->products as $product)
                                            <tr>
                                                <td></td>
                                                <td>{{ $product->product_name }}</td>
                                                <td>{{ $product->product_notes }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
            <div class="tab-pane" id="block-tabs-history">
                <div class="table-responsive">
                    @include('framework::app._histories')
                </div>
            </div>
            <div class="tab-pane" id="block-tabs-settings">
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Action</legend>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            @if(auth()->user()->may('update.bumi.deposit.bank'))
                            <a href="{{url('facility/bumi-deposit/bank/'.$bank->id.'/edit')}}" class="btn btn-effect-ripple btn-info"><i class="fa fa-pencil"></i> Edit</a>
                            @endif
                            @if(auth()->user()->may('delete.bumi.deposit.bank'))
                            <a onclick="secureDelete({{$bank->id}},'{{ url('facility/bumi-deposit/bank/delete') }}', '{{url('facility/bumi-deposit/bank')}}')"
                               href="javascript:void(0)" class="btn btn-effect-ripple btn-danger" data-toogle="tooltip"
                               title="Delete"><i class="fa fa-times"></i>
                                Delete
                            </a>
                            @endif
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
        <!-- END Tabs Content -->
    </div>
</div>
@stop
