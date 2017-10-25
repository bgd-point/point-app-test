@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('point-accounting::app/accounting/point/memo-journal/_breadcrumb')
        <li><a href="{{url('accounting/point/memo-journal')}}">Memo Journal</a></li>
        <li>Create</li>
    </ul>
    <h2 class="sub-header">Memo Journal | Edit</h2>
    @include('point-accounting::app.accounting.point.memo-journal._menu')

    @include('core::app.error._alert')
    <div class="panel panel-default"> 
        <div class="panel-body">
            <form action="{{url('accounting/point/memo-journal/'.$memo_journal->id)}}" method="post" class="form-horizontal form-bordered">
                {!! csrf_field() !!}
                <input name="_method" type="hidden" value="PUT">

                <div class="form-group">
                    <label class="col-md-3 control-label">Reason edit *</label>
                    <div class="col-md-6">
                        <input name="edit_notes" class="form-control" value="" autofocus="" autocomplete="off" type="text">
                    </div>
                </div>

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
                            <input type="text" name="form_date" class="form-control date input-datepicker" data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}" value="{{date(date_format_get(), strtotime($memo_journal->formulir->form_date))}}">
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
                            <input name="notes" id="notes" class="form-control" value="{{ $memo_journal->formulir->notes }}" />
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
                        <div class="table-responsive">
                            <table id="item-datatable" class="table table-striped">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th style="width:18%;"class="text-center">COA</th>
                                    <th style="width:15%;"class="text-center">Master Reference</th>
                                    <th style="width:15%;"class="text-center">Form Reference</th>
                                    <th style="width:20%;"class="text-center">Description</th>
                                    <th style="width:12%;">Debit</th>
                                    <th style="width:12%;" >Credit</th>
                                </tr>
                                </thead>
                                <tbody class="manipulate-row">
                                 @for($i=0;$i<count($details);$i++)
                                 <?php
                                    $data = \Point\Core\Helpers\TempDataHelper::searchKeyValue('memo.journal', ['coa_id', 'subledger_id', 'subledger_type', 'form_reference_id', 'description', 'debit', 'credit'],
                                        [$details[$i]['coa_id'], $details[$i]['subledger_id'], $details[$i]['subledger_type'], $details[$i]['form_reference_id'], $details[$i]['description'], $details[$i]['debit'], $details[$i]['credit']]);
                                 ?>
                                    <tr>
                                        <td><a href="javascript:void(0)" class="remove-row btn btn-danger"  data-item="{{$data['rowid']}}"><i class="fa fa-trash"></i></a></td>
                                        <td>
                                            <select id="coa-id-{{$i}}" name="coa_id[]" class="selectize" style="width:100%;" data-placeholder="Choose one.." onchange="selectCOA(this.value, {{$i}})">
                                                <option ></option>
                                                @foreach($list_coa as $coa)
                                                <option @if($coa->id == $details[$i]['coa_id'] ) selected @endif value="{{$coa->id}}">{{$coa->account}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                        <?php
                                            $list_journal = Point\Framework\Models\Journal::joinCoa()->coaHasSubleger()->where('coa.id', $details[$i]['coa_id'])->get();
                                        ?>
                                            <select id="master-{{$i}}" name="master[]" class="selectize" style="width:100%;" data-placeholder="Choose one.." onchange="selectMaster(this.value, {{$i}} )">
                                                <?php
                                                    if ($list_journal) {
                                                        foreach ($list_journal as $journal) {
                                                            if ($journal->subledger_id && $journal->subledger_type) {
                                                                $subledger = $journal->subledger_type::find($journal->subledger_id);
                                                                $value = $journal->subledger_id.'#'.$journal->subledger_type;
                                                                $name = $subledger->name;

                                                                $subleger_id_temp = $details[$i]['subledger_id'].'#'.$details[$i]['subledger_type'];
                                                                if ($value == $subleger_id_temp) {
                                                                    echo "<option value='".$value."' selected>$name</option>";
                                                                } else {
                                                                    echo "<option value='".$value."'>".$name."</option>";
                                                                }
                                                            }
                                                        }
                                                    }
                                                ?>
                                            </select>
                                        </td>
                                        <td>
                                            <select id="invoice-{{$i}}" name="invoice[]" class="selectize" style="width:100%;" data-placeholder="Choose one..">
                                                <?php
                                                    $list_journal = Point\Framework\Models\Journal::where('coa_id', $details[$i]['coa_id'])->where('subledger_id', $details[$i]['subledger_id'])->get();
                                                    if ($list_journal) {
                                                        foreach ($list_journal as $journal) {
                                                            if ($journal->subledger_id && $journal->subledger_type) {
                                                                $value = $journal->formulir->id;
                                                                $text = $journal->formulir->form_number.' #'.$journal->formulir->notes;
                                                                if ($details[$i]['form_reference_id'] == $value) {
                                                                    echo "<option value='".$value."' selected>".$text."</option>";
                                                                } else {
                                                                    echo "<option value='".$value."'>".$text."</option>";
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
                                    <td colspan="5"><input type="button" id="addItemRow" class="btn btn-primary" value="Add Item" /></td>
                                    <td align="right"><input type="text" readonly name="foot_debit" id="foot_debit" class="form-control format-quantity text-right" value="old('foot_debit')" /></td>
                                    <td align="right"><input type="text" readonly name="foot_credit" id="foot_credit" class="form-control format-quantity text-right" value="old('foot_credit')" /></td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
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
                <div class="form-group">
                    <div class="col-md-6 col-md-offset-3">
                        <button type="submit" class="btn btn-effect-ripple btn-primary">Submit</button>
                        @if(count($details) > 0)
                            <a href="{{url('accounting/point/memo-journal/cancel/'.$memo_journal->id)}}" class="btn btn-effect-ripple btn-danger" data-toggle="modal">CLEAR</a>
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
    calculateAll();

    $('#addItemRow').on('mouseup', function () {
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
            '<select id="invoice-'+counter+'" name="invoice[]" class="selectize" style="width:100%;" data-placeholder="Choose one..">'
            +'</select>',
            '<input type="text" name="desc[]" class="form-control" value="" />',
            '<input type="text" name="debit[]" class="form-control format-quantity text-right cls_debit" value="0" />',
            '<input type="text" name="credit[]" class="form-control format-quantity text-right cls_credit" value="0" />'
        ] ).draw( false );
        
        initSelectize('#coa-id-'+counter);
        initSelectize('#master-'+counter);
        initSelectize('#invoice-'+counter);
        initFormatNumber();
        
        $("textarea").on("click", function () {
            $(this).select();
        });
        $("input[type='text']").on("click", function () {
            $(this).select();
        });

        counter++;
    });

    $('#item-datatable tbody').on( 'click', '.remove-row', function () {
        $.post(
            '<?php print url('accounting/point/memo-journal/delete-temp/') ?>',
            {rowid: $(this).attr('data-item')},
            function(data){
                console.log(data);
                
        });
        
        item_table.row($(this).parents('tr')).remove().draw();
        calculateAll();
    }).on('keyup', '.cls_debit', function() {
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
    }).on('keyup', '.cls_credit', function() {
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
    }).on('blur', '.cls_debit,.cls_credit', function() {
        if($(this).val()=='') $(this).val(0);
    });

    $(document).on("keypress", 'form', function (e) {
        var code = e.keyCode || e.which;
        if (code == 13) {
            e.preventDefault();
            return false;
        }
    });

    $(document).ready(function() {
        calculateAll();
    })

    
    function selectCOA(id, counter)
    {
        var selectize = $("#invoice-"+counter)[0].selectize;
        selectize.clear();
        selectize.clearOptions();
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
                var selectize = $("#invoice-"+counter)[0].selectize;
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

</script>
@stop
