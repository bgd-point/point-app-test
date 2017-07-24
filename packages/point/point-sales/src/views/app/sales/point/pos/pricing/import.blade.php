@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
             @include('point-sales::app/sales/point/pos/pricing/_breadcrumb')
            <li><a href="{{ url('sales/point/pos/pricing') }}">Pricing</a></li>
            <li>Import</li>
        </ul>
        <h2 class="sub-header">Point Of Sales | Pricing Import</h2>
        @include('point-sales::app.sales.point.pos.pricing._menu')

        @include('core::app.error._alert')
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="well-sm ">
                    <form action="{{url('sales/point/pos/pricing/import/upload')}}" method="post" enctype="multipart/form-data">
                        {!! csrf_field() !!}
                        <div class="form-group">
                            <label for="example-nf-email">IMPORT FROM EXCEL</label>
                            <br/>
                            <input type="file" name="file" required style="display:inline"/>
                            <button class="btn btn-primary"><i class="fa fa-upload"></i> UPLOAD FILE</button>
                        </div> 
                    </form>
                    <div class="form-group">
                        <p>
                            Download file examples below and fill out all fields as the name presented. <br>
                            Do not change the content of the column "code, items, and Group"
                        </p>
                        <a href="{{url('sales/point/pos/pricing/import/download')}}" class="btn btn-effect-ripple btn-info"><i class="fa fa-download"></i> DOWNLOAD SAMPLE FILE</a>
                    </div>
                </div>
                <br>
                
                @if($success || $error)
                <form action="{{url('sales/point/pos/pricing/import/store')}}" method="post">
                    <div class="form-horizontal form-bordered">
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Form</legend>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class=" col-md-12">
                                <div class="well-sm bg-info">
                                    <h4><strong>Information</strong></h4>
                                    <p>
                                        @if(!empty($success))
                                            * {{$count_success}} price item added <br>
                                        @endif
                                        @if(!empty($error))
                                            * {{$count_error}} price item not added 
                                            <a href="#modal-import-error" data-toggle="modal"><i class="fa fa-eye fa-fw"></i>Detail</a>
                                        @endif
                                    </p>
                                </div>    
                            </div>
                        </div>
                @endif
                @if(!empty($success))        
                        <div class="form-group">
                            <label class="col-md-3 control-label">Used Date *</label>
                            <div class="col-md-9">
                                <input type="text" name="form_date" class="form-control date input-datepicker" data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}" value="{{date(date_format_get(), strtotime(\Carbon::now()))}}" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label">Notes</label>
                            <div class="col-md-9">
                                <input type="text" name="notes" class="form-control" value="">
                            </div>
                        </div>  
                    </div>
                
                    {!! csrf_field() !!}
                    {!! $list_import->render() !!}

                    <div class="table-responsive"> 
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th  style="width:70px">No</th>
                                    <th width="25%">Item</th>
                                    <th width="15%">Price</th>
                                    <th width="15%">Discount <br>(%)</th>
                                    <th width="20%">Group</th>
                                    <th width="20%">Nett</th>
                                    <th width="20%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $count=0;?>
                                
                                @for($i=0; $i < count($success); $i++)
                                    <input type="hidden" id="item-id-{{$i}}" value="{{$success[$i]['id']}}">
                                    <tr>
                                        <td align="center">{{$i+1+(app('request')->input('page')*100)}}</td>
                                        <td id="item-{{$i}}">{{$success[$i]['code']}}{{$success[$i]['item']}}</td>
                                        <td id="price-{{$i}}">{{number_format_quantity($success[$i]['price'],0)}}</td>
                                        <td id="discount-{{$i}}">{{$success[$i]['discount']}}</td>
                                        <td id="group-{{$i}}">{{$success[$i]['group']}}</td>
                                        <td id="nett-{{$i}}">{{number_format_quantity($success[$i]['price'] - $success[$i]['discount'] / 100 * $success[$i]['price'],0)}}</td>
                                        <td align="center">
                                            <a href="{{url('sales/point/pos/pricing/import/delete/'.$success[$i]['rowid'])}}" class="remove-row btn btn-danger" data-item=""><i class="fa fa-trash"></i></a>
                                            <a href="#edit-modal" onclick="edit({{$success[$i]['rowid']}},{{$i}})"  class="remove-row btn btn-info"data-toggle="modal"><i class="fa fa-pencil"></i></a>
                                        </td>
                                    </tr>
                                @endfor
                            
                            </tbody>
                        </table> 

                        <div class="form-group">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-effect-ripple btn-primary loading">SUBMIT FORM</button>
                                <a href="{{url('sales/point/pos/pricing/import/clear')}}" class="btn btn-effect-ripple btn-danger" data-toggle="modal">CLEAR</a>
                            </div>
                        </div>
                    </div>

                    {!! $list_import->render() !!}

                </form>
                @endif

            </div>
        </div>  
    </div>


@include('point-sales::app.sales.point.pos.pricing._error-import')
@include('point-sales::app.sales.point.pos.pricing._edit-import')

<script>
function edit(id, index) {
    group = $("#group-"+index).html();
    price = $("#price-"+index).html();
    item  = $("#item-"+index).html();
    discount = $("#discount-"+index).html();
    item_id  = $("#item-id-"+index).val();
    
    $("#label-group").html(group);
    $("#item-edit").val(item);
    $("#price-edit").val(appNum(price));
    $("#discount-edit").val(discount);
    $("#item-id-edit").val(item_id);
    $("#row-id").val(id);
    $("#index").val(index);
    $("#group").val(group);
    $("#price-edit").focus();
    resetForm();

}

function validateItem(){
    if($("#price-edit").val().trim()==""){
        alert("Please, complete the fill.");
        $("#price-edit").focus();
    }else{
        storeItem();
    }
}

function storeItem(){
    data = $("#form_item").serialize();
    $("#button-item").html("Saving...");

    $.ajax({
        type:'POST',
        url: "{{URL::to('sales/point/pos/pricing/import/insert')}}",
        data:data,
        success: function(result){
            console.log(result);
            if(result.status == "failed"){
                alert("Failed, please try again!");
                resetForm();
            }else{
                updateTable(result,$("#index").val());
                $('.modal').modal('hide');
            }
            
        }, error: function(result){
           console.log(result);
        }
    });
}

function resetForm(){
    $("#button-item").html("Submit");
}

function updateTable(result, index){
    $("#price-"+index).html(result.price);
    $("#discount-"+index).html(result.discount);
    var net = appNum(dbNum(result.price) - dbNum(result.price) * dbNum(result.discount) / 100);
    $("#nett-"+index).html(net);
    
}
</script>
@stop
