@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('bumi-deposit::app/facility/bumi-deposit/_breadcrumb')
        <li><a href="{{ url('facility/bumi-deposit/bank') }}">Bank</a></li>
        <li>Create</li>
    </ul>

    <h2 class="sub-header">Bank</h2>
    @include('bumi-deposit::app.facility.bumi-deposit.bank._menu')

    @include('core::app.error._alert')

    <div class="panel panel-default"> 
        <div class="panel-body">
            <form action="{{ url('facility/bumi-deposit/bank') }}" method="post" class="form-horizontal form-bordered">
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
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Branch *</label>
                    <div class="col-md-6">
                        <input type="text" name="branch" id="branch" class="form-control" value="{{ old('branch') }}" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Address</label>
                    <div class="col-md-6">
                        <input type="text" name="address" id="address" class="form-control" value="{{ old('address') }}" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Phone</label>
                    <div class="col-md-6">
                        <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone') }}" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Fax</label>
                    <div class="col-md-6">
                        <input type="text" name="fax" id="fax" class="form-control" value="{{ old('fax') }}" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Notes</label>
                    <div class="col-md-6">
                        <input type="text" name="notes" id="notes" class="form-control" value="{{ old('notes') }}" />
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
