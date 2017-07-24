@extends('core::app.layout')

@section('content')
    <div id="page-content">
        <ul class="breadcrumb breadcrumb-top">
            @include('point-sales::app/sales/point/pos/pricing/_breadcrumb')
            <li><a href="{{ url('sales/point/pos/pricing') }}">Pricing</a></li>
            <li>Create</li>
        </ul>
        <h2 class="sub-header">Point Of Sales | Pricing</h2>
        @include('point-sales::app.sales.point.pos.pricing._menu')

        @include('core::app.error._alert')
        <div class="panel panel-default">
            <div class="panel-body form-horizontal form-bordered">
                <form action="{{ url('sales/point/pos/pricing/create-step-2/') }}" method="get" class="form-horizontal">
                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <legend><i class="fa fa-angle-right"></i> Form</legend>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Used Date *</label>
                        <div class="col-md-6">
                            <input type="text" name="form_date" class="form-control date input-datepicker" data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}" value="{{ date(date_format_get(), strtotime(date_format_db($form_date))) }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Notes</label>
                        <div class="col-md-6">
                            <input type="text" name="notes" class="form-control" value="{{$notes}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-3">
                            <input type="text" name="search" class="form-control" placeholder="Search Name..." value="{{\Input::get('search')}}" autofocus>
                        </div>
                        <div class="col-sm-6">
                            <button type="submit" class="btn btn-effect-ripple btn-effect-ripple btn-primary"><i class="fa fa-search"></i> Search</button> 
                        </div>
                    </div>
                </form>
                <form action="{{url('sales/point/pos/pricing')}}" method="post">
                    {!! csrf_field() !!}
                    <input type="hidden" name="form_date" class="form-control date input-datepicker" data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}" value="{{ date(date_format_get(), strtotime(date_format_db($form_date))) }}">
                    <input type="hidden" name="notes" class="form-control" value="{{$notes}}">
                    
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                {!! $list_item->appends(['form_date' => $form_date, 'notes' => $notes, 'search' => $search])->render() !!}
                                <table id="item-datatable" class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" class="text-center">ITEM</th>
                                            <th rowspan="2" class="text-center">COST OF GOOD <br> SALES</th>
                                        @foreach($list_group as $group)
                                            <th colspan="3" class="text-center">{{ $group->name }}</th>
                                        @endforeach
                                        </tr>
                                        <tr>
                                            @foreach($list_group as $group)
                                                <th class="text-center">price</th>
                                                <th class="text-center">discount</th>
                                                <th class="text-center" onclick="updateNett()">nett</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php $counter = 0; ?>
                                    @foreach($list_item as $item)
                                        <tr>
                                            <td>{{ $item->codeName }}</td>
                                            <td><input type="text" id="cost_of_sales-{{$counter}}" class="form-control format-quantity text-right" value="{{ $item->averageCostOfSales(date_format_db(\Input::get('form_date'),date('H:i:s')) )}}" style="min-width:109px" readonly/></td>
                                            @foreach($list_group as $person_group)
                                                <?php
                                                    $price = 0;
                                                    $discount = 0;
                                                    $nett = 0;
                                                    $pos_pricing_date = Point\PointSales\Models\Pos\PosPricing::joinFormulir()
                                                        ->select('point_sales_pos_pricing.id')
                                                        ->where('formulir.form_date', '<=', \Carbon::now())
                                                        ->orderBy('formulir.id', 'desc')
                                                        ->get()
                                                        ->toArray();

                                                    if ($pos_pricing_date) {
                                                        $pos_pricing = Point\PointSales\Models\Pos\PosPricingItem::where('item_id', '=', $item->id)
                                                            ->where('person_group_id', '=', $person_group->id)
                                                            ->whereIn('pos_pricing_id', $pos_pricing_date)
                                                            ->orderBy('id', 'desc')
                                                            ->get();

                                                        $price = 0;
                                                        $discount = 0;
                                                        foreach ($pos_pricing as $pos_price) {
                                                            if ($pos_price->price != null) {
                                                                $price = $pos_price->price;
                                                                $discount = $pos_price->discount;
                                                                $nett = $price - ($price * $discount / 100);
                                                                break;
                                                            } else {
                                                                continue;
                                                            }
                                                        }
    
                                                    }
                                                    
                                                    $data = \Point\Core\Helpers\TempDataHelper::searchKeyValue('pos.pricing.create', auth()->user()->id, ['item_id', 'person_group_id'], [$item->id, $person_group->id]);
                                                    if ($data) {
                                                        $data = \Point\Core\Helpers\TempDataHelper::find($data['rowid']);
                                                        $price = $data['price'];
                                                        $discount = $data['discount'];
                                                        $nett = $price - ($price * $discount / 100);
                                                    }

                                                    
                                                ?>
                                                <td><input type="text" id="price-{{$counter}}" class="form-control format-quantity text-right" value="{{ $price }}" onchange="validate({{$counter}}, {{$person_group->id}}, {{$item->id}}, 'price-', {{$price}} )" onkeyup="updateNett({{$counter}})" style="min-width:109px"/></td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="text" id="discount-{{$counter}}" class="form-control format-quantity text-right" value="{{ $discount }}" onchange="validate({{$counter}}, {{$person_group->id}}, {{$item->id}}, 'discount-', {{$discount}} )" onkeyup="updateNett({{$counter}})" style="min-width:109px"/>
                                                        <span class="input-group-addon">%</span>
                                                    </div>
                                                </td>
                                                <td><input type="text" readonly="readonly" id="nett-{{$counter}}" class="form-control format-quantity text-right" value="{{ $nett }}" style="min-width:109px" /></td>
                                                <?php $counter++; ?>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                                {!! $list_item->appends(['form_date' => $form_date, 'notes' => $notes, 'search' => $search])->render() !!}
                            </div>
                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-3">
                                    <button type="submit" class="btn btn-effect-ripple btn-primary">Done</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('scripts')
<script>
    function validate(counter, person_group_id, item_id, element, value){
        if($('#discount-'+counter).val() > 99){
            $('#discount-'+counter).val(99);
        }
        var price = $('#price-'+counter).val();
        var discount = $('#discount-'+counter).val();
        var base_price = $('#cost_of_sales-'+counter).val();
        if(base_price > (price - (price * discount / 100))) {
              swal({
              title: "confirmation",
              text: "nett price less than cost of sales, are you sure want to use the price ?",
              type: "warning",
              showCancelButton: true,
              confirmButtonText: "Yes",
              closeOnConfirm: false
            },
            function(isConfirm){
                if(isConfirm) {
                    updatePrice(counter, person_group_id, item_id)
                    swal("success","done" ,"success");
                } else {
                    $('#'+element+counter).val(value);
                    $('#'+element+counter).focus();
                }
            });
        } else {
            updatePrice(counter, person_group_id, item_id)
        }
    }

    function updatePrice(counter, person_group_id, item_id){
        $.ajax({
            url: "{{URL::to('sales/point/pos/pricing/update-price')}}",
            type: 'GET',
            data: {
                person_group_id: person_group_id,
                item_id: item_id,
                price: $('#price-'+counter).val(),
                discount: $('#discount-'+counter).val()
            },
            success: function(data) {
                $('#nett-'+counter).val(appNum(data.nett));
            }, error: function(data) {
            }
        });
    }

    function updateNett(counter) {
        var price = dbNum($('#price-'+counter).val());
        var discount = dbNum($('#discount-'+counter).val());
        $('#nett-'+counter).val(appNum(price - (price * discount / 100)));
    }
</script>
@stop
