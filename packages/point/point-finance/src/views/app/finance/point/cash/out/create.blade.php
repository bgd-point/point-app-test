@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
         @include('point-finance::app.finance.point.cash._breadcrumb')
        <li>Create</li>
    </ul>

    <h2 class="sub-header">Payment | Cash</h2>
    @include('point-finance::app.finance.point.cash._menu')

    @include('core::app.error._alert')
    <div class="panel panel-default"> 
        <div class="panel-body">
            <form action="{{url('finance/point/cash/out')}}" method="post" class="form-horizontal form-bordered">
                {!! csrf_field() !!}
                <input type="hidden" name="pay_to" value="{{$pay_to}}">
                <input type="hidden" name="payment_reference_id" value="{{$payment_reference->id}}">

                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Form</legend>
                        </div>
                    </div> 
                </fieldset>                
                <div class="form-group">
                    <label class="col-md-3 control-label">Payment date *</label>
                    <div class="col-md-3">
                        <input type="text" name="payment_date" class="form-control date input-datepicker" data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}" value="{{ date(date_format_get(), strtotime(\Carbon::now())) }}">
                    </div>
                    <div class="col-md-3">
                        <div class="input-group bootstrap-timepicker">
                            <input type="text" id="time" name="time" class="form-control timepicker" value="{{old('time')}}">
                            <span class="input-group-btn">
                                <a href="javascript:void(0)" class="btn btn-effect-ripple btn-primary"><i class="fa fa-clock-o"></i></a>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Cash Account *</label>
                    <div class="col-md-6">
                        <select name="account_cash_id" class="selectize" data-placeholder="Choose account...">
                            <option ></option>
                            @foreach($list_coa as $coa)
                                <option selected value="{{$coa->id}}">{{$coa->account}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Payment To</label>
                    <div class="col-md-6 content-show">
                        <input type="hidden" name="person_id" value="{{$person->id}}">
                        {{$person->codeName}}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Notes</label>
                    <div class="col-md-6 content-show">
                        <input readonly type="hidden" name="notes" class="form-control" value="{{ $payment_reference->reference->notes }}">
                        {!! nl2br(e($payment_reference->reference->notes)) !!}
                    </div>
                </div>
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="fb-datatable" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Account</th>
                                            <th>Notes</th>
                                            <th class="text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($payment_reference->detail as $payment_reference_detail)
                                            <tr>
                                                <td>
                                                    {{$payment_reference_detail->coa->account}}
                                                    <input type="hidden" name="coa_id[]" value="{{ $payment_reference_detail->coa_id }}">
                                                </td>
                                                <td>
                                                    {{$payment_reference_detail->notes_detail}}
                                                    <input type="hidden" name="notes_detail[]" value="{{ $payment_reference_detail->notes_detail }}">
                                                </td>
                                                <td class="text-right">
                                                    {{number_format_quantity($payment_reference_detail->amount)}}
                                                    <input type="hidden" name="amount[]" value="{{ $payment_reference_detail->amount }}">
                                                    <input type="hidden" name="allocation_id[]" value="{{ $payment_reference_detail->allocation_id }}">
                                                    <input type="hidden" name="formulir_reference_id[]" value="{{ $payment_reference_detail->form_reference_id }}">
                                                    <input type="hidden" name="formulir_reference_class[]" value="{{ $payment_reference_detail->subledger_type }}">
                                                    <input type="hidden" name="reference_id[]" value="{{ $payment_reference_detail->reference_id }}">
                                                    <input type="hidden" name="reference_type[]" value="{{ $payment_reference_detail->reference_type }}">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        @if($payment_reference->cash_advance_id)
                                            <tr>
                                                <td colspan="2" class="text-right">Cash Advance</td>
                                                <td class="text-right">
                                                    {{ number_format_accounting($payment_reference->cashAdvance->remaining_amount * -1) }}
                                                    <input readonly type="hidden" id="cash_advance_id" name="cash_advance_id" class="form-control text-right" value="{{ number_format_price($payment_reference->cash_advance_id) }}" />
                                                </td>
                                            </tr>
                                        @endif
                                        <tr>
                                            <td colspan="2" class="text-right">Total</td>
                                            <td class="text-right">
                                                {{ number_format_price($payment_reference->total) }}
                                                <input readonly type="hidden" id="total" name="total" class="form-control text-right" value="{{ number_format_price($payment_reference->total) }}" />
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>

                                <?php $counter = 0;?>
                                <table id="item-datatable" class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th style="width: 50px"></th>
                                        <th style="min-width: 220px">CASH ADVANCE *</th>
                                        <th style="min-width: 220px">AMOUNT</th>
                                        <th style="min-width: 30px">CLOSE</th>
                                    </tr>
                                    </thead>
                                    <tbody class="manipulate-row">

                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td>
                                            <input type="button" id="addItemRow" class="btn btn-primary" value="Add Item">
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
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
                        <label class="col-md-3 control-label">Form creator</label>
                        <div class="col-md-6 content-show">
                            {{\Auth::user()->name}}
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
      var item_table = initDatatable('#item-datatable');
      var counter = {{$counter}} ? {{$counter}} : 0;

      $('#addItemRow').on('click', function () {
        item_table.row.add([
          '<a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a>',
          '<select id="cash-advance-id-' + counter + '" name="cash_advance_id[]" class="selectize" style="width: 100%;" data-placeholder="Choose one.." onchange="selectCashAdvance(this.value, ' + counter + ')">'
          +'<option></option>'
          +'</select>',
          '<input type="text" id="cash-advance-amount-' + counter + '" name="cash_advance_amount[]" class="form-control format-quantity calculate text-right" value="0" />',
          '<input type="checkbox" id="close-' + counter +'" name="close[]" />'
        ]).draw(false);

        initSelectize('#cash-advance-id-' + counter);
        reloadCashAdvance(counter);
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

      function selectCashAdvance(item_id, counter) {

      }

      function reloadCashAdvance(counter)
      {
        $.ajax({
          url: "{{URL::to('finance/point/cash-advance/list')}}",
          method: "get",
          success: function(data) {
            console.log("ss");
            console.log(data);
            var allocation = $('#cash-advance-id-'+counter)[0].selectize;
            allocation.load(function(callback) {
              callback(eval(JSON.stringify(data.lists)));
            });

          }, error: function(data) {
            console.log("ss" + data.message);
          }
        });
      }

    </script>
@stop
