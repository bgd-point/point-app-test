@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-expedition::app/expedition/point/expedition-order/_breadcrumb')
            <li>Create Step 2</li>
        </ul>
        <h2 class="sub-header">Expedition Order</h2>
        @include('point-expedition::app.expedition.point.expedition-order._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('expedition/point/expedition-order/'.$expedition_reference->id.'/store')}}" method="post"
                      class="form-horizontal form-bordered">
                    {!! csrf_field() !!}
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> REF# <a href="{{ $reference->getLinkReference() }}" target="_blank">{{ $reference->formulir->form_number }}</a></legend>
                                <input type="hidden" name="reference_id" value="{{ $reference->formulir->formulirable_id }}"/>
                                <input type="hidden" name="reference_type" value="{{ $reference->formulir->formulirable_type }}"/>
                                <input type="hidden" name="reference_formulir_id" value="{{ $reference->expedition_reference_id }}"/>
                                <input type="hidden" name="group" value="{{ \Input::get('group') ? : false }}"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Date</label>
                            <div class="col-md-6 content-show">
                                {{ date_format_view($reference->formulir->form_date, true) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Supplier</label>
                            <div class="col-md-6 content-show">
                                <a href="{{url('master/contact/customer/'.$reference->person_id)}}"> {{ $reference->person->codeName }}</a>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Form</legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Delivery Date</label>
                        <div class="col-md-3">
                            <input type="text" name="form_date" class="form-control date input-datepicker"
                                   data-date-format="{{date_format_masking()}}"
                                   value="{{date(date_format_get(), strtotime(\Carbon::now()))}}">
                        </div>
                        <div class="col-md-3">
                            <div class="input-group bootstrap-timepicker">
                                <input type="text" id="time" name="time" class="form-control timepicker">
                                <span class="input-group-btn">
                                    <a href="javascript:void(0)" class="btn btn-effect-ripple btn-primary"><i class="fa fa-clock-o"></i></a>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Expedition</label>
                        <div class="col-md-6">
                            <div class="input-group">
                                <select id="contact_id" class="selectize" name="expedition_id" style="width: 100%;" data-placeholder="Choose one..">
                                    <option>-- Select one --</option>
                                    @foreach($list_expedition as $expedition)
                                    <option value="{{$expedition->id}}" @if(old('expedition') == $expedition->id) selected @endif>{{$expedition->codeName}}</option>
                                    @endforeach
                                </select>
                                <span class="input-group-btn">
                                    <a href="#modal-contact" class="btn btn-effect-ripple btn-primary" data-toggle="modal">
                                        <i class="fa fa-plus"></i>
                                    </a>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>
                        <div class="col-md-6">
                            <input type="text" name="notes" class="form-control" value="{{ $reference->formulir->notes }}">
                        </div>
                    </div>
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Item</legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12 ">
                                <div class="table-responsive">
                                    <table id="item-datatable" class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th width="30%">ITEM</th>
                                                <th width="15%">AVAILABLE QUANTITY</th>
                                                <th width="15%">QUANTITY</th>
                                                <th width="25%">UNIT</th>
                                            </tr>
                                        </thead>
                                        <tbody class="manipulate-row">
                                            <?php $counter = 0; ?>
                                            @foreach($expedition_reference->items as $expedition_reference_item)
                                            <?php
                                            $available_quantity = Point\PointExpedition\Helpers\ExpeditionOrderHelper::availableQuantity($reference->expedition_reference_id, $expedition_reference_item->item_id);
                                            ?>
                                            <tr>
                                                <td>{{ $expedition_reference_item->item->codeName }}
                                                    <input type="hidden" name="item_id[]" value="{{$expedition_reference_item->item_id}}"/>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control" readonly="" value="{{ $available_quantity }}">
                                                </td>
                                                <td>
                                                    <input id="item-quantity-{{$counter}}" type="text"
                                                           name="item_quantity[]"
                                                           class="form-control text-right calculate format-quantity"
                                                           value="{{ \Input::get('group') ? $expedition_reference_item->quantity : $available_quantity }}"
                                                           {{\Input::get('group') ? 'readonly' : ''}}/>
                                                    <input type="hidden" name="price[]" value="{{$expedition_reference_item->price}}"/>
                                                </td>
                                                <td>
                                                    {{ $expedition_reference_item->unit }}
                                                    <input type="hidden" name="item_unit_name[]" value="{{$expedition_reference_item->unit}}"/>
                                                </td>
                                            </tr>
                                            <?php $counter++;?>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="3" class="text-right"><strong>Subtotal</strong></td>
                                                <td>
                                                    <input type="text" onkeyup="calculate()" id="subtotal"
                                                           name="subtotal"
                                                           class="form-control format-quantity text-right"
                                                           value="0"/></td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" class="text-right"><strong>Discount</strong></td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="text" onkeyup="calculate()" maxlength="3"
                                                               id="discount" name="discount"
                                                               class="form-control format-quantity text-right"
                                                               value="0"/>
                                                        <span class="input-group-addon">%</span>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" class="text-right"><strong>Tax Base</strong></td>
                                                <td>
                                                    <input type="text" readonly id="tax_base" name="tax_base"
                                                           class="form-control format-quantity calculateAverageFee text-right"
                                                           value="0"/>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" class="text-right"><strong>Tax</strong></td>
                                                <td>
                                                    <input type="text" readonly id="tax" name="tax"
                                                           class="form-control format-quantity calculateAverageFee text-right"
                                                           value="0"/>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="3"></td>
                                                <td>
                                                    <label>
                                                        <input type="checkbox" id="tax-choice-include-tax" name="type_of_tax"
                                                           {{ old('type_of_tax') == 'include' ? 'checked'  : '' }}
                                                           onclick="$('#tax-choice-exclude-tax').prop('checked', false); calculate();"
                                                           value="include" /> Include Tax
                                                    </label>
                                                    <br />
                                                    <label>
                                                        <input type="checkbox" id="tax-choice-exclude-tax" name="type_of_tax"
                                                           {{ old('type_of_tax') == 'exclude' ? 'checked'  : '' }}
                                                           onclick="$('#tax-choice-include-tax').prop('checked', false); calculate();"
                                                           value="exclude" /> Exclude Tax
                                                    </label>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" class="text-right"><h4><strong>Total</strong></h4></td>
                                                <td>
                                                    <input type="text" id="total" name="total" readonly
                                                           class="form-control format-quantity text-right"
                                                           value="0"/>
                                                    </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </fieldset>

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
                            <label class="col-md-3 control-label">Request Approval To</label>
                            <div class="col-md-6">
                                <select name="approval_to" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                    @foreach($list_user_approval as $user_approval)
                                        @if($user_approval->may('approval.point.expedition.order'))
                                            <option value="{{$user_approval->id}}" @if(old('approval_to') == $user_approval->id) selected @endif>{{$user_approval->name}}</option>
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
    @include('framework::app.master.contact.__create', ['person_type' => 'expedition'])
@stop

@section('scripts')
    <script>
        var item_table = initDatatable('#item-datatable');

        function calculate() {
            if (dbNum($('#discount').val()) > 100) {
                dbNum($('#discount').val(100))
            }

            var discount = dbNum($('#discount').val());
            if($('#tax-choice-include-tax').prop('checked')) {
                $('#discount').val(0);
                $('#discount').prop('readonly', true);
                var discount = 0;
            } else {
                $('#discount').prop('readonly', false);
            }
            var subtotal = dbNum($('#subtotal').val());
            var tax_base = subtotal - (subtotal / 100 * discount);
            var tax = 0;

            if ($('#tax-choice-exclude-tax').prop('checked')) {
                tax = tax_base * 10 / 100;
            }

            if ($('#tax-choice-include-tax').prop('checked')) {
                tax_base = tax_base * 100 / 110;
                tax = tax_base * 10 / 100;
            }

            $('#tax_base').val(appNum(tax_base));
            $('#tax').val(appNum(tax));
            $('#total').val(appNum(tax_base + tax));
        }
    </script>
@stop
