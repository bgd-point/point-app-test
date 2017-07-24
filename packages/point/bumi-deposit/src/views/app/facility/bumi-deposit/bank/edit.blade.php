@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('bumi-deposit::app/facility/bumi-deposit/_breadcrumb')
            <li><a href="{{ url('facility/bumi-deposit/bank') }}">Bank</a></li>
            <li>Edit</li>
        </ul>

        <h2 class="sub-header">Bank</h2>
        @include('bumi-deposit::app.facility.bumi-deposit.bank._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('facility/bumi-deposit/bank/'.$bank->id)}}" method="post" class="form-horizontal form-bordered">
                    <input name="_method" type="hidden" value="PUT">
                    {!! csrf_field() !!}

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Form</legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Bank Name *</label>
                        <div class="col-md-6">
                            <input type="text" name="name" id="name" class="form-control" value="{{$bank->name}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Branch *</label>
                        <div class="col-md-6">
                            <input type="text" name="branch" id="branch" class="form-control" value="{{$bank->branch}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Address</label>
                        <div class="col-md-6">
                            <input type="text" name="address" id="address" class="form-control" value="{{$bank->address}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Phone</label>
                        <div class="col-md-6">
                            <input type="text" name="phone" id="phone" class="form-control" value="{{$bank->phone}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Fax</label>
                        <div class="col-md-6">
                            <input type="text" name="fax" id="fax" class="form-control" value="{{$bank->fax}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>
                        <div class="col-md-6">
                            <input type="text" name="notes" id="notes" class="form-control" value="{{$bank->notes}}" />
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
                                                <td><input type="hidden" name="account_id_old[]" class="form-control" value="{{$account->id}}" /><a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a></td>
                                                <td><input type="text" name="account_name_old[]" class="form-control" value="{{$account->account_name}}" /></td>
                                                <td><input type="text" name="account_number_old[]" class="form-control" value="{{$account->account_number}}" /></td>
                                                <td><input type="text" name="account_notes_old[]" class="form-control" value="{{$account->account_notes}}" /></td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <hr>

                                <input type="button" id="addAccountRow" class="btn btn-primary" value="Add Account">
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
                                                <td><input type="hidden" name="product_id_old[]" class="form-control" value="{{$product->id}}" /><a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a></td>
                                                <td><input type="text" name="product_name_old[]" class="form-control" value="{{$product->product_name}}" /></td>
                                                <td><input type="text" name="product_notes_old[]" class="form-control" value="{{$product->product_notes}}" /></td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <hr>

                                <input type="button" id="addProductRow" class="btn btn-primary" value="Add Product">
                            </div>
                        </div>
                    </fieldset>

                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-3">
                            <button type="submit" class="btn btn-effect-ripple btn-primary">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script>
        var account_table = $('#account-datatable').DataTable({
            bSort: false,
            bFilter: false,
            bPaginate: false,
            bInfo: false,
            bScrollCollapse: true,
            scrollY: 500,
            scrollX: true
        });

        var product_table = $('#product-datatable').DataTable({
            bSort: false,
            bFilter: false,
            bPaginate: false,
            bInfo: false,
            bScrollCollapse: true,
            scrollY: 500,
            scrollX: true
        });

        $('#account-datatable tbody').on( 'click', '.remove-row', function () {
            account_table.row( $(this).parents('tr') ).remove().draw();
        } );

        $('#product-datatable tbody').on( 'click', '.remove-row', function () {
            product_table.row( $(this).parents('tr') ).remove().draw();
        } );

        $('#addAccountRow').on( 'click', function () {
            account_table.row.add( [
                '<a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a>',
                '<input type="text" name="account_name[]" class="form-control" />',
                '<input type="text" name="account_number[]" class="form-control" value="" />',
                '<input type="text" name="account_notes[]" class="form-control" value="" />'
            ] ).draw( false );
            $.getScript("{{asset('assets/js/all.js')}}");
        } );

        $('#addProductRow').on( 'click', function () {
            product_table.row.add( [
                '<a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a>',
                '<input type="text" name="product_name[]" class="form-control" />',
                '<input type="text" name="product_notes[]" class="form-control" value="" />'
            ] ).draw( false );
            $.getScript("{{asset('assets/js/all.js')}}");
        } );
    </script>
@stop

