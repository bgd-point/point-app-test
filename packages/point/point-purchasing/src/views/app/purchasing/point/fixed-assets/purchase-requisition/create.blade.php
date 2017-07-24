@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.fixed-assets.purchase-requisition._breadcrumb')
            <li>Create</li>
        </ul>
        <h2 class="sub-header">Purchase Requisition | Fixed Assets</h2>
        @include('point-purchasing::app.purchasing.point.fixed-assets.purchase-requisition._menu')

        @include('core::app.error._alert')
        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('purchasing/point/fixed-assets/purchase-requisition')}}" method="post"
                      class="form-horizontal form-bordered">
                    {!! csrf_field() !!}

                    <input type="hidden" name="employee_checking" value="required">
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Form</legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Required Date *</label>

                        <div class="col-md-3">
                            <input type="text" name="required_date" class="form-control date input-datepicker"
                                   data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}"
                                   value="{{date(date_format_get(), strtotime(\Carbon::now()))}}">
                        </div>
                        <div class="col-md-3">
                            <div class="input-group bootstrap-timepicker">
                                <input type="text" id="time" name="time" class="form-control timepicker">
                            <span class="input-group-btn">
                                <a href="javascript:void(0)" class="btn btn-effect-ripple btn-primary"><i
                                            class="fa fa-clock-o"></i></a>
                            </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Employee *</label>
                        <div class="col-md-6">
                            <?php $employee = Point\Framework\Models\Master\Person::find(old('employee_id')); ?>                        
                            <select id="employee-id" name="employee_id" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                <option value="{{ old('employee_id') }}">{{ $employee ? $employee->codeName : ''}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Supplier</label>
                        <div class="col-md-6">
                            <?php $supplier = Point\Framework\Models\Master\Person::find(old('supplier_id')); ?>                        
                            <select id="supplier-id" name="supplier_id" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                <option value="{{ old('supplier_id') }}">{{ $supplier ? $supplier->codeName : ''}}</option>
                            </select>
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
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Details</legend>
                            </div>
                        </div>
                    </fieldset>

                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="item-datatable" class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th></th>
                                        <th>Account</th>
                                        <th>Assets Name</th>
                                        <th>Quantity</th>
                                        <th>Unit</th>
                                        <th>Price</th>
                                        <th>Allocation</th>
                                    </tr>
                                    </thead>
                                    <tbody class="manipulate-row">
                                    <?php $counter = 0;?>
                                        @if(count(old('coa_id')) > 0)
                                            @for($counter; $counter < count(old('coa_id')); $counter++ )
                                            <tr>
                                                <td><a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a></td>
                                                <td>
                                                    <select class="selectize" name="coa_id[]" data-placeholder="Choose one..">
                                                        @foreach($list_account as $account)
                                                        <option value="{{$account->id}}" @if(old('coa_id')[$counter] == $account->id) selected @endif>{{$account->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text" name="name[]" value="{{old('name')[$counter]}}" class="form-control">
                                                </td>
                                                <td>
                                                        <input style="min-width: 120px"
                                                            id="item-quantity-{{$counter}}" 
                                                            type="text" name="quantity[]"
                                                            class="form-control format-quantity text-right calculate"
                                                            value="{{old('quantity')[$counter] }}"/>
                                                </td>
                                                <td>
                                                    <input type="text" name="unit[]" value="{{old('unit')[$counter]}}" class="form-control">
                                                </td>
                                                <td>
                                                    <input type="text" name="price[]" value="{{old('price')[$counter]}}" class="form-control format-quantity">
                                                </td>
                                                <td>
                                                    <select id="allocation-id-{{$counter}}" name="allocation_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                                        @foreach($list_allocation as $allocation)
                                                        <option value="{{$allocation->id}}" @if(old('allocation_id')[$counter] == $allocation->id) selected @endif>{{$allocation->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            </tr>

                                            @endfor
                                        @endif

                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td>
                                                <input type="button" id="addItemRow" class="btn btn-primary" value="Add Item">
                                            </td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Authorized User</legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Creator</label>

                            <div class="col-md-6 content-show">
                                {{auth()->user()->name}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Request Approval To *</label>

                            <div class="col-md-6">
                                <select name="approval_to" class="selectize" style="width: 100%;"
                                        data-placeholder="Choose one..">
                                    @foreach($list_user_approval as $user_approval)

                                        @if($user_approval->may('approval.point.purchasing.requisition'))
                                            <option value="{{$user_approval->id}}"
                                                    @if(old('approval_to') == $user_approval->id) selected @endif>{{$user_approval->name}}</option>
                                        @endif

                                    @endforeach
                                </select>
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
@include('framework::scripts.item')
@include('framework::scripts.person')
@section('scripts')
    <script>
        var item_table = initDatatable('#item-datatable');
        var counter = {{$counter}} ? {{$counter}} : 0;

        $('#addItemRow').on('click', function () {
            item_table.row.add([
                '<a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a>',
                '<select id="coa-id-' + counter + '" name="coa_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
                @foreach($list_account as $account)
                + '<option value="{{$account->id}}">{{$account->name}}</option>'
                @endforeach
                + '</select>',
                '<input type="text" class="form-control" name="name[]">',
                '<input type="text" id="item-quantity-' + counter + '" name="quantity[]" class="form-control format-quantity text-right calculate" value="0" />',
                '<input type="text" id="item-unit-' + counter + '" name="unit[]" class="form-control" value="unit" />',
                '<input type="text" id="item-price-' + counter + '" name="price[]" class="form-control format-quantity calculate text-right" value="0" />',
                '<select id="allocation-id-' + counter + '" name="allocation_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one..">'
                @foreach($list_allocation as $allocation)
                + '<option value="{{$allocation->id}}">{{$allocation->name}}</option>'
                @endforeach
                + '</select>'
            ]).draw(false);

            initSelectize('#coa-id-' + counter);
            initSelectize('#allocation-id-' + counter);
            initFormatNumber();

            $("textarea").on("click", function () {
                $(this).select();
            });
            $("input[type='text']").on("click", function () {
                $(this).select();
            });
            counter++;
        });

        $('#item-datatable tbody').on('click', '.remove-row', function () {
            item_table.row($(this).parents('tr')).remove().draw();
        });

        $(document).on("keypress", 'form', function (e) {
            var code = e.keyCode || e.which;
            if (code == 13) {
                e.preventDefault();
                return false;
            }
        });

        // reload data item with ajax
        if (counter > 0) {
            reloadPerson('#employee-id', 'employee', false);
            reloadPerson('#supplier-id', 'supplier', false);
        } else {
            reloadPerson('#supplier-id', 'supplier');
            reloadPerson('#employee-id', 'employee');
        }
    </script>
@stop
