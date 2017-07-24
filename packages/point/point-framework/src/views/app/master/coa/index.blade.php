@extends('core::app.layout')

@section('content')
<div id="page-content">
    <ul class="breadcrumb breadcrumb-top">
        @include('core::app/master/_breadcrumb')
        <li>Chart of Accounts</li>
    </ul>

    <h2 class="sub-header">Chart of Accounts</h2>
    @include('framework::app.master.coa._menu')
    <div class="panel panel-primary" id="data-coa">
        @include('framework::app.master.coa._data-index')
    </div>
</div>

@include('framework::app.master.coa._create-category')
@include('framework::app.master.coa._create-by-category')
@include('framework::app.master.coa._create-by-group')
@include('framework::app.master.coa._show')
@include('framework::app.master.coa._edit')
@include('framework::app.master.coa.group._create')
@include('framework::app.master.coa.group._edit')
@include('framework::app.master.coa.depreciation._template')
@stop

@section('scripts')
<script>
    function state(index) {
        $.ajax({
            type:'post',
            url: "{{URL::to('master/coa/state')}}",
            data: {
                index: index
            },
            success: function(result){
                if(result.status === "failed"){
                    swal(result.status, result.message);
                } else {
                    var status = result.data_value == 0 ? 'enable' : 'disable';

                    $("#link-state-"+index).attr('title', status);

                    if(result.data_value == 0 ){
                        $("#link-state-"+index).removeClass("btn-default").addClass("btn-success");
                        $("#icon-state-"+index).removeClass("fa fa-play").addClass("fa fa-pause");
                    } else {
                        $("#link-state-"+index).removeClass("btn-success").addClass("btn-default");
                        $("#icon-state-"+index).removeClass("fa fa-pause").addClass("fa fa-play");
                    }

                    swal(result.status, result.message,"success");
                    loadIndex();
                }
            }
        });
    }

    function loadIndex() {
        $.ajax({
            url: '{{url("master/coa/load-index")}}',
            success: function(data) {
                $("#data-coa").html(data);
            }
        });
    }

    function show(id, type) {
        var url;
        if(type == 'group') {
            url = '{{url("master/coa/group/show")}}';
        } else if (type == 'coa') {
            url = '{{url("master/coa/show")}}';
        } else if (type == 'category') {
            url = '{{url("master/coa/show-category")}}';
        }

        $.ajax({
            url: url,
            data: {id: id},
            success: function(data) {
                $("#show").modal();
                $("#show").html(data);
            }
        });
    }

    function manipulateSubledger(category_name, key, subledger_element) {
        var html_having_fixed_asset = 
            '<div class="form-group" id="subledger-type-'+key+'">'
                +'<label class="col-md-3 control-label">Subledger Type</label>'
                +'<div class="col-md-9">'
                   +'<select name="subledger_type" onchange="selectSubledger(this.value)" id="select-subledger-'+key+'" class="selectize" style="width: 50%;" data-placeholder="Choose one.." tabindex="-1" aria-hidden="true">'
                        +'<option ></option>'
                        +'<option value="person">Person</option>'
                        +'<option value="item">Item</option>'
                        +'<option value="fixed_asset">Fixed Asset</option>'
                    +'</select>'
                +'</div>'
            +'</div>'
            +'<div class="form-group" style="display:none" id="useful-life-form">'
                +'<label class="col-md-3 control-label">Useful Life *</label>'
                +'<div class="col-md-3">'
                    +'<div class="input-group">'
                        +'<input type="text" name="useful_life" id="useful-life" class="form-control format-quantity" maxlength="2" value="">'
                        +'<span class="input-group-addon">Year</span>'
                    +'</div>'
                +'</div>'
            +'</div>';

        var html_not_fixed_asset = 
            '<div class="form-group" id="subledger-type-'+key+'">'
                +'<label class="col-md-3 control-label">Subledger Type</label>'
                +'<div class="col-md-9">'
                   +'<select name="subledger_type" onchange="selectSubledger(this.value)" id="select-subledger-'+key+'" class="selectize" style="width: 50%;" data-placeholder="Choose one.." tabindex="-1" aria-hidden="true">'
                        +'<option ></option>'
                        +'<option value="person">Person</option>'
                        +'<option value="item">Item</option>'
                    +'</select>'
                +'</div>'
            +'</div>'
            +'<div class="form-group" style="display:none" id="useful-life-form"></div>';

        if(category_name == 'Fixed Assets'){
            $(subledger_element).html(html_having_fixed_asset);   
        }else{
            $(subledger_element).html(html_not_fixed_asset);
        }

        initSelectize('#select-subledger-'+key);
    }

    function showSubledger(subledger, key){
        if(subledger === true) {
            $("#subledger-body-"+key).css("display","block");
            initFormatNumber();
        } else {
            $("#subledger-body-"+key).css("display","none");
        }
        var select = $('#select-subledger')[0].selectize;
        select.clear();
    }

    function selectSubledger(subledger) {
        if(subledger == 'fixed_asset'){
            $("#useful-life-form").css("display","block");
            $("#useful-life").focus();
        } else {
            $("#useful-life-form").css("display","none");
            $("#useful-life").val("");
        }
    }

    $(document).ready(function(){
        initHoverable();
    });
</script>
@stop
