@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-expedition::app/expedition/point/expedition-order/_breadcrumb')
            <li>EDIT</li>
        </ul>
        <h2 class="sub-header">Expedition Order</h2>
        @include('point-expedition::app.expedition.point.expedition-order._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('expedition/point/expedition-order/'.$expedition_order->id)}}" method="post"
                      class="form-horizontal form-bordered">
                    {!! csrf_field() !!}
                    <input name="_method" type="hidden" value="PUT">
                    <input name="reference_type" type="hidden" value="{{get_class($reference)}}">
                    <input name="reference_id" type="hidden" value="{{$reference->id}}">

                    <div class="form-group">
                        <label class="col-md-3 control-label">Reason edit *</label>
                        <div class="col-md-6">
                            <input type="text" name="edit_notes" class="form-control" value="{{$expedition_order->formulir->approval_message}}" autofocus>
                        </div>
                    </div>
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend>
                                    <i class="fa fa-angle-right"></i>
                                    REF# <a href="{{ Point\PointExpedition\Models\ExpeditionOrderReference::where('expedition_reference_id', $reference->formulir_id)->first()->getLinkReference() }}" target="_blank">{{ $reference->formulir->form_number }}</a>
                                </legend>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Date</label>
                            <div class="col-md-6 content-show">
                                {{ date_format_view($expedition_order->formulir->form_date, true) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Supplier</label>
                            <div class="col-md-6 content-show">
                                {{ $expedition_reference->person->codeName}}
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
                                   value="{{ date(date_format_get(), strtotime($expedition_order->formulir->form_date)) }}">
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
                                    <option value="{{$expedition->id}}" @if($expedition_order->expedition_id == $expedition->id) selected @endif>{{$expedition->codeName}}</option>
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
                            <input type="text" name="notes" class="form-control" value="{{ $expedition_order->formulir->notes }}">
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
                                <div class="table-responsive well well-sm">
                                    <table id="item-datatable" class="table table-striped">
                                        <thead>
                                        <tr>
                                            <th>ITEM</th>
                                            <th>QUANTITY</th>
                                            <th>UNIT</th>
                                        </tr>
                                        </thead>
                                        <tbody class="manipulate-row">
                                            @foreach($expedition_reference->items as $expedition_reference_item)
                                            <tr>
                                                <td>{{ $expedition_reference_item->item->codeName }}</td>
                                                <td>{{ $expedition_reference_item->quantity }}</td>
                                                <td>{{ $expedition_reference_item->unit }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                        <tr>
                                            <td colspan="2" class="text-right">Subtotal</td>
                                            <td>
                                                <input type="text" id="subtotal" name="subtotal" onkeyup="calculate()" 
                                                       class="form-control format-quantity text-right"
                                                       value="{{$expedition_order->expedition_fee }}"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="text-right">Discount</td>
                                            <td>
                                                <input type="text" id="discount" name="discount" onkeyup="calculate()" 
                                                       class="form-control text-right"
                                                       value="{{ number_format_quantity($expedition_order->discount) }}"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="text-right">Tax Base</td>
                                            <td>
                                                <input type="text" readonly id="tax_base" name="tax_base"
                                                       class="form-control text-right"
                                                       value="{{ number_format_quantity($expedition_order->tax_base) }}"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="text-right">Tax</td>
                                            <td><input type="text" readonly id="tax" name="tax"
                                                       class="form-control text-right"
                                                       value="{{ number_format_quantity($expedition_order->tax) }}"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"></td>
                                            <td>
                                                <label>
                                                    <input type="checkbox" id="tax-choice-include-tax" name="type_of_tax"
                                                       {{ $expedition_order->type_of_tax == 'include' ? 'checked'  : '' }}
                                                       onclick="$('#tax-choice-exclude-tax').prop('checked', false); calculate();"
                                                       value="include" /> Include Tax
                                                </label>
                                                <br />
                                                <label>
                                                    <input type="checkbox" id="tax-choice-exclude-tax" name="type_of_tax"
                                                       {{ $expedition_order->type_of_tax == 'exclude' ? 'checked'  : '' }}
                                                       onclick="$('#tax-choice-include-tax').prop('checked', false); calculate();"
                                                       value="exclude" /> Exclude Tax
                                                </label>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="text-right">Total</td>
                                            <td>
                                                <input type="text" id="total" name="total" readonly
                                                       class="form-control text-right"
                                                       value="{{ number_format_quantity($expedition_order->total) }}"/>
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
