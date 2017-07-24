@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-accounting::app/accounting/point/memo-journal/_breadcrumb')
        <li><a href="{{url('accounting/point/memo-journal')}}">Memo Journal</a></li>
        <li>Create</li>
    </ul>
    <h2 class="sub-header">Memo Journal | Create</h2>
    @include('point-accounting::app.accounting.point.memo-journal._menu')

    @include('core::app.error._alert')
    <div class="panel panel-default"> 
        <div class="panel-body">
            <form action="{{url('accounting/point/memo-journal')}}" method="post" class="form-horizontal form-bordered">
                {!! csrf_field() !!}
                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Create</legend>
                        </div>
                    </div> 
                </fieldset>

                <fieldset>
                <div class="form-group">
                    <label class="col-md-3 control-label">Date *</label>
                    <div class="col-md-3">
                        <input type="text" name="form_date" class="form-control date input-datepicker" data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}" value="{{date(date_format_get(), strtotime(\Carbon::now()))}}">
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
                    <label class="col-md-3 control-label">Notes</label>
                    <div class="col-md-6">
                        <input name="notes" id="notes" class="form-control" value="{{ old('notes') }}" />
                    </div>
                </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Approval To *</label>
                        <div class="col-md-6">
                            <select name="approval_to" class="selectize" style="width: 100%;" data-placeholder="Choose one..">
                                @foreach($list_user_approval as $user_approval)
                                    @if($user_approval->may('approval.point.accounting.memo.journal'))
                                        <option value="{{$user_approval->id}}" @if(old('approval_to') == $user_approval->id) selected @endif>{{$user_approval->name}}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <div class="form-group">
                        <div class="col-md-12">
                            <legend><i class="fa fa-angle-right"></i> Details</legend>
                        </div>
                    </div>
                </fieldset>
                <div class="form-group">
                    <div class="col-md-12">
                        <div class="table-responsive" style="overflow-x: visible">
                            <table id="item-datatable" class="table table-striped">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th style="width:18%;"class="text-center">COA</th>
                                    <th style="width:15%;"class="text-center">Master Reference</th>
                                    <th style="width:15%;"class="text-center">Form Reference</th>
                                    <th style="width:20%;"class="text-center">Description</th>
                                    <th style="width:12%;">Debit</th>
                                    <th style="width:12%;">Credit</th>
                                </tr>
                                </thead>
                                <tbody class="manipulate-row">

                                 @for($i=0;$i<count($details);$i++)
                                 <?php
                                    $data = \Point\Core\Helpers\TempDataHelper::searchKeyValue('memo.journal', ['coa_id', 'subledger_id', 'subledger_type', 'form_reference_id', 'description', 'debit', 'credit'],
                                        [$details[$i]['coa_id'], $details[$i]['subledger_id'], $details[$i]['subledger_type'], $details[$i]['form_reference_id'], $details[$i]['description'], $details[$i]['debit'], $details[$i]['credit']]);
                                 ?>
                                    <tr>
                                        <td><a href="javascript:void(0)" class="remove-row btn btn-danger" data-item="{{$data['rowid']}}"><i class="fa fa-trash"></i></a></td>
                                        <td>
                                            <select id="coa-id-{{$i}}" name="coa_id[]" class="selectize" style="width:100%;" data-placeholder="Choose one.." onchange="selectCOA(this.value, {{$i}})">
                                                <option ></option>
                                                @foreach($list_coa as $coa)
                                                <option @if($coa->id == $details[$i]['coa_id'] ) selected @endif value="{{$coa->id}}">{{$coa->account}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select id="master-{{$i}}" name="master[]" class="selectize" style="width:100%;" data-placeholder="Choose one.." onchange="selectMaster(this.value, {{$i}} )">
                                                <option></option>
                                                <?php
                                                    $list_journal = Point\Framework\Models\Journal::joinCoa()->coaHasSubleger()->where('coa.id', $details[$i]['coa_id'])->get();
                                                    if($list_journal){
                                                        foreach($list_journal as $journal) {
                                                            if($journal->subledger_id && $journal->subledger_type){
                                                                $subledger = $journal->subledger_type::find($journal->subledger_id);
                                                                $value = $journal->subledger_id.'#'.$journal->subledger_type;
                                                                $name = $subledger->name;

                                                                $subleger_temp = $details[$i]['subledger_id'].'#'.$details[$i]['subledger_type'];
                                                                if($subleger_temp == $value){
                                                                    echo "<option value='".$value."' selected>$name</option>";
                                                                }else{
                                                                    echo "<option value='".$value."'>$name</option>";
                                                                }
                                                            }
                                                        }
                                                    }
                                                ?>

                                            </select>
                                        </td>
                                        <td>
                                            <select id="form-reference-{{$i}}" name="form-reference[]" class="selectize" style="width:100%;" data-placeholder="Choose one..">
                                                <option ></option>
                                                <?php
                                                    $list_journal = Point\Framework\Models\Journal::where('coa_id', $details[$i]['coa_id'])->where('subledger_id', $details[$i]['subledger_id'])->get();
                                                    if($list_journal) {
                                                        foreach($list_journal as $journal) {
                                                            if($journal->subledger_id && $journal->subledger_type){
                                                                $formulir = Point\Framework\Models\Formulir::find($journal->form_journal_id);
                                                                $value= $formulir->id;
                                                                $text= $formulir->form_number.' #'.$formulir->notes;

                                                                if($details[$i]['form_reference_id'] == $value){
                                                                    echo "<option value='".$value."' selected>$text</option>";
                                                                }else{
                                                                    echo "<option value='".$value."'>$text</option>";
                                                                }
                                                            }
                                                        }
                                                    }
                                                ?>

                                            </select>
                                        </td>
                                        <td><input type="text" name="desc[]" class="form-control" value="{{$details[$i]['description']}}" /></td>
                                        <td><input type="text" name="debit[]" class="form-control format-quantity text-right cls_debit" value="{{$details[$i]['debit']}}" /></td>
                                        <td><input type="text" name="credit[]" class="form-control format-quantity text-right cls_credit" value="{{$details[$i]['credit']}}" /></td>
                                    </tr>

                                @endfor

                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="5"><input type="button" id="addItemRow" class="btn btn-primary" value="Add Item" onclick="addRow()"/></td>
                                    <td align="right"><input type="text" readonly name="foot_debit" id="foot_debit" class="form-control format-quantity text-right" value="0" /></td>
                                    <td align="right"><input type="text" readonly name="foot_credit" id="foot_credit" class="form-control format-quantity text-right" value="0" /></td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-6 col-md-offset-3">
                        <button type="submit" class="btn btn-effect-ripple btn-primary">Submit</button>
                        @if(count($details) > 0)
                            <a href="{{url('accounting/point/memo-journal/clear-temp')}}" class="btn btn-effect-ripple btn-danger" data-toggle="modal">CLEAR</a>
                        @endif    
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
    var counter = {{count($details)}};

    function addRow() {

        validateRow();

        item_table.row.add( [
            '<a href="javascript:void(0)" class="remove-row btn btn-danger"><i class="fa fa-trash"></i></a>',
            '<select id="coa-id-'+counter+'" name="coa_id[]" class="selectize" style="width:100%;" data-placeholder="Choose one.." onchange="selectCOA(this.value, '+counter+')">'
            +'<option ></option>'
            @foreach($list_coa as $coa)
            +'<option value="{{$coa->id}}">{{$coa->account}}</option>'
            @endforeach
            +'</select>',
            '<select id="master-'+counter+'" name="master[]" class="selectize" style="width:100%;" data-placeholder="Choose one.." onchange="selectMaster(this.value, '+counter+')">'
            +'</select>',
            '<select id="form-reference-'+counter+'" name="form-reference[]" class="selectize" style="width:100%;" data-placeholder="Choose one..">'
            +'</select>',
            '<input id="desc-'+counter+'" type="text" name="desc[]" class="form-control" value="" />',
            '<input id="debit-'+counter+'" type="text" name="debit[]" class="form-control format-quantity text-right cls_debit" value="0" />',
            '<input id="credit-'+counter+'" type="text" name="credit[]" class="form-control format-quantity text-right cls_credit" value="0" />'
        ] ).draw( false );

        initSelectize('#coa-id-'+counter);
        initSelectize('#master-'+counter);
        initSelectize('#form-reference-'+counter);
        initFormatNumber();
        counter++;
    }

    function validateRow() {
        // validate column item
        for (var i = 0; i <= counter; i++) {
            if($('#coa-id-'+i).length != 0){
                if(! $('#coa-id-'+i).val()){
                    swal("Please, select the account");
                    selectizeInFocus('#coa-id-'+counter);
                    return false;
                    break;
                }
            }
        };
    }

    $('#item-datatable tbody').on( 'click', '.remove-row', function () {
        $.post(
        '<?php print url('accounting/point/memo-journal/delete-temp/') ?>',
        {rowid: $(this).attr('data-item')},
        function(data) {
            console.log(data);
        });

        item_table.row($(this).parents('tr')).remove().draw();
        calculateAll();
    });

    $('#item-datatable tbody').on('keyup', '.cls_debit', function() {
        var index = $('.cls_debit').index(this);
        if(!$(this).attr('readonly')) {
            if($(this).val()>0 || $(this).val()!='') {
                $('.cls_credit').eq(index).val(0).attr('readonly', 'readonly');
            }
            if($(this).val()==0 || $(this).val()=='') {
                $('.cls_credit').eq(index).val(0).attr('readonly', null);
            }
            calculateDebit();
        }
    });

    $('#item-datatable tbody').on('keyup', '.cls_credit', function() {
        var index = $('.cls_credit').index(this);
        if(!$(this).attr('readonly')) {
            if($(this).val()>0 || $(this).val()!='') {
                $('.cls_debit').eq(index).val(0).attr('readonly', 'readonly');
            }
            if($(this).val()==0 || $(this).val()=='') {
                $('.cls_debit').eq(index).val(0).attr('readonly', null);
            }
            calculateCredit();
        }
    });

    $('#item-datatable tbody').on('blur', '.cls_debit,.cls_credit', function() {
        if($(this).val()=='') {
            $(this).val(0)
        };
    });

    function addTemp(name, user_id, keys) {
        $.ajax({
            url: "{{URL::to('temp/add')}}",
            type: 'POST',
            data: {
                name: name,
                user_id: user_id,
                keys: keys
            },
            success: function(data) {

            }, error: function(data) {
                swal('something went wrong');
            }
        });
    }

    function selectCOA(id, counter) {

        var keys = {
            'coa_id': $('#coa-id-'+counter).val(),
            'subledger_id': $('#master-'+counter).val(),
            'subledger_type': '',
            'form_reference_id': $('#form-reference-'+counter).val(),
            'debit': $('#debit-'+counter).val(),
            'credit': $('#credit-'+counter).val(),
            'description': $('#desc-'+counter).val(),
        };

        addTemp('memo.journal', {{auth()->user()->id}}, keys);

        $.ajax({
            url: "{{URL::to('accounting/point/memo-journal/update-master')}}",
            type: 'GET',
            data: {
                id: id
            },
            success: function(data) {
                console.log(data);
                var selectize = $("#master-"+counter)[0].selectize;
                selectize.clear();
                selectize.clearOptions();
                selectize.load(function(callback) {
                    callback(eval(JSON.stringify(data.lists)));
                    //selectize.addItem(data.defaultID);
                });
            }, error: function(data) {
                
            }
        });
    }

    function selectMaster(id, counter) {
        var coa_id = $('#coa-id-'+counter).val();

        $.ajax({
            url: "{{URL::to('accounting/point/memo-journal/update-form')}}",
            type: 'GET',
            data: {
                coa_id: coa_id,
                master_id: id,
            },
            success: function(data) {
                var selectize = $("#form-reference-"+counter)[0].selectize;
                selectize.clear();
                selectize.clearOptions();
                selectize.load(function(callback) {
                    callback(eval(JSON.stringify(data.lists)));
                    // selectize.addItem(data.defaultID);
                });
            },
            error: function(data) {
                swal('something went wrong');
            }
        });
       
    }

    function calculateDebit() {
        var foot_debit = 0;
        $('.cls_debit').each(function() {
            foot_debit += dbNum($(this).val());
        });

        $('#foot_debit').val(appNum(foot_debit));
    }

    function calculateCredit() {
        var foot_credit = 0;
        $('.cls_credit').each(function() {
            foot_credit += dbNum($(this).val());
        });

        $('#foot_credit').val(appNum(foot_credit));
    }

    function calculateAll() {
        calculateDebit();
        calculateCredit();
    }

    $(document).ready(function(){
        calculateAll();
    });
</script>
@stop
