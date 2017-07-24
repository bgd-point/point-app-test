@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.fixed-assets._breadcrumb')
            <li>Create</li>
        </ul>
        <h2 class="sub-header">Contract | Fixed Assets</h2>
        @include('point-purchasing::app.purchasing.point.fixed-assets.contract._menu')

        @include('core::app.error._alert')
        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('purchasing/point/fixed-assets/contract/'.$contract->id)}}" method="post"
                      class="form-horizontal form-bordered">
                    {!! csrf_field() !!}
                    <input name="_method" type="hidden" value="PUT">
                    <input type="hidden" name="coa_id" value="{{$contract->coa_id}}"> 
                    <input type="hidden" name="journal_id" value="{{$contract->journal_id}}"> 
                        
                    <div class="form-group">
                        <label class="col-md-3 control-label">Reason edit *</label>
                        <div class="col-md-6">
                            <input type="text" name="edit_notes" class="form-control" autofocus>
                        </div>
                    </div>
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Form</legend>
                            </div>
                        </div>
                    </fieldset>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Asset Account *</label>
                        <div class="col-md-6 content-show">
                        {{$contract->coa->name}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Acquisition date *</label>
                        <div class="col-md-6">
                            <input type="text" name="form_date" class="form-control date input-datepicker"
                                   data-date-format="{{\DateHelper::formatMasking()}}"
                                   placeholder="{{\DateHelper::formatMasking()}}"
                                   value="{{ date(date_format_get(), strtotime($contract->formulir->form_date)) }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Asset name *</label>
                        <div class="col-md-6">
                            <input type="text" name="name" class="form-control" value="{{$contract->name}}">
                            <input type="hidden" name="asset_name" class="form-control" value="{{$contract->name}}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Useful period *</label>
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" id="useful-life" name="useful_life" class="form-control text-right format-quantity" value="{{ $contract->useful_life }}" onkeyup="calculateDepreciation()"/>
                                <span class="input-group-addon">MONTH</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Salvage Value *</label>
                        <div class="col-md-6">
                            <input type="text" id="salvage-value" name="salvage_value" class="form-control text-right format-quantity"
                                   value="{{ $contract->salvage_value }}" onkeyup="calculateDepreciation()"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Purchase date *</label>
                        <div class="col-md-6">
                            <input type="text" name="purchase_date" class="form-control date input-datepicker"
                                   data-date-format="{{\DateHelper::formatMasking()}}"
                                   placeholder="{{\DateHelper::formatMasking()}}"
                                   value="{{date(date_format_get(), strtotime($contract->date_purchased))}}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Supplier</label>
                        <div class="col-md-6 content-show">
                            {{$contract->supplier->codeName}}
                            <input type="hidden" name="supplier_id" value="{{$contract->supplier_id}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Quantity *</label>
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" id="quantity" name="quantity"
                                       class="form-control text-right format-quantity" value="{{ $contract->quantity }}" readonly="" />
                                <input type="hidden" name="unit" value="{{$contract->unit}}">
                                <span class="input-group-addon">{{$contract->unit}}</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Price *</label>
                        <div class="col-md-6">
                            <input type="text" id="price" name="price" class="form-control text-right format-quantity"
                                   value="{{ $contract->price }}" readonly="" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Total price *</label>
                        <div class="col-md-6">
                            <input type="text" id="total-price" name="total_price" class="form-control text-right format-quantity" readonly/>
                            <span id="hitung-total" class="help-block"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Total paid </label>
                        <div class="col-md-6">
                            <input type="text" id="total-paid" name="total_paid" class="form-control text-right format-quantity" value="{{ $contract->total_paid }}" onkeyup="calculate()"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">depreciation *</label>
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" id="depreciation" name="depreciation" class="form-control text-right format-quantity" value="{{ $contract->depreciation }}" readonly=""/>
                                <span class="input-group-addon">/ month</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">notes</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" value="{{ $contract->formulir->notes}}" name="notes" />
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
                                        <th style="min-width:220px">Description</th>
                                        <th style="min-width:220px">Date</th>
                                    </tr>
                                    </thead>
                                    <tbody class="manipulate-row">
                                        <?php $counter = 0 ; ?>
                                        @foreach($contract->details as $contract_detail)
                                        <tr>
                                            <td>
                                                @if($contract->journal_id != $contract_detail->reference->journal_id)
                                                <a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a>
                                                @endif
                                                <input type="hidden" name="fixed_assets_contract_reference_id[]" value="{{$contract_detail->fixed_assets_contract_reference_id}}">
                                                <input type="hidden" class="format-quantity form-control" id="total-item-row-{{$counter}}" value="{{$contract_detail->reference->total_price}}">
                                            </td>
                                            <td>{{$contract_detail->reference->formulir->form_number}} {{$contract_detail->reference->formulir->notes}}</td>
                                            <td>{{date_format_view($contract_detail->reference->date_purchased)}}</td>
                                        </tr>
                                        <?php $counter++;?>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Person In Charge</legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Creator</label>
                            <div class="col-md-6 content-show">
                                {{\Auth::user()->name}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Ask Approval To</label>
                            <div class="col-md-6">
                                <select id="approval-to" name="approval_to" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                    <option></option>
                                    @foreach($list_user_approval as $user_approval)
                                        @if($user_approval->may('approval.point.purchasing.contract'))
                                            <option value="{{$user_approval->id}}" @if($contract->formulir->approval_to == $user_approval->id) selected @endif>{{$user_approval->name}}</option>
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
@section('scripts')
    <script>
    var counter = {{$counter}};
        var item_table = initDatatable("#item-datatable");
        $('#item-datatable tbody').on('mouseup', '.remove-row', function () {
            item_table.row($(this).parents('tr')).remove().draw();
            calculate();
        });

        $(function() {
            calculate();
        });

        function calculate() {
            var total = 0;
            var total_item_row = 0;
            for(var i=0; i<counter; i++) {
                if ($("#total-item-row-"+i).length != 0){
                    var total_item_row = dbNum($("#total-item-row-"+i).val());
                    total += total_item_row;
                }
            }
            $('#total-price').val(appNum(total));
        }

        function calculateDepreciation() {
            var quantity = $('#quantity').val();
            var price = $('#price').val();
            var total_price = dbNum(quantity) * dbNum(price);
            $('#total-price').val(appNum(total_price));

            var salvage_value = dbNum($('#salvage-value').val());
            var useful_life = dbNum($('#useful-life').val());
            var acquisition = dbNum($('#total-price').val());
            var activa_cost = dbNum($('#total-paid').val());
            var result = (acquisition + activa_cost - salvage_value) / (12 * useful_life);

            $('#depreciation').val(appNum(result));
        }
        
    </script>
@stop
