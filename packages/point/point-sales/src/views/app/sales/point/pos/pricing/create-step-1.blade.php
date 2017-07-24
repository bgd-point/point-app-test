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
        <div class="panel-body">
            <form action="{{url('sales/point/pos/pricing/create-step-2/')}}" method="get" class="form-horizontal form-bordered">

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
                        <input type="text" name="form_date" class="form-control date input-datepicker" data-date-format="{{date_format_masking()}}" placeholder="{{date_format_masking()}}" value="{{date(date_format_get(), strtotime(\Carbon::now()))}}" />
                    </div>
                </div>           

                <div class="form-group">
                    <label class="col-md-3 control-label">Notes</label>
                    <div class="col-md-6">
                        <input type="text" name="notes" class="form-control" value="{{old('notes')}}">
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-6 col-md-offset-3">
                        <button type="submit" class="btn btn-effect-ripple btn-primary">Next</button>
                    </div>
                </div>
            </form>
        </div>
    </div>  
</div>
@stop
