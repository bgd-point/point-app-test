@extends('core::app.layout')

@section('content')
    <div id="page-content" class="inner-sidebar-left">

        @include('framework::app.master.contact._sidebar')

        <ul class="breadcrumb breadcrumb-top">
            @include('core::app/master/_breadcrumb')
            <li><a href="{{ url('master/contact/'.$person_type->name) }}">Supplier</a></li>
            <li>Create</li>
        </ul>

        <h2 class="sub-header">{{ $person_type->name }}</h2>
        @include('framework::app.master.contact._menu')
        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('master/contact/'.$person_type->slug)}}" method="post" class="form-horizontal form-bordered">
                    {!! csrf_field() !!}

                    <div class="form-group">
                        <label class="col-md-3 control-label">Group *</label>
                        <div class="col-md-6">
                            <div class="input-group">
                                <select id="person_group_id" name="person_group_id" class="">
                                    <option value="">Choose your group</option>
                                </select>
                                <span class="input-group-btn">
                                    <a href="{{url('master/contact/'.$person_type->slug.'/group')}}" class="btn btn-primary" target="_blank">
                                        <i class="fa fa-plus"></i>
                                    </a>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Code *</label>
                        <div class="col-md-6">
                            <input type="text" name="code" class="form-control" value="{{$code}}" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Name *</label>
                        <div class="col-md-6">
                            <input type="text" name="name" class="form-control" value="{{old('name')}}" autofocus>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Email</label>
                        <div class="col-md-6">
                            <input type="email" name="email" class="form-control" value="{{old('email')}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Address</label>
                        <div class="col-md-6">
                            <input type="text" name="address" class="form-control" value="{{old('address')}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Phone</label>
                        <div class="col-md-6">
                            <input type="text" name="phone" class="form-control" value="{{old('phone')}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>
                        <div class="col-md-6">
                            <input type="text" name="notes" class="form-control" value="{{old('notes')}}">
                        </div>
                    </div>

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-3">
                            </div>
                            <div class="col-md-9">
                                <legend><i class="fa fa-angle-right"></i> Contact Person</legend>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-3">
                            </div>
                            <div class="col-md-9">
                                <div class="table-responsive">
                                    <table id="contact-datatable" class="table table-striped">
                                        <thead>
                                        <tr>
                                            <th></th>
                                            <th>NAME</th>
                                            <th>PHONE</th>
                                            <th>ADDRESS</th>
                                            <th>EMAIL</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>

                                <hr>

                                <input type="button" id="addContactRow" class="btn btn-primary" value="Add Contact">
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-3">
                            </div>
                            <div class="col-md-9">
                                <legend><i class="fa fa-angle-right"></i> Data Bank</legend>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-3">
                            </div>
                            <div class="col-md-9">
                                <div class="table-responsive">
                                    <table id="bank-datatable" class="table table-striped">
                                        <thead>
                                        <tr>
                                            <th></th>
                                            <th>BANK BRANCH</th>
                                            <th>BANK NAME</th>
                                            <th>ACCOUNT NUMBER</th>
                                            <th>ACCOUNT NAME</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>

                                <hr>

                                <input type="button" id="addBankRow" class="btn btn-primary" value="Add Bank">
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
    var contact_table = initDatatable('#contact-datatable');
    initFunctionRemoveInDatatable('#contact-datatable', contact_table);
    var bank_table = initDatatable('#bank-datatable');
    initFunctionRemoveInDatatable('#bank-datatable', bank_table);
    
    $('#addContactRow').on( 'click', function () {
        contact_table.row.add( [
            '<a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a>',
            '<input type="text" name="contact_name[]" class="form-control" />',
            '<input type="text" name="contact_phone[]" class="form-control" value="" />',
            '<input type="text" name="contact_address[]" class="form-control" value="" />',
            '<input type="text" name="contact_email[]" class="form-control" value="" />'
        ] ).draw( false );
        $.getScript("{{asset('assets/js/all.js')}}");
    } );

    $('#addBankRow').on( 'click', function () {
        bank_table.row.add( [
            '<a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a>',
            '<input type="text" name="bank_branch[]" class="form-control" />',
            '<input type="text" name="bank_name[]" class="form-control" value="" />',
            '<input type="text" name="bank_account_number[]" class="form-control" value="" />',
            '<input type="text" name="bank_account_name[]" class="form-control" value="" />'
        ] ).draw( false );
        $.getScript("{{asset('assets/js/all.js')}}");
    } );

    $('#person_group_id').selectize({
        onFocus         : eventHandler('{{ url('master/contact/'.$person_type->id.'/group/list') }}', 'person_group_id')
    });
</script>
@stop
