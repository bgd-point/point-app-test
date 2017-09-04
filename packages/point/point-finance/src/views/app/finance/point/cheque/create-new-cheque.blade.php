@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-finance::app.finance.point.cheque._breadcrumb')
        <li><a href="{{ url('finance/point/cheque') }}">Cheque</a></li>
        <li>Pending Cheque</li>
    </ul>
    <h2 class="sub-header">Cheque</h2>
    @include('point-finance::app.finance.point.cheque._menu')
    <div class="panel panel-default">

        <div class="panel-body">
        <form action="{{url('finance/point/cheque/create-new/store')}}" method="post" class="form-horizontal form-bordered">
            {!! csrf_field() !!}
            <input type="hidden" name="reference_id" value="{{$cheque_detail->cheque->id}}">
            <input type="hidden" name="reference_detail_id" value="{{$cheque_detail->id}}">
            <div class="form-horizontal form-bordered">
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> CHEQUE REFERENCE</legend>
                        </div>
                    </div>
                </fieldset>
                <div class="form-group">
                    <label class="col-md-2 control-label">Due Date</label>
                    <div class="col-md-10 content-show">
                        {{date_format_view($cheque_detail->due_date)}}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">Rejected Date</label>
                    <div class="col-md-10 content-show">
                        {{date_format_view($cheque_detail->rejected_at)}}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">Cheque Number</label>
                    <div class="col-md-10 content-show">
                        {{$cheque_detail->number}}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">Bank</label>
                    <div class="col-md-10 content-show">
                        {{$cheque_detail->bank}}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">Amount</label>
                    <div class="col-md-10 content-show">
                        {{number_format_quantity($cheque_detail->amount, 2)}}
                    </div>
                </div>
            </div>
            <fieldset>
                <div class="form-group">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="cheque-datatable" class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th></th>
                                        <th>Bank</th>
                                        <th>Form Date</th>
                                        <th>Due Date</th>
                                        <th>Number</th>
                                        <th>Notes</th>
                                        <th>Amount</th>
                                    </tr>
                                    </thead>
                                    <tbody class="manipulate-row">
                                    <?php $counter = 0;?>
                                    @if(count(old('bank')) > 0)
                                        @for($counter; $counter < count(old('bank')); $counter++ )
                                        <tr>
                                            <td><a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a></td>
                                                <td><input class="form-control" type="text" name="bank[]" id="bank-{{$counter}}"></td>
                                                <td>
                                                    <input type="text" name="form_date_cheque[]" id="form-date-cheque-{{$counter}}" class="form-control date input-datepicker"
                                                       data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}"
                                                       value="{{ date(date_format_get(), strtotime(\Carbon::now())) }}">
                                                </td>
                                                <td>
                                                    <input type="text" name="due_date_cheque[]" id="due-date-cheque-{{$counter}}" class="form-control date input-datepicker"
                                                       data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}"
                                                       value="{{ date(date_format_get(), strtotime(\Carbon::now())) }}">
                                                </td>
                                                <td>
                                                    <input type="text" name="number_cheque[]" id="number-cheque-{{$counter}}" value="{{old('number_cheque')[$counter]}}" class="form-control">
                                                </td>
                                                <td>
                                                    <input type="text" name="notes_cheque[]" id="notes-cheque-{{$counter}}" value="{{old('notes_cheque')[$counter]}}" class="form-control">
                                                </td>
                                                <td>
                                                    <input type="text" name="amount_cheque[]" id="amount-cheque-{{$counter}}" value="{{old('amount_cheque')[$counter]}}" class="form-control text-right format-price-alt  row-total-cheque calculate-cheque">
                                                </td>
                                        </tr>
                                        @endfor
                                    @endif
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td>
                                                <input type="button" id="addChequeRow" class="btn btn-primary" value="Add Cheque">
                                            </td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td> Total Cheque
                                                <input type="text" readonly="" class="form-control format-price-alt" name="total_cheque" id="total-cheque" value="{{old('total_cheque')}}">
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
<script type="text/javascript">
    /**
     * Generate Cheque Table
     * 
     */
    var cheque_table = initDatatable('#cheque-datatable');
    var counter_cheque = 0;
    $('#addChequeRow').on('click', function () {
        cheque_table.row.add([
            '<a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a>',
            '<input type="text" id="bank-' + counter_cheque + '" name="bank[]" class="form-control">',
            '<input type="text" name="form_date_cheque[]" id="form-date-cheque-' + counter_cheque + '" class="form-control date input-datepicker"'
               + 'data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}"'
               + 'value="{{ date(date_format_get(), strtotime(\Carbon::now())) }}">',
            '<input type="text" name="due_date_cheque[]" id="due-date-cheque-' + counter_cheque + '" class="form-control date input-datepicker"'
               + 'data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}"'
               + 'value="{{ date(date_format_get(), strtotime(\Carbon::now())) }}">',
            '<input type="text" name="number_cheque[]" id="number-cheque-'+counter_cheque+'" value="" class="form-control">',
            '<input type="text" name="notes_cheque[]" id="notes-cheque-'+counter_cheque+'" value="" class="form-control">',
            '<input type="text" id="amount-cheque-' + counter_cheque + '" name="amount_cheque[]" class="form-control text-right format-price-alt  row-total-cheque calculate-cheque" value="0" />',
        ]).draw(false);

        initFormatNumber();

        $('.calculate-cheque').keyup(function () {
            calculateCheque();
        });
        counter_cheque++;
    });

    $('#cheque-datatable tbody').on('click', '.remove-row', function () {
        cheque_table.row($(this).parents('tr')).remove().draw();
        calculateCheque();
    });

    function calculateCheque() {
        var total_cheque = 0;
        for (var i = 0; i < counter_cheque; i++) {
            total_cheque += dbNum($('#amount-cheque-'+i).val());
        }
        $('#total-cheque').val(appNum(total_cheque));
    }
</script>
@stop