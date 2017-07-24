@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-purchasing::app.purchasing.point.inventory._breadcrumb')
            <li><a href="{{ url('purchasing/point/downpayment') }}">Downpayment</a></li>
            <li>Edit</li>
        </ul>
        <h2 class="sub-header">DOWNPAYMENT</h2>
        @include('point-purchasing::app.purchasing.point.inventory.downpayment._menu')

        @include('core::app.error._alert')

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="{{url('purchasing/point/downpayment/'.$downpayment->id)}}" method="post"
                      class="form-horizontal form-bordered">
                    {!! csrf_field() !!}
                    <input name="_method" type="hidden" value="PUT">

                    <div class="form-group">
                        <label class="col-md-3 control-label">Reason edit *</label>

                        <div class="col-md-6">
                            <input type="text" name="edit_notes" class="form-control"
                                   value="{{$downpayment->formulir->approval_message}}" autofocus>
                        </div>
                    </div>

                    @if($downpayment->purchasing_order_id !== null && $downpayment->purchasing_order_id > 0)
                        <fieldset>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <legend><i class="fa fa-angle-right"></i> REF
                                        @if($downpayment->purchasing_order_id !== null && $downpayment->purchasing_order_id > 0)<a
                                                target="_blank"
                                                href="{{ url('purchasing/point/purchasing-order/'.$downpayment->purchasing_order_id) }}">#{{$downpayment->purchaseOrder->formulir->form_number}}</a>@endif
                                    </legend>
                                    <input type="hidden" name="order_reference" class="form-control"
                                           value="{{$downpayment->purchasing_order_id}}">
                                </div>
                            </div>
                        </fieldset>

                        <div class="form-group">
                            <label class="col-md-3 control-label">Form Date</label>

                            <div class="col-md-6 content-show">
                                {{ date_format_view($downpayment->purchaseOrder->formulir->form_date) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Total amount</label>

                            <div class="col-md-6 content-show">
                                {{ number_format_price($downpayment->purchaseOrder->total) }}
                            </div>
                        </div>
                    @endif

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Downpayment Form</legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Number</label>

                        <div class="col-md-6 content-show">
                            {{$downpayment->formulir->form_number}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Form Date</label>

                        <div class="col-md-3">
                            <input type="text" name="form_date" class="form-control date input-datepicker"
                                   data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}"
                                   value="{{ date(date_format_get(), strtotime($downpayment->formulir->form_date)) }}">
                        </div>
                        <div class="col-md-3">
                            <div class="input-group bootstrap-timepicker">
                                <input type="text" id="time" name="time" class="form-control timepicker"
                                       value="{{date('H:i', strtotime($downpayment->formulir->form_date))}}">
                            <span class="input-group-btn">
                                <a href="javascript:void(0)" class="btn btn-effect-ripple btn-primary"><i
                                            class="fa fa-clock-o"></i></a>
                            </span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Supplier</label>

                        <div class="col-md-6">
                            <select name="supplier_id" class="selectize" style="width: 100%;"
                                    data-placeholder="Choose one..">
                                @if($downpayment->purchasing_order_id !== null && $downpayment->purchasing_order_id > 0)
                                    <option value="{{$downpayment->purchaseOrder->supplier->id}}">{{$downpayment->purchaseOrder->supplier->codeName}}</option>
                                @else
                                    @foreach($list_supplier as $supplier)
                                        <option value="{{$supplier->id}}"
                                                @if($downpayment->supplier_id == $supplier->id) selected @endif>{{$supplier->codeName}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Payment Type</label>

                        <div class="col-md-6 content-show">
                            {{ $downpayment->payment_type }}
                            <input readonly type="hidden" name="payment_type" class="form-control"
                                   value="{{$downpayment->payment_type}}"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Amount *</label>

                        <div class="col-md-6">
                            <input id="quantity" type="text" name="amount" class="form-control format-quantity"
                                   value="{{$downpayment->amount}}"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>

                        <div class="col-md-6">
                            <input type="text" name="notes" class="form-control"
                                   value="{{$downpayment->formulir->notes}}">
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
                            <label class="col-md-3 control-label">Request Approval To</label>

                            <div class="col-md-6">
                                <select name="approval_to" class="selectize" style="width: 100%;"
                                        data-placeholder="Choose one..">
                                    @foreach($list_user_approval as $user_approval)
                                        @if($user_approval->may('approval.point.purchasing.downpayment'))
                                            <option @if($downpayment->formulir->approval_to == $user_approval->id) selected
                                                    @endif value="{{$user_approval->id}}">{{$user_approval->name}} </option>
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
        initDatatable('#item-datatable');
    </script>
@stop
