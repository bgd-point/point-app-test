@extends('core::app.layout')

@section('content')
    <div id="page-content" class="inner-sidebar-left">

        @include('framework::app.master.contact._sidebar')

        <ul class="breadcrumb breadcrumb-top">
            @include('core::app/master/_breadcrumb')
            <li><a href="{{ url('master/contact/'.$person_type->name) }}">Supplier</a></li>
            <li>{{ $person->code }}</li>
            <li>Edit</li>
        </ul>

        <h2 class="sub-header">{{ $person_type->name }}</h2>
        @include('framework::app.master.contact._menu')
        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('master/contact/'.$person_type->slug.'/'.$person->id)}}" method="post" class="form-horizontal form-bordered">
                    {!! csrf_field() !!}
                    <input name="_method" type="hidden" value="PUT">

                    <div class="form-group">
                        <label class="col-md-3 control-label">Group *</label>
                        <div class="col-md-6">
                            <div class="input-group">
                                <select id="person_group_id" name="person_group_id" class="">
                                    <option value="{{$person->person_group_id}}">{{$person->group->name}}</option>

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
                            <input type="text" name="code" class="form-control" value="{{$person->code}}" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Name *</label>
                        <div class="col-md-6">
                            <input type="text" name="name" class="form-control" value="{{$person->name}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Email</label>
                        <div class="col-md-6">
                            <input type="email" name="email" class="form-control" value="{{$person->email}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Address</label>
                        <div class="col-md-6">
                            <input type="text" name="address" class="form-control" value="{{$person->address}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Phone</label>
                        <div class="col-md-6">
                            <input type="text" name="phone" class="form-control" value="{{$person->phone}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>
                        <div class="col-md-6">
                            <input type="text" name="notes" class="form-control" value="{{$person->notes}}">
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
                                        @foreach($person->contacts as $contact)
                                            <tr>
                                                <td><input type="hidden" name="contact_id_old[]" class="form-control" value="{{$contact->id}}" /><a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a></td>
                                                <td><input type="text" name="contact_name_old[]" class="form-control" value="{{$contact->name}}" /></td>
                                                <td><input type="text" name="contact_phone_old[]" class="form-control" value="{{$contact->phone}}" /></td>
                                                <td><input type="text" name="contact_address_old[]" class="form-control" value="{{$contact->address}}" /></td>
                                                <td><input type="text" name="contact_email_old[]" class="form-control" value="{{$contact->email}}" /></td>
                                            </tr>
                                        @endforeach
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
                                        @foreach($person->banks as $bank)
                                            <tr>
                                                <td><input type="hidden" name="bank_id_old[]" class="form-control" value="{{$bank->id}}" /><a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a></td>
                                                <td><input type="text" name="bank_branch_old[]" class="form-control" value="{{$bank->branch}}" /></td>
                                                <td><input type="text" name="bank_name_old[]" class="form-control" value="{{$bank->name}}" /></td>
                                                <td><input type="text" name="bank_account_number_old[]" class="form-control" value="{{$bank->account_number}}" /></td>
                                                <td><input type="text" name="bank_account_name_old[]" class="form-control" value="{{$bank->account_name}}" /></td>
                                            </tr>
                                        @endforeach
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
